<?php
// ================================================
//  FICHIER  : controllers/AvisController.php
//  RÔLE     : Gère la notation des événements (front + back)
// ================================================

require_once __DIR__ . '/../models/AvisModel.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/ParticipationModel.php';
require_once __DIR__ . '/../controllers/Validator.php';

class AvisController
{
    private AvisModel         $avisModel;
    private EventModel        $eventModel;
    private ParticipationModel $participModel;

    public function __construct()
    {
        $this->avisModel     = new AvisModel();
        $this->eventModel    = new EventModel();
        $this->participModel = new ParticipationModel();
    }

    // ========== FRONT OFFICE ==========

    /**
     * Formulaire de notation d'un événement.
     * Route : ?page=front_avis_noter&id_event=X&id_participation=Y
     */
    public function frontNoter(): void
    {
        $idEvent         = (int)($_GET['id_event']         ?? 0);
        $idParticipation = (int)($_GET['id_participation'] ?? 0);

        $event       = $this->eventModel->findById($idEvent);
        $participant = $idParticipation > 0
                        ? $this->participModel->findById($idParticipation)
                        : null;

        if (!$event) {
            header('Location: index.php?page=front_event_liste');
            exit;
        }

        $erreurs    = [];
        $succes     = false;
        $dejaNote   = $idParticipation > 0
                       ? $this->avisModel->dejaNote($idEvent, $idParticipation)
                       : false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$dejaNote) {
            $note        = (int)($_POST['note']        ?? 0);
            $commentaire = Validator::nettoyer($_POST['commentaire'] ?? '');

            if ($note < 1 || $note > 5) {
                $erreurs[] = 'Veuillez choisir une note entre 1 et 5 étoiles.';
            }
            if (strlen($commentaire) < 5) {
                $erreurs[] = 'Le commentaire doit faire au moins 5 caractères.';
            }
            if (strlen($commentaire) > 1000) {
                $erreurs[] = 'Le commentaire ne peut pas dépasser 1000 caractères.';
            }

            if (empty($erreurs)) {
                $avis = new AvisModel($idEvent, $idParticipation, $note, $commentaire, 0);
                $avis->create();
                $succes = true;
            }
        }

        $titrePage = 'Laisser un avis';
        require __DIR__ . '/../views/front/event/avis.php';
    }

    // ========== BACK OFFICE ==========

    /**
     * Liste de tous les avis avec modération.
     * Route : ?page=back_avis_liste
     */
    public function backListe(): void
    {
        $filters = [
            'approuve' => $_GET['approuve'] ?? '',
            'id_event' => (int)($_GET['id_event'] ?? 0),
        ];

        $avis    = $this->avisModel->findAll($filters);
        $events  = $this->eventModel->findAll();
        $pending = $this->avisModel->countPending();
        $msg     = $_GET['msg'] ?? '';

        $titrePage = 'Modération des avis';
        require __DIR__ . '/../views/back/avis/liste.php';
    }

    /**
     * Approuver un avis.
     * Route : ?page=back_avis_approuver&id=X
     */
    public function backApprouver(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->avisModel->approuver($id);
        }
        header('Location: index.php?page=back_avis_liste&msg=approuve');
        exit;
    }

    /**
     * Rejeter (supprimer) un avis.
     * Route : ?page=back_avis_rejeter&id=X
     */
    public function backRejeter(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->avisModel->rejeter($id);
        }
        header('Location: index.php?page=back_avis_liste&msg=rejete');
        exit;
    }
}
