<?php

namespace App\Controllers;

use App\Libraries\Core\Session;
use App\Models\User;

/**
 * Class AuthController
 *
 * Handles user authentication processes like login, registration, and logout.
 */
class AuthController extends BaseController
{
    protected Session $session;
    protected User $userModel;

    /**
     * AuthController constructor.
     * Initializes session and user model.
     */
    public function __construct()
    {
        $this->session = new Session();
        $this->userModel = new User();
    }

    /**
     * Displays the user registration page.
     */
    public function showRegistrationForm()
    {
        $this->render('auth.register', ['title' => 'Register'], 'auth');
    }

    /**
     * Handles the submission of the registration form.
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit();
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        $old_input = ['name' => $name, 'email' => $email];
        $errors = [];

        // --- Validation ---
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        if ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match.';
        }
        if ($this->userModel->findByEmail($email)) {
            $errors[] = 'An account with this email address already exists.';
        }

        if (!empty($errors)) {
            // Redisplay the form with errors and old input
            $this->render('auth.register', [
                'title' => 'Register',
                'errors' => $errors,
                'old' => $old_input
            ], 'auth');
            return;
        }

        // --- Create User ---
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $success = $this->userModel->create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        if ($success) {
            $this->session->flash('success', 'Registration successful! You can now log in.');
            header('Location: /login');
            exit();
        } else {
            $this->render('auth.register', [
                'title' => 'Register',
                'errors' => ['An unexpected error occurred. Please try again.'],
                'old' => $old_input
            ], 'auth');
        }
    }

    /**
     * Displays the user login page.
     */
    public function showLoginForm()
    {
        $this->render('auth.login', ['title' => 'Login'], 'auth');
    }

    /**
     * Handles the submission of the login form.
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, set up the session
            $this->session->set('user', [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]);
            // Redirect to the main dashboard
            header('Location: /');
            exit();
        } else {
            // Invalid credentials
            $this->session->flash('error', 'Invalid email or password.');
            header('Location: /login');
            exit();
        }
    }

    /**
     * Logs the user out by destroying the session.
     */
    public function logout()
    {
        $this->session->destroy();
        header('Location: /login');
        exit();
    }
}
