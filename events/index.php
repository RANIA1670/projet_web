<?php

declare(strict_types=1);

/**
 * Module gestion d'événements (intégration branche ibrahim — routes ?page= ).
 */
require_once dirname(__DIR__) . '/core/Bootstrap.php';

App\Core\Bootstrap::init();

require_once dirname(__DIR__) . '/core/auth.php';
require_once dirname(__DIR__) . '/core/layout.php';
require_once dirname(__DIR__) . '/core/data.php';

cityzen_session_start();

$page = isset($_GET['page']) ? (string) $_GET['page'] : 'accueil';

if ($page !== '' && str_starts_with($page, 'back_')) {
    cityzen_require_agent();
}

require_once __DIR__ . '/controllers/EventController.php';
require_once __DIR__ . '/controllers/SponsorController.php';
require_once __DIR__ . '/controllers/ParticipationController.php';
require_once __DIR__ . '/controllers/TicketController.php';
require_once __DIR__ . '/controllers/AvisController.php';
require_once __DIR__ . '/controllers/ApiController.php';

switch ($page) {
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

    case 'back_dashboard':
        require __DIR__ . '/views/back/dashboard.php';
        break;

    case 'ticket_download':
        (new TicketController())->download();
        break;

    case 'ticket_confirmation':
        (new TicketController())->confirmation();
        break;

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

    case 'front_calendrier':
        $titrePage = 'Calendrier';
        require __DIR__ . '/views/front/calendrier.php';
        break;

    case 'api_events_json':
        (new ApiController())->eventsJson();
        break;

    default:
        require __DIR__ . '/views/front/accueil.php';
        break;
}
