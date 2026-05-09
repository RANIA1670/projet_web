<?php
// ================================================
//  FICHIER  : index.php  (Front Router)
//  RÔLE     : Point d'entrée unique du projet
//             Lit le paramètre ?page= et dispatch
//             vers le bon contrôleur/action
// ================================================

// Charger les contrôleurs
require_once __DIR__ . '/controllers/EventController.php';
require_once __DIR__ . '/controllers/SponsorController.php';
require_once __DIR__ . '/controllers/ParticipationController.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/controllers/AvisController.php';
require_once __DIR__ . '/controllers/ApiController.php';

// Lire la page demandée (défaut : accueil)
$page = $_GET['page'] ?? 'accueil';

// ---- Dispatch : choisir le bon contrôleur ----
switch ($page) {

    // ===== FRONT OFFICE =====
    case 'accueil':
        require __DIR__ . '/views/front/accueil.php';
        break;

    case 'front_event_liste':
        (new EventController())->frontListe();
        break;

    case 'front_event_detail':
        (new EventController())->frontDetail();
        break;

    case 'front_sponsor_liste':
        (new SponsorController())->frontListe();
        break;

    // ===== BACK OFFICE — EVENTS =====
    case 'back_event_liste':
        (new EventController())->backListe();
        break;

    case 'back_event_ajouter':
        (new EventController())->backAjouter();
        break;

    case 'back_event_modifier':
        (new EventController())->backModifier();
        break;

    case 'back_event_supprimer':
        (new EventController())->backSupprimer();
        break;

    case 'back_event_export_pdf':
        (new EventController())->backExportPdf();
        break;

    case 'back_event_envoyer_rappels':
        (new EventController())->backEnvoyerRappels();
        break;

    // ===== BACK OFFICE — SPONSORS =====
    case 'back_sponsor_liste':
        (new SponsorController())->backListe();
        break;

    case 'back_sponsor_ajouter':
        (new SponsorController())->backAjouter();
        break;

    case 'back_sponsor_modifier':
        (new SponsorController())->backModifier();
        break;

    case 'back_sponsor_supprimer':
        (new SponsorController())->backSupprimer();
        break;

    // ===== BACK OFFICE — PARTICIPATIONS =====
    case 'back_participation_liste':
        (new ParticipationController())->backListe();
        break;

    case 'back_participation_ajouter':
        (new ParticipationController())->backAjouter();
        break;

    case 'back_participation_modifier':
        (new ParticipationController())->backModifier();
        break;

    case 'back_participation_supprimer':
        (new ParticipationController())->backSupprimer();
        break;

    // ===== BACK OFFICE — Dashboard =====
    case 'back_dashboard':
        require __DIR__ . '/views/back/dashboard.php';
        break;

    // ===== BILLETS PDF + QR CODE =====
    case 'ticket_download':
        (new TicketController())->download();
        break;

    case 'ticket_confirmation':
        (new TicketController())->confirmation();
        break;

    // ===== AVIS & NOTATION =====
    case 'front_avis_noter':
        (new AvisController())->frontNoter();
        break;

    case 'back_avis_liste':
        (new AvisController())->backListe();
        break;

    case 'back_avis_approuver':
        (new AvisController())->backApprouver();
        break;

    case 'back_avis_rejeter':
        (new AvisController())->backRejeter();
        break;

    // ===== CALENDRIER INTERACTIF =====
    case 'front_calendrier':
        $titrePage = 'Calendrier';
        require __DIR__ . '/views/front/calendrier.php';
        break;

    // ===== API JSON (FullCalendar) =====
    case 'api_events_json':
        (new ApiController())->eventsJson();
        break;

    default:
        require __DIR__ . '/views/front/accueil.php';
        break;
}
