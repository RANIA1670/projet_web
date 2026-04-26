<?php
/**
 * CityZen - AdminController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'models/SignalementModel.php';
require_once APP_PATH . 'models/InterventionModel.php';
require_once APP_PATH . 'models/UserModel.php';

class AdminController extends Controller
{
    private SignalementModel $signalementModel;
    private InterventionModel $interventionModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->signalementModel = new SignalementModel();
        $this->interventionModel = new InterventionModel();
        $this->userModel = new UserModel();
    }

    private function requireAdmin(): void
    {
        $this->requireLogin();
        $user = $this->currentUser();
        if ($user['role'] !== 'admin') {
            $this->setFlash('error', 'Accès refusé. Vous devez être administrateur.');
            $this->redirect('/');
        }
    }

    public function index(array $params = []): void
    {
        $statsSignalements = $this->signalementModel->getStatsByStatut();
        $statsInterventions = $this->interventionModel->getStatsByStatut();
        
        $totalSignalements = array_sum(array_column($statsSignalements, 'total'));
        $totalInterventions = array_sum(array_column($statsInterventions, 'total'));

        $recentSignalements = $this->signalementModel->getRecent(5);
        
        $this->render('admin/index', [
            'pageTitle' => 'Tableau de bord Administration',
            'totalSignalements' => $totalSignalements,
            'totalInterventions' => $totalInterventions,
            'recentSignalements' => $recentSignalements,
            'flash' => $this->getFlash()
        ]);
    }

    public function signalements(array $params = []): void
    {
        $page = (int)$this->get('page', 1);
        $statut = $this->get('statut', '');
        
        $filters = [];
        if ($statut) $filters['statut'] = $statut;

        $signalements = $this->signalementModel->findAllWithDetails($page, 100, $filters);
        
        $this->render('admin/signalements', [
            'pageTitle' => 'Gestion des Signalements',
            'signalements' => $signalements,
            'statut' => $statut,
            'flash' => $this->getFlash()
        ]);
    }

    public function interventions(array $params = []): void
    {
        $page = (int)$this->get('page', 1);
        $interventions = $this->interventionModel->findAllWithDetails($page, 100);
        
        $this->render('admin/interventions', [
            'pageTitle' => 'Gestion des Interventions',
            'interventions' => $interventions,
            'techniciens' => $this->userModel->getTechniciens(),
            'flash' => $this->getFlash()
        ]);
    }

    public function assignTechnicien(array $params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        $techId = (int)$this->input('technicien_id', 0);
        
        if ($id > 0 && $techId > 0) {
            $this->interventionModel->update($id, ['technicien_id' => $techId]);
            $this->setFlash('success', 'Technicien assigné avec succès.');
        } else {
            $this->setFlash('error', 'Erreur lors de l\'assignation.');
        }
        
        $this->redirect('admin/interventions');
    }

    public function updateInterventionStatus(array $params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        $statut = $this->input('statut', '');
        $notes = $this->sanitize($this->input('notes', ''));

        if ($id > 0 && $statut) {
            $this->interventionModel->update($id, [
                'statut' => $statut,
                'notes' => $notes
            ]);
            
            // On ajoute aussi un historique (Partie Métier)
            $this->interventionModel->addSuivi($id, $statut, "Changement de statut par l'administrateur : " . $statut, $_SESSION['user_id'] ?? null);
            
            $this->setFlash('success', 'Statut de l\'intervention mis à jour.');
        } else {
            $this->setFlash('error', 'Données invalides.');
        }

        $this->redirect('admin/interventions');
    }

    public function deleteIntervention(array $params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            $this->interventionModel->delete($id);
            $this->setFlash('success', 'Intervention supprimée.');
        }
        $this->redirect('admin/interventions');
    }

    public function techniciens(array $params = []): void
    {
        $techniciens = $this->userModel->getTechniciens();
        
        // Find which technicien is busy
        // A technicien is busy if they have an intervention that is 'en_cours' or 'planifiee'
        $busyTechs = [];
        // Since we don't have a direct model method, we'll fetch interventions and map them
        $interventions = $this->interventionModel->findAllWithDetails(1, 1000);
        foreach ($interventions as $inv) {
            if (in_array($inv['statut'], ['planifiee', 'en_cours'])) {
                if ($inv['technicien_id']) {
                    $busyTechs[$inv['technicien_id']] = $inv;
                }
            }
        }

        foreach ($techniciens as &$tech) {
            if (isset($busyTechs[$tech['id']])) {
                $tech['is_busy'] = true;
                $tech['current_intervention'] = $busyTechs[$tech['id']];
            } else {
                $tech['is_busy'] = false;
            }
        }
        unset($tech);

        $this->render('admin/techniciens', [
            'pageTitle' => 'Gestion des Techniciens',
            'techniciens' => $techniciens,
            'flash' => $this->getFlash()
        ]);
    }
}
