<?php
// ================================================
//  FICHIER  : controllers/ParticipationController.php
//  RÔLE     : Gère les actions sur les participations
// ================================================

require_once __DIR__ . '/../models/ParticipationModel.php';
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/Validator.php';

class ParticipationController
{
    private ParticipationModel $model;
    private EventModel         $eventModel;

    public function __construct()
    {
        $this->model      = new ParticipationModel();
        $this->eventModel = new EventModel();
    }

    // ========== BACK OFFICE ==========

    public function backListe(): void
    {
        $filters = [
            'keyword'   => trim($_GET['keyword'] ?? ''),
            'id_event'  => (int)($_GET['id_event'] ?? 0),
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
        ];

        $participations = $this->model->findAll($filters);
        $events         = $this->eventModel->findAll();
        $msg            = $_GET['msg'] ?? '';
        require __DIR__ . '/../views/back/participation/liste.php';
    }

    public function backAjouter(): void
    {
        $events  = $this->eventModel->findAll();
        $erreurs = [];
        $nom_participant = $email_participant = '';
        $id_event = 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_participant   = Validator::nettoyer($_POST['nom_participant']   ?? '');
            $email_participant = Validator::nettoyer($_POST['email_participant'] ?? '');
            $id_event          = (int)($_POST['id_event'] ?? 0);

            $v = new Validator();
            $v->requis($nom_participant,   'Nom du participant')
              ->minLen($nom_participant, 2,'Nom du participant')
              ->requis($email_participant, 'Email')
              ->email($email_participant,  'Email')
              ->entierPositif($id_event,   'Événement');

            $participation = new ParticipationModel($nom_participant, $email_participant, $id_event);
            if (!$v->aDesErreurs() && $participation->existeDeja($email_participant, $id_event)) {
                $erreurs[] = 'Cet email est déjà inscrit à cet événement.';
            }

            if (empty($erreurs) && !$v->aDesErreurs()) {
                $participation->create();
                header('Location: index.php?page=back_participation_liste&msg=ajoute');
                exit;
            }
            if ($v->aDesErreurs()) $erreurs = array_merge($erreurs, $v->getErreurs());
        }
        require __DIR__ . '/../views/back/participation/form.php';
    }

    public function backModifier(): void
    {
        $id            = (int)($_GET['id'] ?? 0);
        $participation = $this->model->findById($id);
        if (!$participation) { header('Location: index.php?page=back_participation_liste'); exit; }

        $events  = $this->eventModel->findAll();
        $erreurs = [];
        $modeEdit          = true;
        $nom_participant   = $participation['nom_participant'];
        $email_participant = $participation['email_participant'];
        $id_event          = $participation['id_event'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_participant   = Validator::nettoyer($_POST['nom_participant']   ?? '');
            $email_participant = Validator::nettoyer($_POST['email_participant'] ?? '');
            $id_event          = (int)($_POST['id_event'] ?? 0);

            $v = new Validator();
            $v->requis($nom_participant,   'Nom du participant')
              ->requis($email_participant, 'Email')
              ->email($email_participant,  'Email')
              ->entierPositif($id_event,   'Événement');

            $participation = new ParticipationModel($nom_participant, $email_participant, $id_event, $id);
            if (!$v->aDesErreurs() && $participation->existeDeja($email_participant, $id_event, $id)) {
                $erreurs[] = 'Cet email est déjà inscrit à cet événement.';
            }

            if (empty($erreurs) && !$v->aDesErreurs()) {
                $participation->update();
                header('Location: index.php?page=back_participation_liste&msg=modifie');
                exit;
            }
            if ($v->aDesErreurs()) $erreurs = array_merge($erreurs, $v->getErreurs());
        }
        require __DIR__ . '/../views/back/participation/form.php';
    }

    public function backSupprimer(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $participation = new ParticipationModel('', '', 0, $id);
            $participation->delete();
        }
        header('Location: index.php?page=back_participation_liste&msg=supprime');
        exit;
    }
}
