<?php
/**
 * CityZen - ContactController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'models/ContactModel.php';

class ContactController extends Controller
{
    private ContactModel $contactModel;

    public function __construct()
    {
        $this->contactModel = new ContactModel();
    }

    public function index(array $params = []): void
    {
        $this->render('contact/index', [
            'pageTitle' => 'Contact',
            'flash'     => $this->getFlash(),
        ]);
    }

    public function send(array $params = []): void
    {
        $nom     = $this->sanitize($this->input('nom', ''));
        $email   = filter_var($this->input('email', ''), FILTER_SANITIZE_EMAIL);
        $sujet   = $this->sanitize($this->input('sujet', ''));
        $message = $this->sanitize($this->input('message', ''));

        if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Veuillez remplir tous les champs.'], 400);
            } else {
                $this->setFlash('error', 'Veuillez remplir tous les champs.');
                $this->redirect('contact');
            }
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Adresse email invalide.'], 400);
            } else {
                $this->setFlash('error', 'Adresse email invalide.');
                $this->redirect('contact');
            }
            return;
        }

        $id = $this->contactModel->insert([
            'nom'     => $nom,
            'email'   => $email,
            'sujet'   => $sujet,
            'message' => $message,
            'statut'  => 'non_lu',
        ]);

        if ($this->isAjax()) {
            $this->json(['success' => (bool)$id, 'message' => $id ? 'Message envoyé avec succès !' : 'Erreur lors de l\'envoi.']);
        } else {
            $this->setFlash($id ? 'success' : 'error', $id ? 'Votre message a été envoyé avec succès !' : 'Erreur lors de l\'envoi.');
            $this->redirect('contact');
        }
    }
}
