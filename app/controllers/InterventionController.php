<?php
/**
 * CityZen - InterventionController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'models/InterventionModel.php';
require_once APP_PATH . 'models/SignalementModel.php';
require_once APP_PATH . 'models/DemandeInterventionModel.php';
require_once APP_PATH . 'models/CategorieModel.php';
require_once APP_PATH . 'models/UserModel.php';

class InterventionController extends Controller
{
    private InterventionModel $interventionModel;
    private SignalementModel $signalementModel;
    private DemandeInterventionModel $demandeModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->interventionModel = new InterventionModel();
        $this->signalementModel  = new SignalementModel();
        $this->demandeModel      = new DemandeInterventionModel();
        $this->userModel         = new UserModel();
    }

    public function index(array $params = []): void
    {
        $page          = max(1, (int)$this->get('page', 1));
        $interventions = $this->interventionModel->findAllWithDetails($page);
        $total         = $this->interventionModel->count();
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
        $id = (int)($params['id'] ?? 0);
        $intervention = $this->interventionModel->findByIdWithDetails($id);
        if (!$intervention) {
            http_response_code(404);
            require APP_PATH . 'views/errors/404.php';
            return;
        }
        $suivi = $this->interventionModel->getSuivi($id);
        $this->render('intervention/show', [
            'pageTitle'    => 'Intervention #' . str_pad($id, 5, '0', STR_PAD_LEFT),
            'intervention' => $intervention,
            'suivi'        => $suivi,
            'flash'        => $this->getFlash(),
        ]);
    }

    public function demandeForm(array $params = []): void
    {
        $signalements = $this->signalementModel->findAll('created_at', 'DESC');
        $techniciens  = $this->userModel->getTechniciens();
        
        $this->render('intervention/demande', [
            'pageTitle'   => 'Demande d\'Intervention',
            'signalements'=> $signalements,
            'techniciens' => $techniciens,
            'flash'       => $this->getFlash(),
        ]);
    }

    public function storeDemande(array $params = []): void
    {
        $nom          = $this->sanitize($this->input('nom_demandeur', ''));
        $email        = filter_var($this->input('email_demandeur', ''), FILTER_SANITIZE_EMAIL);
        $telephone    = $this->sanitize($this->input('telephone', ''));
        $type         = $this->sanitize($this->input('type_intervention', ''));
        $description  = $this->sanitize($this->input('description', ''));
        $urgence      = $this->input('urgence', 'normal');
        $signalId     = (int)$this->input('signalement_id', 0);

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

        $techId       = (int)$this->input('technicien_id', 0);
        $userRole     = $_SESSION['user_role'] ?? 'citoyen';

        if ($techId > 0 && $userRole === 'admin') {
            // Si c'est un admin qui assigne un technicien, on crée directement une intervention
            $id = $this->interventionModel->insert([
                'signalement_id' => $signalId ?: null,
                'technicien_id'  => $techId,
                'titre'          => "Intervention : " . ucfirst($type) . " - " . $nom,
                'description'    => $description,
                'statut'         => 'planifiee',
                'date_planifiee' => date('Y-m-d', strtotime('+1 day')),
            ]);

            if ($id) {
                // Si un signalement est lié, on met à jour son statut
                if ($signalId) {
                    $this->signalementModel->update($signalId, ['statut' => 'en_attente']);
                }
                $this->setFlash('success', 'Intervention créée et technicien assigné avec succès !');
                $this->redirect('admin/interventions');
                return;
            }
        } else {
            // Sinon on crée une demande classique
            $id = $this->demandeModel->insert([
                'user_id'        => $_SESSION['user_id'] ?? null,
                'signalement_id' => $signalId ?: null,
                'nom_demandeur'  => $nom,
                'email_demandeur'=> $email,
                'telephone'      => $telephone,
                'type_intervention' => $type,
                'description'    => $description,
                'urgence'        => in_array($urgence, ['normal','urgent','tres_urgent']) ? $urgence : 'normal',
                'statut'         => 'en_attente',
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
