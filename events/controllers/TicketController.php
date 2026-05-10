<?php
// ================================================
//  FICHIER  : controllers/TicketController.php
//  RÔLE     : Gère le téléchargement des billets PDF
// ================================================

require_once __DIR__ . '/../models/ParticipationModel.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/TicketGenerator.php';

class TicketController
{
    private ParticipationModel $participModel;
    private EventModel         $eventModel;

    public function __construct()
    {
        $this->participModel = new ParticipationModel();
        $this->eventModel    = new EventModel();
    }

    /**
     * Télécharge le billet PDF pour une participation donnée.
     * Route : ?page=ticket_download&id_participation=X
     */
    public function download(): void
    {
        $idParticipation = (int)($_GET['id_participation'] ?? 0);

        if ($idParticipation <= 0) {
            header('Location: index.php');
            exit;
        }

        // Récupérer la participation
        $participant = $this->participModel->findById($idParticipation);
        if (!$participant) {
            header('Location: index.php?page=front_event_liste&msg=billet_introuvable');
            exit;
        }

        // Récupérer l'événement associé
        $event = $this->eventModel->findById((int)$participant['id_event']);
        if (!$event) {
            header('Location: index.php?page=front_event_liste&msg=event_introuvable');
            exit;
        }

        // Générer et envoyer le PDF
        $generator = new TicketGenerator();
        $generator->output($participant, $event);
    }

    /**
     * Page de confirmation après inscription avec lien billet.
     * Route : ?page=ticket_confirmation&id_participation=X&id_event=Y
     */
    public function confirmation(): void
    {
        $idParticipation = (int)($_GET['id_participation'] ?? 0);
        $idEvent         = (int)($_GET['id_event'] ?? 0);

        if ($idParticipation <= 0 || $idEvent <= 0) {
            header('Location: index.php');
            exit;
        }

        $participant = $this->participModel->findById($idParticipation);
        $event       = $this->eventModel->findById($idEvent);

        if (!$participant || !$event) {
            header('Location: index.php');
            exit;
        }

        $titrePage = 'Confirmation d\'inscription';
        require __DIR__ . '/../views/front/ticket/confirmation.php';
    }
}
