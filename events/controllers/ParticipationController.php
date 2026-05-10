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
        $nom_participant = $email_participant = $numero_participant = $sexe_participant = '';
        $age_participant = '';
        $id_event = 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_participant    = Validator::nettoyer($_POST['nom_participant']    ?? '');
            $email_participant  = Validator::nettoyer($_POST['email_participant']  ?? '');
            $numero_participant = Validator::nettoyer($_POST['numero_participant'] ?? '');
            $age_participant    = Validator::nettoyer($_POST['age_participant']    ?? '');
            $sexe_participant   = Validator::nettoyer($_POST['sexe_participant']   ?? '');
            $id_event           = (int)($_POST['id_event'] ?? 0);

            $v = new Validator();
            $v->requis($nom_participant,   'Nom du participant')
              ->minLen($nom_participant, 2,'Nom du participant')
              ->requis($email_participant, 'Email')
              ->email($email_participant,  'Email')
              ->requis($numero_participant,'Numéro')
              ->telephone($numero_participant, 'Numéro')
              ->requis($age_participant,   'Âge')
              ->entierPositif($age_participant, 'Âge')
              ->requis($sexe_participant,  'Sexe')
              ->entierPositif($id_event,   'Événement');

            if (!in_array($sexe_participant, ['Homme', 'Femme', 'Autre'], true)) {
                $erreurs[] = 'Le champ « Sexe » est invalide.';
            }

            $participation = new ParticipationModel(
                $nom_participant,
                $email_participant,
                $id_event,
                $numero_participant,
                (int)$age_participant,
                $sexe_participant
            );
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
        $numero_participant = $participation['numero_participant'] ?? '';
        $age_participant    = $participation['age_participant'] ?? '';
        $sexe_participant   = $participation['sexe_participant'] ?? '';
        $id_event          = $participation['id_event'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom_participant    = Validator::nettoyer($_POST['nom_participant']    ?? '');
            $email_participant  = Validator::nettoyer($_POST['email_participant']  ?? '');
            $numero_participant = Validator::nettoyer($_POST['numero_participant'] ?? '');
            $age_participant    = Validator::nettoyer($_POST['age_participant']    ?? '');
            $sexe_participant   = Validator::nettoyer($_POST['sexe_participant']   ?? '');
            $id_event           = (int)($_POST['id_event'] ?? 0);

            $v = new Validator();
            $v->requis($nom_participant,   'Nom du participant')
              ->requis($email_participant, 'Email')
              ->email($email_participant,  'Email')
              ->requis($numero_participant,'Numéro')
              ->telephone($numero_participant, 'Numéro')
              ->requis($age_participant,   'Âge')
              ->entierPositif($age_participant, 'Âge')
              ->requis($sexe_participant,  'Sexe')
              ->entierPositif($id_event,   'Événement');

            if (!in_array($sexe_participant, ['Homme', 'Femme', 'Autre'], true)) {
                $erreurs[] = 'Le champ « Sexe » est invalide.';
            }

            $participation = new ParticipationModel(
                $nom_participant,
                $email_participant,
                $id_event,
                $numero_participant,
                (int)$age_participant,
                $sexe_participant,
                $id
            );
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
            $participation = new ParticipationModel('', '', 0, '', 0, '', $id);
            $participation->delete();
        }
        header('Location: index.php?page=back_participation_liste&msg=supprime');
        exit;
    }
}
