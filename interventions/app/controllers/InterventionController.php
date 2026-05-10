<?php
/**
 * CityZen - InterventionController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'services/InterventionService.php';
require_once APP_PATH . 'services/SignalementService.php';
require_once APP_PATH . 'services/UserService.php';
require_once APP_PATH . 'models/DemandeInterventionModel.php';

class InterventionController extends Controller
{
    private InterventionService  $interventionService;
    private SignalementService   $signalementService;
    private UserService          $userService;
    private DemandeInterventionModel $demandeModel;

    public function __construct()
    {
        $this->interventionService = new InterventionService();
        $this->signalementService  = new SignalementService();
        $this->userService         = new UserService();
        $this->demandeModel        = new DemandeInterventionModel();
    }

    public function index(array $params = []): void
    {
        $page          = max(1, (int)$this->get('page', 1));
        $interventions = $this->interventionService->findAllWithDetails($page);
        $total         = $this->interventionService->getModel()->count();
        $totalPages    = (int)ceil($total / ITEMS_PER_PAGE);

        $this->render('intervention/index', [
            'pageTitle'     => 'Interventions',
            'interventions' => $interventions,
            'page'          => $page,
            'totalPages'    => $totalPages,
            'total'         => $total,
            'flash'         => $this->getFlash(),
        ]);
    }

    public function show(array $params = []): void
    {
        $id           = (int)($params['id'] ?? 0);
        $intervention = $this->interventionService->findByIdWithDetails($id);
        if (!$intervention) {
            http_response_code(404);
            require APP_PATH . 'views/errors/404.php';
            return;
        }
        $suivi = $this->interventionService->getSuivi($id);
        $this->render('intervention/show', [
            'pageTitle'    => 'Intervention #' . str_pad($id, 5, '0', STR_PAD_LEFT),
            'intervention' => $intervention,
            'suivi'        => $suivi,
            'flash'        => $this->getFlash(),
        ]);
    }

    public function demandeForm(array $params = []): void
    {
        $signalements = $this->signalementService->getModel()->findAll('created_at', 'DESC');
        $techniciens  = $this->userService->getTechniciens();

        $this->render('intervention/demande', [
            'pageTitle'    => 'Demande d\'Intervention',
            'signalements' => $signalements,
            'techniciens'  => $techniciens,
            'flash'        => $this->getFlash(),
        ]);
    }

    public function storeDemande(array $params = []): void
    {
        $nom         = $this->sanitize($this->input('nom_demandeur', ''));
        $email       = filter_var($this->input('email_demandeur', ''), FILTER_SANITIZE_EMAIL);
        $telephone   = $this->sanitize($this->input('telephone', ''));
        $type        = $this->sanitize($this->input('type_intervention', ''));
        $description = $this->sanitize($this->input('description', ''));
        $urgence     = $this->input('urgence', 'normal');
        $signalId    = (int)$this->input('signalement_id', 0);

        if (empty($nom) || empty($email) || empty($description)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            $this->redirect('intervention/demande');
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Adresse email invalide.');
            $this->redirect('intervention/demande');
            return;
        }

        $techId   = (int)$this->input('technicien_id', 0);
        $userRole = $_SESSION['user_role'] ?? 'citoyen';

        if ($techId > 0 && $userRole === 'admin') {
            $id = $this->interventionService->getModel()->insert([
                'signalement_id' => $signalId ?: null,
                'technicien_id'  => $techId,
                'titre'          => 'Intervention : ' . ucfirst($type) . ' - ' . $nom,
                'description'    => $description,
                'statut'         => 'planifiee',
                'date_planifiee' => date('Y-m-d', strtotime('+1 day')),
            ]);

            if ($id) {
                if ($signalId) {
                    $this->signalementService->getModel()->update($signalId, ['statut' => 'en_attente']);
                }
                $this->setFlash('success', 'Intervention créée et technicien assigné avec succès !');
                $this->redirect('admin/interventions');
                return;
            }
        } else {
            $id = $this->demandeModel->insert([
                'user_id'           => $_SESSION['user_id'] ?? null,
                'signalement_id'    => $signalId ?: null,
                'nom_demandeur'     => $nom,
                'email_demandeur'   => $email,
                'telephone'         => $telephone,
                'type_intervention' => $type,
                'description'       => $description,
                'urgence'           => in_array($urgence, ['normal','urgent','tres_urgent']) ? $urgence : 'normal',
                'statut'            => 'en_attente',
            ]);

            if ($id) {
                $this->setFlash('success', 'Votre demande d\'intervention a été soumise avec succès ! Référence : #' . str_pad($id, 5, '0', STR_PAD_LEFT));
                $this->redirect('interventions');
                return;
            }
        }

        $this->setFlash('error', 'Erreur lors de l\'enregistrement.');
        $this->redirect('intervention/demande');
    }
}


