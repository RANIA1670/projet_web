<?php
/**
 * CityZen - HomeController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'services/SignalementService.php';
require_once APP_PATH . 'services/InterventionService.php';
require_once APP_PATH . 'services/CategorieService.php';

class HomeController extends Controller
{
    private SignalementService  $signalementService;
    private InterventionService $interventionService;
    private CategorieService    $categorieService;

    public function __construct()
    {
        $this->signalementService  = new SignalementService();
        $this->interventionService = new InterventionService();
        $this->categorieService    = new CategorieService();
    }

    public function index(array $params = []): void
    {
        $stats = [
            'total_signalements' => $this->signalementService->getModel()->count(),
            'en_cours'           => $this->signalementService->getModel()->countWhere(['statut' => 'en_cours']),
            'resolus'            => $this->signalementService->getModel()->countWhere(['statut' => 'resolu']),
            'interventions'      => $this->interventionService->getModel()->count(),
        ];

        $statsByStatut    = $this->signalementService->getStatsByStatut();
        $statsByCategorie = $this->signalementService->getStatsByCategorie();
        $recentSignalements = $this->signalementService->getRecent(6);
        $categories       = $this->categorieService->findAllWithCount();

        $this->render('home/index', [
            'pageTitle'          => 'Accueil',
            'stats'              => $stats,
            'statsByStatut'      => $statsByStatut,
            'statsByCategorie'   => $statsByCategorie,
            'recentSignalements' => $recentSignalements,
            'categories'         => $categories,
            'flash'              => $this->getFlash(),
        ]);
    }
}
