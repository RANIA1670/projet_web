<?php
/**
 * CityZen - HomeController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'models/SignalementModel.php';
require_once APP_PATH . 'models/InterventionModel.php';
require_once APP_PATH . 'models/CategorieModel.php';

class HomeController extends Controller
{
    private SignalementModel $signalementModel;
    private InterventionModel $interventionModel;
    private CategorieModel $categorieModel;

    public function __construct()
    {
        $this->signalementModel  = new SignalementModel();
        $this->interventionModel = new InterventionModel();
        $this->categorieModel    = new CategorieModel();
    }

    public function index(array $params = []): void
    {
        $stats = [
            'total_signalements'  => $this->signalementModel->count(),
            'en_cours'            => $this->signalementModel->countWhere(['statut' => 'en_cours']),
            'resolus'             => $this->signalementModel->countWhere(['statut' => 'resolu']),
            'interventions'       => $this->interventionModel->count(),
        ];

        $statsByStatut    = $this->signalementModel->getStatsByStatut();
        $statsByCategorie = $this->signalementModel->getStatsByCategorie();
        $recentSignalements = $this->signalementModel->getRecent(6);
        $categories       = $this->categorieModel->findAllWithCount();

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
