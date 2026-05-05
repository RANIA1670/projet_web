<?php
/**
 * CityZen - SuiviController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'services/InterventionService.php';
require_once APP_PATH . 'services/SignalementService.php';

class SuiviController extends Controller
{
    private InterventionService $interventionService;
    private SignalementService  $signalementService;

    public function __construct()
    {
        $this->interventionService = new InterventionService();
        $this->signalementService  = new SignalementService();
    }

    public function index(array $params = []): void
    {
        $reference    = $this->get('reference', '');
        $intervention = null;
        $suivi        = [];

        if (!empty($reference)) {
            $id = (int)ltrim($reference, '#0');
            if ($id > 0) {
                $intervention = $this->interventionService->findByIdWithDetails($id);
                if ($intervention) {
                    $suivi = $this->interventionService->getSuivi($id);
                }
            }
            if (!$intervention) {
                $signalement = $this->signalementService->getModel()->findById($id);
                if ($signalement) {
                    $rows = $this->interventionService->getModel()->find(['signalement_id' => $id]);
                    if (!empty($rows)) {
                        $intervention = $this->interventionService->findByIdWithDetails($rows[0]['id']);
                        $suivi        = $this->interventionService->getSuivi($rows[0]['id']);
                    }
                }
            }
        }

        $recentInterventions = $this->interventionService->findAllWithDetails(1, 5);

        $this->render('suivi/index', [
            'pageTitle'           => 'Suivi d\'Intervention',
            'reference'           => $reference,
            'intervention'        => $intervention,
            'suivi'               => $suivi,
            'recentInterventions' => $recentInterventions,
            'flash'               => $this->getFlash(),
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
        $this->render('suivi/show', [
            'pageTitle'    => 'Suivi Intervention #' . str_pad($id, 5, '0', STR_PAD_LEFT),
            'intervention' => $intervention,
            'suivi'        => $suivi,
            'flash'        => $this->getFlash(),
        ]);
    }

    public function addComment(array $params = []): void
    {
        $this->requireLogin();
        $id          = (int)($params['id'] ?? 0);
        $statut      = $this->sanitize($this->input('statut', ''));
        $commentaire = $this->sanitize($this->input('commentaire', ''));
        $userId      = $_SESSION['user_id'] ?? null;

        if (!empty($commentaire)) {
            $this->interventionService->addSuivi($id, $statut, $commentaire, $userId);
        }

        if ($this->isAjax()) {
            $suivi = $this->interventionService->getSuivi($id);
            $this->json(['success' => true, 'suivi' => $suivi]);
        } else {
            $this->setFlash('success', 'Commentaire ajouté.');
            $this->redirect('suivi/' . $id);
        }
    }

    public function apiGet(array $params = []): void
    {
        $id           = (int)($params['id'] ?? 0);
        $intervention = $this->interventionService->findByIdWithDetails($id);
        if (!$intervention) { $this->json(['error' => 'Non trouvé'], 404); return; }
        $suivi = $this->interventionService->getSuivi($id);
        $this->json(['intervention' => $intervention, 'suivi' => $suivi]);
    }
}
