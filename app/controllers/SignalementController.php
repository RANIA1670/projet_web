<?php
/**
 * CityZen - SignalementController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'models/SignalementModel.php';
require_once APP_PATH . 'models/CategorieModel.php';

class SignalementController extends Controller
{
    private SignalementModel $signalementModel;
    private CategorieModel $categorieModel;

    public function __construct()
    {
        $this->signalementModel = new SignalementModel();
        $this->categorieModel   = new CategorieModel();
    }

    public function index(array $params = []): void
    {
        $page     = max(1, (int)$this->get('page', 1));
        $perPage  = ITEMS_PER_PAGE;
        $filters  = [
            'statut'      => $this->get('statut', ''),
            'priorite'    => $this->get('priorite', ''),
            'categorie_id'=> $this->get('categorie_id', ''),
            'search'      => $this->get('search', ''),
        ];

        $signalements = $this->signalementModel->findAllWithDetails($page, $perPage, $filters);
        $total        = $this->signalementModel->countFiltered($filters);
        $totalPages   = (int)ceil($total / $perPage);
        $categories   = $this->categorieModel->findAll('nom', 'ASC');

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
        $categories = $this->categorieModel->findAll('nom', 'ASC');
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

        $data = [
            'user_id'      => $_SESSION['user_id'] ?? null,
            'titre'        => $titre,
            'description'  => $description,
            'categorie_id' => $categorie_id ?: null,
            'adresse'      => $adresse,
            'priorite'     => in_array($priorite, ['faible','moyenne','haute','urgente']) ? $priorite : 'moyenne',
            'date_incident'=> $date_incident,
            'statut'       => 'nouveau',
            'image'        => $imagePath,
            'latitude'     => $latitude ?: null,
            'longitude'    => $longitude ?: null,
        ];

        $id = $this->signalementModel->insert($data);
        if ($id) {
            $this->setFlash('success', 'Signalement créé avec succès ! Référence : #' . str_pad($id, 5, '0', STR_PAD_LEFT));
            $this->redirect('signalement/' . $id);
        } else {
            $this->setFlash('error', 'Erreur lors de l\'enregistrement du signalement.');
            $this->redirect('signalement/creer');
        }
    }

    public function show(array $params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        $signalement = $this->signalementModel->findByIdWithDetails($id);
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
        $id = (int)($params['id'] ?? 0);
        $signalement = $this->signalementModel->findByIdWithDetails($id);
        if (!$signalement) { $this->redirect('signalements'); return; }
        $categories = $this->categorieModel->findAll('nom', 'ASC');
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
        $id = (int)($params['id'] ?? 0);
        $signalement = $this->signalementModel->findById($id);
        if (!$signalement) { $this->redirect('signalements'); return; }

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

        $this->signalementModel->update($id, $data);
        $this->setFlash('success', 'Signalement mis à jour avec succès.');
        $this->redirect('signalement/' . $id);
    }

    public function destroy(array $params = []): void
    {
        $this->requireLogin();
        $id = (int)($params['id'] ?? 0);
        $user = $this->currentUser();
        
        $signalement = $this->signalementModel->findById($id);
        if (!$signalement) {
            $this->redirect('signalements');
            return;
        }

        // Seuls l'administrateur ou l'auteur du signalement peuvent le supprimer
        if ($user['role'] !== 'admin' && $user['id'] != $signalement['user_id']) {
            $this->setFlash('error', 'Vous n\'avez pas les droits nécessaires pour supprimer ce signalement.');
            $this->redirect('signalement/' . $id);
            return;
        }

        $this->signalementModel->delete($id);
        $this->setFlash('success', 'Le signalement #' . $id . ' a été supprimé.');

        // Redirection intelligente selon le rôle
        if ($user['role'] === 'admin') {
            $this->redirect('admin/signalements');
        } else {
            $this->redirect('signalements');
        }
    }

    public function apiList(array $params = []): void
    {
        $page    = max(1, (int)$this->get('page', 1));
        $filters = [
            'statut'      => $this->get('statut', ''),
            'priorite'    => $this->get('priorite', ''),
            'categorie_id'=> $this->get('categorie_id', ''),
            'search'      => $this->get('search', ''),
        ];
        $signalements = $this->signalementModel->findAllWithDetails($page, ITEMS_PER_PAGE, $filters);
        $total        = $this->signalementModel->countFiltered($filters);
        $this->json(['data' => $signalements, 'total' => $total, 'page' => $page]);
    }

    public function apiStats(array $params = []): void
    {
        $this->json([
            'total'      => $this->signalementModel->count(),
            'par_statut' => $this->signalementModel->getStatsByStatut(),
            'par_categorie' => $this->signalementModel->getStatsByCategorie(),
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
