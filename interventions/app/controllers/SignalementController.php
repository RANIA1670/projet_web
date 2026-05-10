<?php
/**
 * CityZen - SignalementController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'services/SignalementService.php';
require_once APP_PATH . 'services/CategorieService.php';
require_once APP_PATH . 'services/NotificationService.php';

class SignalementController extends Controller
{
    private SignalementService $signalementService;
    private CategorieService   $categorieService;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->signalementService = new SignalementService();
        $this->categorieService   = new CategorieService();
        $this->notificationService = new NotificationService();
    }

    public function index(array $params = []): void
    {
        $page    = max(1, (int)$this->get('page', 1));
        $perPage = ITEMS_PER_PAGE;
        $filters = [
            'statut'       => $this->get('statut', ''),
            'priorite'     => $this->get('priorite', ''),
            'categorie_id' => $this->get('categorie_id', ''),
            'search'       => $this->get('search', ''),
        ];

        $signalements = $this->signalementService->findAllWithDetails($page, $perPage, $filters);
        $total        = $this->signalementService->countFiltered($filters);
        $totalPages   = (int)ceil($total / $perPage);
        $categories   = $this->categorieService->getModel()->findAll('nom', 'ASC');

        $this->render('signalement/index', [
            'pageTitle'    => 'Liste des Signalements',
            'signalements' => $signalements,
            'categories'   => $categories,
            'filters'      => $filters,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'total'        => $total,
            'flash'        => $this->getFlash(),
        ]);
    }

    public function create(array $params = []): void
    {
        $categories = $this->categorieService->getModel()->findAll('nom', 'ASC');
        $this->render('signalement/create', [
            'pageTitle'  => 'Signaler un Problème',
            'categories' => $categories,
            'flash'      => $this->getFlash(),
        ]);
    }

    public function store(array $params = []): void
    {
        $titre        = $this->sanitize($this->input('titre', ''));
        $description  = $this->sanitize($this->input('description', ''));
        $categorie_id = (int)$this->input('categorie_id', 0);
        $adresse      = $this->sanitize($this->input('adresse', ''));
        $priorite     = $this->input('priorite', 'moyenne');
        $date_incident= $this->input('date_incident', date('Y-m-d'));
        $latitude     = $this->input('latitude', null);
        $longitude    = $this->input('longitude', null);

        if (empty($titre) || empty($description) || empty($adresse)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            $this->redirect('signalement/creer');
            return;
        }

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagePath = $this->handleUpload($_FILES['image']);
            if ($imagePath === false) {
                $this->setFlash('error', 'Erreur lors du téléchargement de l\'image.');
                $this->redirect('signalement/creer');
                return;
            }
        }

        $czUid = function_exists('cityzen_current_user_id') ? cityzen_current_user_id() : 0;
        $data = [
            'user_id'       => $czUid > 0 ? $czUid : null,
            'titre'         => $titre,
            'description'   => $description,
            'categorie_id'  => $categorie_id ?: null,
            'adresse'       => $adresse,
            'priorite'      => in_array($priorite, ['faible','moyenne','haute','urgente']) ? $priorite : 'moyenne',
            'date_incident' => $date_incident,
            'statut'        => 'nouveau',
            'image'         => $imagePath,
            'latitude'      => $latitude ?: null,
            'longitude'     => $longitude ?: null,
        ];

        $id = $this->signalementService->getModel()->insert($data);
        if ($id) {
            // Notifier les techniciens d'un nouveau signalement
            $this->notificationService->notifyTechnicians($id, $data['priorite']);

            $this->setFlash('success', 'Signalement créé avec succès ! Référence : #' . str_pad($id, 5, '0', STR_PAD_LEFT));
            $this->redirect('signalement/' . $id);
        } else {
            $this->setFlash('error', 'Erreur lors de l\'enregistrement du signalement.');
            $this->redirect('signalement/creer');
        }
    }

    public function show(array $params = []): void
    {
        $id          = (int)($params['id'] ?? 0);
        $signalement = $this->signalementService->findByIdWithDetails($id);
        if (!$signalement) {
            http_response_code(404);
            require APP_PATH . 'views/errors/404.php';
            return;
        }
        $this->render('signalement/show', [
            'pageTitle'   => 'Signalement #' . str_pad($id, 5, '0', STR_PAD_LEFT),
            'signalement' => $signalement,
            'flash'       => $this->getFlash(),
        ]);
    }

    public function edit(array $params = []): void
    {
        $this->requireLogin();
        $id          = (int)($params['id'] ?? 0);
        $signalement = $this->signalementService->findByIdWithDetails($id);
        if (!$signalement) { $this->redirect('signalements'); return; }
        $categories = $this->categorieService->getModel()->findAll('nom', 'ASC');
        $this->render('signalement/edit', [
            'pageTitle'   => 'Modifier le Signalement',
            'signalement' => $signalement,
            'categories'  => $categories,
            'flash'       => $this->getFlash(),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->requireLogin();
        $id          = (int)($params['id'] ?? 0);
        $signalement = $this->signalementService->getModel()->findById($id);
        if (!$signalement) { $this->redirect('signalements'); return; }

        $ancienStatut = $signalement['statut'];

        $data = [
            'titre'        => $this->sanitize($this->input('titre', '')),
            'description'  => $this->sanitize($this->input('description', '')),
            'categorie_id' => (int)$this->input('categorie_id', 0) ?: null,
            'adresse'      => $this->sanitize($this->input('adresse', '')),
            'priorite'     => $this->input('priorite', 'moyenne'),
            'statut'       => $this->input('statut', 'nouveau'),
            'date_incident'=> $this->input('date_incident', date('Y-m-d')),
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imagePath = $this->handleUpload($_FILES['image']);
            if ($imagePath !== false) $data['image'] = $imagePath;
        }

        $this->signalementService->getModel()->update($id, $data);

        if ($ancienStatut !== $data['statut']) {
            $this->notificationService->notifyStatusChange($id, $ancienStatut, $data['statut']);
        }

        $this->setFlash('success', 'Signalement mis à jour avec succès.');
        $this->redirect('signalement/' . $id);
    }

    public function destroy(array $params = []): void
    {
        $id = (int) ($params['id'] ?? 0);
        $agent = function_exists('cityzen_is_agent') && cityzen_is_agent();
        $czUid = function_exists('cityzen_current_user_id') ? cityzen_current_user_id() : 0;

        $signalement = $this->signalementService->getModel()->findById($id);
        if (!$signalement) {
            $this->redirect('signalements');

            return;
        }

        if (!$agent && (int) ($signalement['user_id'] ?? 0) !== (int) $czUid) {
            $this->setFlash('error', 'Vous n\'avez pas les droits nécessaires pour supprimer ce signalement.');
            $this->redirect('signalement/' . $id);

            return;
        }

        $this->signalementService->getModel()->delete($id);
        $this->setFlash('success', 'Le signalement #' . $id . ' a été supprimé.');

        if ($agent) {
            $this->redirect('backoffice/signalements');
        } else {
            $this->redirect('signalements');
        }
    }

    public function apiList(array $params = []): void
    {
        $page    = max(1, (int)$this->get('page', 1));
        $filters = [
            'statut'       => $this->get('statut', ''),
            'priorite'     => $this->get('priorite', ''),
            'categorie_id' => $this->get('categorie_id', ''),
            'search'       => $this->get('search', ''),
        ];
        $signalements = $this->signalementService->findAllWithDetails($page, ITEMS_PER_PAGE, $filters);
        $total        = $this->signalementService->countFiltered($filters);
        $this->json(['data' => $signalements, 'total' => $total, 'page' => $page]);
    }

    public function apiStats(array $params = []): void
    {
        $this->json([
            'total'         => $this->signalementService->getModel()->count(),
            'par_statut'    => $this->signalementService->getStatsByStatut(),
            'par_categorie' => $this->signalementService->getStatsByCategorie(),
        ]);
    }

    private function handleUpload(array $file): string|false
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if ($file['size'] > MAX_FILE_SIZE) return false;
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) return false;
        if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
        $filename = 'sig_' . uniqid() . '.' . $ext;
        $dest     = UPLOAD_PATH . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) return false;
        return $filename;
    }
}


