<?php

namespace App\Controllers\Mobile;

use App\Controllers\BaseController;
use App\Models\User as UserModel;

/**
 * Class MobileAuthController
 *
 * Handles authentication for mobile clients.
 */
class MobileAuthController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Handles mobile user login.
     * This would typically involve validating credentials and returning a token.
     */
    public function login()
    {
        header('Content-Type: application/json');

        // A real implementation would parse the request body for email/password
        // and return a JWT or other token.
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // In a real app, generate and return a JWT token here.
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.',
                'token' => 'dummy-auth-token-for-' . $user['name']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
        }
    }
}
