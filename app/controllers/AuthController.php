<?php
/**
 * CityZen - AuthController
 */

require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'models/UserModel.php';

class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function loginForm(array $params = []): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
        }
        $this->render('auth/login', [
            'pageTitle' => 'Connexion',
            'flash'     => $this->getFlash(),
        ]);
    }

    public function login(array $params = []): void
    {
        if ($this->isLoggedIn()) { $this->redirect('/'); }

        $email    = $this->input('email', '');
        $password = $this->input('password', '');

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Veuillez remplir tous les champs.');
            $this->redirect('auth/connexion');
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            $this->setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('auth/connexion');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['user_nom']    = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['user_role']   = $user['role'];

        $this->setFlash('success', 'Bienvenue ' . $user['prenom'] . ' ' . $user['nom'] . ' !');
        $this->redirect('/');
    }

    public function registerForm(array $params = []): void
    {
        if ($this->isLoggedIn()) { $this->redirect('/'); }
        $this->render('auth/register', [
            'pageTitle' => 'Créer un compte',
            'flash'     => $this->getFlash(),
        ]);
    }

    public function register(array $params = []): void
    {
        if ($this->isLoggedIn()) { $this->redirect('/'); }

        $nom      = $this->sanitize($this->input('nom', ''));
        $prenom   = $this->sanitize($this->input('prenom', ''));
        $email    = filter_var($this->input('email', ''), FILTER_SANITIZE_EMAIL);
        $password = $this->input('password', '');
        $confirm  = $this->input('password_confirm', '');
        $tel      = $this->sanitize($this->input('telephone', ''));

        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            $this->setFlash('error', 'Tous les champs obligatoires doivent être remplis.');
            $this->redirect('auth/inscription');
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Adresse email invalide.');
            $this->redirect('auth/inscription');
            return;
        }
        if (strlen($password) < 6) {
            $this->setFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
            $this->redirect('auth/inscription');
            return;
        }
        if ($password !== $confirm) {
            $this->setFlash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('auth/inscription');
            return;
        }
        if ($this->userModel->findByEmail($email)) {
            $this->setFlash('error', 'Cette adresse email est déjà utilisée.');
            $this->redirect('auth/inscription');
            return;
        }

        $id = $this->userModel->register([
            'nom'       => $nom,
            'prenom'    => $prenom,
            'email'     => $email,
            'password'  => $password,
            'telephone' => $tel,
            'role'      => 'citoyen',
        ]);

        if ($id) {
            $this->setFlash('success', 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.');
            $this->redirect('auth/connexion');
        } else {
            $this->setFlash('error', 'Erreur lors de la création du compte.');
            $this->redirect('auth/inscription');
        }
    }

    public function logout(array $params = []): void
    {
        session_destroy();
        header('Location: ' . APP_URL . '/');
        exit;
    }
}
