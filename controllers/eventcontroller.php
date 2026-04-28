<?php
// ================================================
//  FICHIER  : controllers/EventController.php
//  RÔLE     : Gère toutes les actions liées aux events
// ================================================

require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/SponsorModel.php';
require_once __DIR__ . '/../models/ParticipationModel.php';
require_once __DIR__ . '/Validator.php';

class EventController
{
    private EventModel        $model;
    private SponsorModel      $sponsorModel;
    private ParticipationModel $participModel;

    public function __construct()
    {
        $this->model         = new EventModel();
        $this->sponsorModel  = new SponsorModel();
        $this->participModel = new ParticipationModel();
    }

    // ========== BACK OFFICE ==========

    public function backListe(): void
    {
        $filters = [
            'keyword'    => trim($_GET['keyword'] ?? ''),
            'lieu'       => trim($_GET['lieu'] ?? ''),
            'id_sponsor' => (int)($_GET['id_sponsor'] ?? 0),
            'date_from'  => $_GET['date_from'] ?? '',
            'date_to'    => $_GET['date_to'] ?? '',
        ];

        $events   = $this->model->findAll($filters);
        $sponsors = $this->sponsorModel->findAll();
        $msg      = $_GET['msg'] ?? '';

        $selectedEventId = (int)($_GET['event_id'] ?? 0);
        $selectedEvent = null;
        $selectedParticipants = [];

        if ($selectedEventId > 0) {
            $selectedEvent = $this->model->findById($selectedEventId);
            if ($selectedEvent) {
                $selectedParticipants = $this->participModel->findByEvent($selectedEventId);
            }
        }

        require __DIR__ . '/../views/back/event/liste.php';
    }

    public function backAjouter(): void
    {
        $sponsors = $this->sponsorModel->findAll();
        $erreurs  = [];
        $titre = $description = $date_event = $lieu = '';
        $id_sponsor = 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre       = Validator::nettoyer($_POST['titre']       ?? '');
            $description = Validator::nettoyer($_POST['description'] ?? '');
            $date_event  = Validator::nettoyer($_POST['date_event']  ?? '');
            $lieu        = Validator::nettoyer($_POST['lieu']        ?? '');
            $id_sponsor  = (int)($_POST['id_sponsor'] ?? 0);

            $v = new Validator();
            $v->requis($titre,       'Titre')
              ->minLen($titre, 3,    'Titre')
              ->requis($description, 'Description')
              ->requis($date_event,  'Date')
              ->date($date_event,    'Date')
              ->requis($lieu,        'Lieu')
              ->entierPositif($id_sponsor, 'Sponsor');

            if (!$v->aDesErreurs()) {
                $event = new EventModel($titre, $description, $date_event, $lieu, $id_sponsor);
                $event->create();
                header('Location: index.php?page=back_event_liste&msg=ajoute');
                exit;
            }
            $erreurs = $v->getErreurs();
        }
        require __DIR__ . '/../views/back/event/form.php';
    }

    public function backModifier(): void
    {
        $id    = (int)($_GET['id'] ?? 0);
        $event = $this->model->findById($id);
        if (!$event) { header('Location: index.php?page=back_event_liste'); exit; }

        $sponsors   = $this->sponsorModel->findAll();
        $erreurs    = [];
        $modeEdit   = true;
        $titre       = $event['titre'];
        $description = $event['description'];
        $date_event  = $event['date_event'];
        $lieu        = $event['lieu'];
        $id_sponsor  = $event['id_sponsor'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre       = Validator::nettoyer($_POST['titre']       ?? '');
            $description = Validator::nettoyer($_POST['description'] ?? '');
            $date_event  = Validator::nettoyer($_POST['date_event']  ?? '');
            $lieu        = Validator::nettoyer($_POST['lieu']        ?? '');
            $id_sponsor  = (int)($_POST['id_sponsor'] ?? 0);

            $v = new Validator();
            $v->requis($titre,       'Titre')
              ->requis($description, 'Description')
              ->requis($date_event,  'Date')
              ->date($date_event,    'Date')
              ->requis($lieu,        'Lieu')
              ->entierPositif($id_sponsor, 'Sponsor');

            if (!$v->aDesErreurs()) {
                $event = new EventModel($titre, $description, $date_event, $lieu, $id_sponsor, $id);
                $event->update();
                header('Location: index.php?page=back_event_liste&msg=modifie');
                exit;
            }
            $erreurs = $v->getErreurs();
        }
        require __DIR__ . '/../views/back/event/form.php';
    }

    public function backSupprimer(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $event = new EventModel('', '', '', '', 0, $id);
            $event->delete();
        }
        header('Location: index.php?page=back_event_liste&msg=supprime');
        exit;
    }

    public function backExportPdf(): void
    {
        require_once __DIR__ . '/../models/PdfGenerator.php';

        $filters = [
            'keyword'    => trim($_GET['keyword'] ?? ''),
            'lieu'       => trim($_GET['lieu'] ?? ''),
            'id_sponsor' => (int)($_GET['id_sponsor'] ?? 0),
            'date_from'  => $_GET['date_from'] ?? '',
            'date_to'    => $_GET['date_to'] ?? '',
        ];

        $events = $this->model->findAll($filters);
        $pdf = new PdfGenerator();
        $pdf->addTitle('Liste des événements et participants');

        if (empty($events)) {
            $pdf->addLine('Aucun événement ne correspond aux critères de recherche.');
        }

        foreach ($events as $event) {
            $pdf->addEmptyLine();
            $pdf->addLine("Événement #{$event['id_event']} — {$event['titre']}");
            $pdf->addLine("Date : {$event['date_event']}   Lieu : {$event['lieu']}   Sponsor : {$event['nom_sponsor']}");
            $pdf->addLine('Participants :');

            $participants = (new ParticipationModel())->findByEvent($event['id_event']);
            if (empty($participants)) {
                $pdf->addLine('  Aucun participant inscrit.');
            } else {
                foreach ($participants as $participant) {
                    $pdf->addLine('  - ' . $participant['nom_participant'] . ' (' . $participant['email_participant'] . ')');
                }
            }
        }

        $pdf->output('evenements_participants.pdf');
    }

    public function backEnvoyerRappels(): void
    {
        $events = $this->model->findEventsInDays(3);
        $sent = 0;
        $failed = 0;

        foreach ($events as $event) {
            $participants = (new ParticipationModel())->findByEvent($event['id_event']);
            foreach ($participants as $participant) {
                $to = $participant['email_participant'];
                $subject = 'Rappel : événement dans 3 jours – ' . $event['titre'];
                $message = "Bonjour {$participant['nom_participant']},\n\n" .
                           "Ceci est un rappel que l'événement \"{$event['titre']}\" aura lieu le {$event['date_event']} à {$event['lieu']}.\n\n" .
                           "Merci de votre participation,\nL'équipe CityZen";
                $headers = 'From: no-reply@cityzen.local\r\n' .
                           'Reply-To: no-reply@cityzen.local\r\n';

                if (@mail($to, $subject, $message, $headers)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        header('Location: index.php?page=back_dashboard&msg=rappels&sent=' . $sent . '&failed=' . $failed);
        exit;
    }

    // ========== FRONT OFFICE ==========

    public function frontListe(): void
    {
        $filters = [
            'keyword'    => trim($_GET['keyword'] ?? ''),
            'lieu'       => trim($_GET['lieu'] ?? ''),
            'id_sponsor' => (int)($_GET['id_sponsor'] ?? 0),
            'date_from'  => $_GET['date_from'] ?? '',
            'date_to'    => $_GET['date_to'] ?? '',
        ];

        $events   = $this->model->findAll($filters);
        $sponsors = $this->sponsorModel->findAll();
        require __DIR__ . '/../views/front/event/liste.php';
    }

    public function frontDetail(): void
    {
        $id    = (int)($_GET['id'] ?? 0);
        $event = $this->model->findById($id);
        if (!$event) { header('Location: index.php'); exit; }

        $participants = $this->participModel->findByEvent($id);

        // Traitement de l'inscription (formulaire participation)
        $erreurs = [];
        $succes = false;
        $nom_p = $email_p = $numero_p = $sexe_p = '';
        $age_p = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_p    = Validator::nettoyer($_POST['nom_participant']   ?? '');
            $email_p  = Validator::nettoyer($_POST['email_participant'] ?? '');
            $numero_p = Validator::nettoyer($_POST['numero_participant'] ?? '');
            $age_p    = Validator::nettoyer($_POST['age_participant']    ?? '');
            $sexe_p   = Validator::nettoyer($_POST['sexe_participant']   ?? '');

            $v = new Validator();
            $v->requis($nom_p,    'Nom')
              ->requis($email_p,  'Email')
              ->email($email_p,   'Email')
              ->requis($numero_p, 'Numéro')
              ->telephone($numero_p, 'Numéro')
              ->requis($age_p,    'Âge')
              ->entierPositif($age_p, 'Âge')
              ->requis($sexe_p,   'Sexe');

            if (!in_array($sexe_p, ['Homme', 'Femme', 'Autre'], true)) {
                $erreurs[] = 'Le champ « Sexe » est invalide.';
            }

            // Vérifier doublon
            if (empty($erreurs) && !$v->aDesErreurs()) {
                $participModel = new ParticipationModel($nom_p, $email_p, $id, $numero_p, (int)$age_p, $sexe_p);
                if ($participModel->existeDeja($email_p, $id)) {
                    $erreurs[] = 'Cet email est déjà inscrit à cet événement.';
                } else {
                    $participModel->create();
                    $succes       = true;
                    $participants = $this->participModel->findByEvent($id);
                }
            }

            if ($v->aDesErreurs()) {
                $erreurs = array_merge($erreurs, $v->getErreurs());
            }
        }
        require __DIR__ . '/../views/front/event/detail.php';
    }
}
