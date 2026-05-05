<?php
/**
 * CityZen - AdminController (Administration)
 * Contrôleur séparé du front office.
 * Utilise le layout 'admin' et les Services.
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'services/SignalementService.php';
require_once APP_PATH . 'services/InterventionService.php';
require_once APP_PATH . 'services/UserService.php';

class AdminController extends Controller
{
    private SignalementService  $signalementService;
    private InterventionService $interventionService;
    private UserService         $userService;

    public function __construct()
    {
        $this->requireAdmin();
        $this->signalementService  = new SignalementService();
        $this->interventionService = new InterventionService();
        $this->userService         = new UserService();
    }

    private function requireAdmin(): void
    {
        $this->requireLogin();
        $user = $this->currentUser();
        if (!$user || $user['role'] !== 'admin') {
            $this->setFlash('error', 'Accès refusé. Vous devez être administrateur.');
            $this->redirect('/');
        }
    }

    // ─── DASHBOARD ────────────────────────────────────────────────────────────

    public function dashboard(array $params = []): void
    {
        $statsSignalements           = $this->signalementService->getStatsByStatut();
        $statsSignalementsByCategorie = $this->signalementService->getStatsByCategorie();
        $statsSignalementsTrend       = $this->signalementService->getTrendByDay(10);
        $statsInterventions           = $this->interventionService->getStatsByStatut();
        $statsInterventionsByTech     = $this->interventionService->getStatsByTechnician();
        $statsInterventionsMonthly    = $this->interventionService->getMonthlyCounts(6);

        $totalSignalements  = array_sum(array_column($statsSignalements, 'total'));
        $totalInterventions = array_sum(array_column($statsInterventions, 'total'));
        $citoyens           = $this->userService->getModel()->countWhere(['role' => 'citoyen']);
        $techniciens        = $this->userService->getModel()->countWhere(['role' => 'technicien']);

        $recentSignalements  = $this->signalementService->getRecent(5);
        $recentInterventions = $this->interventionService->findAllWithDetails(1, 5);

        $stats = [
            'total_signalements'           => $totalSignalements,
            'total_interventions'          => $totalInterventions,
            'citoyens'                     => $citoyens,
            'techniciens'                  => $techniciens,
            'signalements_by_status'       => $statsSignalements,
            'signalements_by_categorie'    => $statsSignalementsByCategorie,
            'signalements_trend'           => $statsSignalementsTrend,
            'interventions_by_status'      => $statsInterventions,
            'interventions_by_technician'  => $statsInterventionsByTech,
            'interventions_monthly'        => $statsInterventionsMonthly,
        ];

        $this->render('admin/index', [
            'pageTitle'           => 'Dashboard Administrateur',
            'stats'               => $stats,
            'totalSignalements'   => $totalSignalements,
            'totalInterventions'  => $totalInterventions,
            'recentSignalements'  => $recentSignalements,
            'recentInterventions' => $recentInterventions,
            'flash'               => $this->getFlash(),
        ], 'admin');
    }

    // ─── SIGNALEMENTS ─────────────────────────────────────────────────────────

    public function signalements(array $params = []): void
    {
        $page    = (int)$this->get('page', 1);
        $statut  = $this->get('statut', '');
        $filters = [];
        if ($statut) $filters['statut'] = $statut;

        $signalements = $this->signalementService->findAllWithDetails($page, 100, $filters);

        $this->render('admin/signalements', [
            'pageTitle'    => 'Gestion des Signalements',
            'signalements' => $signalements,
            'statut'       => $statut,
            'flash'        => $this->getFlash(),
        ], 'admin');
    }

    public function showSignalement(array $params = []): void
    {
        $id          = (int)($params['id'] ?? 0);
        $signalement = $this->signalementService->findByIdWithDetails($id);

        if (!$signalement) {
            http_response_code(404);
            require APP_PATH . 'views/errors/404.php';
            return;
        }

        $this->render('admin/signalement_show', [
            'pageTitle'   => 'Signalement #' . str_pad($id, 5, '0', STR_PAD_LEFT),
            'signalement' => $signalement,
            'flash'       => $this->getFlash(),
        ], 'admin');
    }

    // ─── INTERVENTIONS ────────────────────────────────────────────────────────

    public function interventions(array $params = []): void
    {
        $page    = max(1, (int)$this->get('page', 1));
        $filters = [
            'q'            => $this->get('q', ''),
            'statut'       => $this->get('statut', ''),
            'technicien_id'=> $this->get('technicien_id', ''),
        ];
        $sort      = $this->get('sort', 'created_at');
        $direction = $this->get('direction', 'DESC');
        $perPage   = 20;

        $interventions = $this->interventionService->searchWithDetails($filters, $sort, $direction, $page, $perPage);
        $totalItems    = $this->interventionService->countFiltered($filters);
        $totalPages    = max(1, (int)ceil($totalItems / $perPage));

        $this->render('admin/interventions', [
            'pageTitle'    => 'Gestion des Interventions',
            'interventions'=> $interventions,
            'techniciens'  => $this->userService->getTechniciens(),
            'filters'      => $filters,
            'sort'         => $sort,
            'direction'    => $direction,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
            'totalItems'   => $totalItems,
            'flash'        => $this->getFlash(),
        ], 'admin');
    }

    public function showIntervention(array $params = []): void
    {
        $id           = (int)($params['id'] ?? 0);
        $intervention = $this->interventionService->findByIdWithDetails($id);

        if (!$intervention) {
            $this->setFlash('error', 'Intervention introuvable.');
            $this->redirect('/admin/interventions');
        }

        $this->render('admin/intervention_show', [
            'pageTitle'    => 'Détail de l\'intervention',
            'intervention' => $intervention,
            'flash'        => $this->getFlash(),
        ], 'admin');
    }

    public function createIntervention(array $params = []): void
    {
        $signalements = $this->signalementService->findAllWithDetails(1, 100);
        $techniciens  = $this->userService->getTechniciens();

        $this->render('admin/intervention_form', [
            'pageTitle'    => 'Créer une intervention',
            'intervention' => null,
            'signalements' => $signalements,
            'techniciens'  => $techniciens,
            'action'       => 'create',
            'flash'        => $this->getFlash(),
        ], 'admin');
    }

    public function storeIntervention(array $params = []): void
    {
        $data = [
            'signalement_id' => (int)$this->input('signalement_id', 0),
            'technicien_id'  => (int)$this->input('technicien_id', 0),
            'date_planifiee' => $this->input('date_planifiee', ''),
            'statut'         => $this->input('statut', 'planifiee'),
            'notes'          => $this->sanitize($this->input('notes', '')),
        ];

        if ($data['signalement_id'] <= 0) {
            $this->setFlash('error', 'Veuillez sélectionner un signalement.');
            $this->redirect('/admin/intervention/creer');
        }

        $this->interventionService->getModel()->insert($data);
        $this->setFlash('success', 'Intervention créée avec succès.');
        $this->redirect('/admin/interventions');
    }

    public function editIntervention(array $params = []): void
    {
        $id           = (int)($params['id'] ?? 0);
        $intervention = $this->interventionService->findByIdWithDetails($id);

        if (!$intervention) {
            $this->setFlash('error', 'Intervention introuvable.');
            $this->redirect('/admin/interventions');
        }

        $this->render('admin/intervention_form', [
            'pageTitle'    => 'Modifier l\'intervention',
            'intervention' => $intervention,
            'signalements' => $this->signalementService->findAllWithDetails(1, 100),
            'techniciens'  => $this->userService->getTechniciens(),
            'action'       => 'edit',
            'flash'        => $this->getFlash(),
        ], 'admin');
    }

    public function updateIntervention(array $params = []): void
    {
        $id   = (int)($params['id'] ?? 0);
        $data = [
            'signalement_id' => (int)$this->input('signalement_id', 0),
            'technicien_id'  => (int)$this->input('technicien_id', 0),
            'date_planifiee' => $this->input('date_planifiee', ''),
            'statut'         => $this->input('statut', ''),
            'notes'          => $this->sanitize($this->input('notes', '')),
        ];

        if ($id <= 0 || $data['signalement_id'] <= 0) {
            $this->setFlash('error', 'Données invalides.');
            $this->redirect('/admin/interventions');
        }

        $this->interventionService->getModel()->update($id, $data);
        $this->setFlash('success', 'Intervention mise à jour.');
        $this->redirect('/admin/interventions');
    }

    public function assignTechnicien(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $techId = (int)$this->input('technicien_id', 0);

        if ($id > 0 && $techId > 0) {
            $this->interventionService->getModel()->update($id, ['technicien_id' => $techId]);
            $this->setFlash('success', 'Technicien assigné avec succès.');
        } else {
            $this->setFlash('error', 'Erreur lors de l\'assignation.');
        }

        $this->redirect('/admin/interventions');
    }

    public function updateInterventionStatus(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $statut = $this->input('statut', '');
        $notes  = $this->sanitize($this->input('notes', ''));

        if ($id > 0 && $statut) {
            $this->interventionService->getModel()->update($id, ['statut' => $statut, 'notes' => $notes]);
            $this->interventionService->addSuivi(
                $id,
                $statut,
                "Changement de statut par l'administrateur : " . $statut,
                $_SESSION['user_id'] ?? null
            );
            $this->setFlash('success', 'Statut mis à jour.');
        } else {
            $this->setFlash('error', 'Données invalides.');
        }

        $this->redirect('/admin/interventions');
    }

    public function deleteIntervention(array $params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            $this->interventionService->getModel()->delete($id);
            $this->setFlash('success', 'Intervention supprimée.');
        }
        $this->redirect('/admin/interventions');
    }

    // ─── TECHNICIENS ──────────────────────────────────────────────────────────

    public function techniciens(array $params = []): void
    {
        $allInterventions = $this->interventionService->findAllWithDetails(1, 1000);
        $techniciens      = $this->userService->getTechniciensWithStatus($allInterventions);

        $this->render('admin/techniciens', [
            'pageTitle'   => 'Gestion des Techniciens',
            'techniciens' => $techniciens,
            'flash'       => $this->getFlash(),
        ], 'admin');
    }

    public function createTechnicien(array $params = []): void
    {
        $this->render('admin/technicien_form', [
            'pageTitle'   => 'Ajouter un Technicien',
            'technicien'  => null,
            'flash'       => $this->getFlash(),
        ], 'admin');
    }

    public function storeTechnicien(array $params = []): void
    {
        $data = [
            'nom'       => $this->sanitize($this->input('nom')),
            'prenom'    => $this->sanitize($this->input('prenom')),
            'email'     => $this->sanitize($this->input('email')),
            'password'  => $this->input('password'),
            'role'      => 'technicien',
            'telephone' => $this->sanitize($this->input('telephone')),
        ];

        if ($this->userService->register($data)) {
            $this->setFlash('success', 'Technicien ajouté.');
            $this->redirect('/admin/techniciens');
        } else {
            $this->setFlash('error', 'Erreur lors de l\'ajout.');
            $this->redirect('/admin/technicien/creer');
        }
    }

    public function editTechnicien(array $params = []): void
    {
        $id         = (int)($params['id'] ?? 0);
        $technicien = $this->userService->getModel()->findById($id);
        if (!$technicien || $technicien['role'] !== 'technicien') {
            $this->redirect('/admin/techniciens');
        }

        $this->render('admin/technicien_form', [
            'pageTitle'  => 'Éditer Technicien',
            'technicien' => $technicien,
            'flash'      => $this->getFlash(),
        ], 'admin');
    }

    public function updateTechnicien(array $params = []): void
    {
        $id   = (int)($params['id'] ?? 0);
        $data = [
            'nom'       => $this->sanitize($this->input('nom')),
            'prenom'    => $this->sanitize($this->input('prenom')),
            'email'     => $this->sanitize($this->input('email')),
            'telephone' => $this->sanitize($this->input('telephone')),
        ];

        if ($this->input('password')) {
            $data['password'] = password_hash($this->input('password'), PASSWORD_DEFAULT);
        }

        if ($this->userService->getModel()->update($id, $data)) {
            $this->setFlash('success', 'Technicien mis à jour.');
        } else {
            $this->setFlash('error', 'Erreur lors de la mise à jour.');
        }
        $this->redirect('/admin/techniciens');
    }

    public function deleteTechnicien(array $params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        if ($id > 0) {
            $this->userService->getModel()->delete($id);
            $this->setFlash('success', 'Technicien supprimé.');
        }
        $this->redirect('/admin/techniciens');
    }

    // ─── EXPORT PDF ───────────────────────────────────────────────────────────

    public function exportPDF(array $params = []): void
    {
        $statsS = $this->signalementService->getStatsByStatut();
        $statsI = $this->interventionService->getStatsByStatut();
        $totalS = array_sum(array_column($statsS, 'total'));
        $totalI = array_sum(array_column($statsI, 'total'));
        $cit    = $this->userService->getModel()->countWhere(['role' => 'citoyen']);
        $tech   = $this->userService->getModel()->countWhere(['role' => 'technicien']);

        $lines = [
            'Dashboard Administrateur - CityZen',
            'Date : ' . date('d/m/Y H:i'),
            '',
            'Statistiques',
            'Total signalements : ' . $totalS,
            'Total interventions : ' . $totalI,
            'Citoyens : ' . $cit,
            'Techniciens : ' . $tech,
            '',
            'Signalements par statut :',
        ];

        foreach ($statsS as $stat) {
            $lines[] = ' - ' . ucfirst(str_replace('_', ' ', $stat['statut'])) . ' : ' . $stat['total'];
        }

        $lines[] = '';
        $lines[] = 'Interventions par statut :';

        foreach ($statsI as $stat) {
            $lines[] = ' - ' . ucfirst(str_replace('_', ' ', $stat['statut'])) . ' : ' . $stat['total'];
        }

        $pdfContent = $this->createSimplePdf($lines);

        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="dashboard_' . date('Y-m-d') . '.pdf"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function createSimplePdf(array $lines): string
    {
        $pageWidth  = 595;
        $pageHeight = 842;
        $content    = "BT\n/F1 14 Tf\n50 780 Td\n";

        foreach ($lines as $line) {
            $content .= "(" . $this->escapePdfText($line) . ") Tj\n0 -18 Td\n";
        }

        $content .= "ET";
        $contentLength = strlen($content);

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageWidth} {$pageHeight}] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Length {$contentLength} >>\nstream\n{$content}\nendstream\nendobj\n";

        $pdf = "%PDF-1.3\n";
        $offsets = [0 => 0];

        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = "xref\n0 " . (count($objects) + 1) . "\n";
        $xref .= sprintf("%010d 65535 f\n", 0);

        for ($i = 1; $i <= count($objects); $i++) {
            $xref .= sprintf("%010d 00000 n\n", $offsets[$i]);
        }

        $startXref = strlen($pdf);
        $pdf .= $xref;
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$startXref}\n%%EOF";

        return $pdf;
    }
}


