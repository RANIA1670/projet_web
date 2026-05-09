<?php
// ================================================
//  FICHIER  : controllers/SponsorController.php
//  RÔLE     : Reçoit les requêtes (GET/POST) liées
//             aux sponsors et appelle le bon modèle
//             puis la bonne vue
// ================================================

require_once __DIR__ . '/../models/SponsorModel.php';
require_once __DIR__ . '/Validator.php';

class SponsorController
{
    private SponsorModel $model;

    public function __construct()
    {
        $this->model = new SponsorModel();
    }

    // ========== BACK OFFICE ==========

    /** Afficher la liste admin */
    public function backListe(): void
    {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
        ];

        $sponsors = $this->model->findAll($filters);
        $msg      = $_GET['msg'] ?? '';
        require __DIR__ . '/../views/back/sponsor/liste.php';
    }

    /** Formulaire + traitement Ajout */
    public function backAjouter(): void
    {
        $erreurs = []; $nom = $email = $telephone = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Nettoyer les données
            $nom       = Validator::nettoyer($_POST['nom']       ?? '');
            $email     = Validator::nettoyer($_POST['email']     ?? '');
            $telephone = Validator::nettoyer($_POST['telephone'] ?? '');

            // Valider (PHP pur, pas HTML5)
            $v = new Validator();
            $v->requis($nom,       'Nom')
              ->minLen($nom, 2,    'Nom')
              ->requis($email,     'Email')
              ->email($email,      'Email')
              ->requis($telephone, 'Téléphone')
              ->telephone($telephone, 'Téléphone');

            if (!$v->aDesErreurs()) {
                $sponsor = new SponsorModel($nom, $email, $telephone);
                $sponsor->create();
                header('Location: index.php?page=back_sponsor_liste&msg=ajoute');
                exit;
            }
            $erreurs = $v->getErreurs();
        }
        require __DIR__ . '/../views/back/sponsor/form.php';
    }

    /** Formulaire + traitement Modification */
    public function backModifier(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { header('Location: index.php?page=back_sponsor_liste'); exit; }

        $sponsor = $this->model->findById($id);
        if (!$sponsor) { header('Location: index.php?page=back_sponsor_liste'); exit; }

        $erreurs = [];
        $nom       = $sponsor['nom'];
        $email     = $sponsor['email'];
        $telephone = $sponsor['telephone'];
        $modeEdit  = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom       = Validator::nettoyer($_POST['nom']       ?? '');
            $email     = Validator::nettoyer($_POST['email']     ?? '');
            $telephone = Validator::nettoyer($_POST['telephone'] ?? '');

            $v = new Validator();
            $v->requis($nom,       'Nom')
              ->minLen($nom, 2,    'Nom')
              ->requis($email,     'Email')
              ->email($email,      'Email')
              ->requis($telephone, 'Téléphone')
              ->telephone($telephone, 'Téléphone');

            if (!$v->aDesErreurs()) {
                $sponsor = new SponsorModel($nom, $email, $telephone, $id);
                $sponsor->update();
                header('Location: index.php?page=back_sponsor_liste&msg=modifie');
                exit;
            }
            $erreurs = $v->getErreurs();
        }
        require __DIR__ . '/../views/back/sponsor/form.php';
    }

    /** Suppression */
    public function backSupprimer(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $sponsor = new SponsorModel('', '', '', $id);
            $sponsor->delete();
        }
        header('Location: index.php?page=back_sponsor_liste&msg=supprime');
        exit;
    }

    // ========== FRONT OFFICE ==========

    /** Afficher la liste publique des sponsors */
    public function frontListe(): void
    {
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
        ];

        $sponsors = $this->model->findAll($filters);
        require __DIR__ . '/../views/front/sponsor/liste.php';
    }
}
