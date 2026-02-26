<?php

class AuthController extends BaseController {
    private UserModel $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function loginPage(): void {
        if (Auth::check()) redirect('dashboard');
        include VIEWS_PATH . 'auth/login.php';
    }

    public function login(): void {
        $this->verifyCsrf();
        $email    = $this->post('email');
        $password = $_POST['password'] ?? '';
        if (empty($email) || empty($password)) {
            flash('error', 'Please enter email and password.');
            redirect('auth/login');
        }
        $user = $this->userModel->findByEmail($email);
        if (!$user || !$user['is_active']) {
            flash('error', 'Invalid credentials or account disabled.');
            redirect('auth/login');
        }
        if ($this->userModel->isLocked($user)) {
            flash('error', 'Account is temporarily locked. Try again in 15 minutes.');
            redirect('auth/login');
        }
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            $this->userModel->incrementLoginAttempts($user['id']);
            if ($user['login_attempts'] + 1 >= MAX_LOGIN_ATTEMPTS) {
                $this->userModel->lockAccount($user['id']);
                flash('error', 'Too many failed attempts. Account locked for 15 minutes.');
            } else {
                flash('error', 'Invalid credentials.');
            }
            redirect('auth/login');
        }
        $this->userModel->resetLoginAttempts($user['id']);
        Auth::login($user);
        $logger = new ActivityLogModel();
        $logger->log($user['id'], 'LOGIN', 'auth');
        redirect('dashboard');
    }

    public function logout(): void {
        $logger = new ActivityLogModel();
        $logger->log(Auth::id(), 'LOGOUT', 'auth');
        Auth::logout();
        redirect('auth/login');
    }
}
