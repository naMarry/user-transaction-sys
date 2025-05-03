<?php

include_once(__DIR__ . '/../models/user.php');

use Dotenv\Dotenv;
use Firebase\JWT\Key;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

class UserController extends User
{

    private $userModel;
    private $data;

    public function __construct()
    {
        $this->userModel = new User();

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            //json
            $this->data = json_decode(file_get_contents('php://input'), true);
        } else {
            // formdata
            $this->data = $_POST;
        }
    }

    // admin access 
    public function generateAdmin()
    {
        $authHeader = $_COOKIE['access_token'] ?? null;

        $userData = $this->checkAuthorization($authHeader);
        $decoded = json_decode($userData);

        if (!$decoded->success || $decoded->user->role != 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorize! Only admins can create admin']);
            exit;
        }

        $username = $this->data['username'] ?? '';
        $password = $this->data['password'] ?? '';

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            return;
        }

        $verifyExitUsername = $this->userModel->getAdminByUsername($username);
        if (!empty($verifyExitUsername)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Username already exited']);
            return;
        }

        $create = $this->userModel->createAdmin($username, $password);
        if ($create) {
            echo json_encode(['success' => true, 'message' => 'Admin created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unable to create admin']);
        }
    }

    public function generateUser()
    {
        $authHeader = $_COOKIE['access_token'] ?? null;

        $userData = $this->checkAuthorization($authHeader);
        $decoded = json_decode($userData);

        if (!$decoded->success || $decoded->user->role != 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorize! Only admins can create users']);
            exit;
        }

        $username = $this->data['username'] ?? '';
        $password = $this->data['password'] ?? '';
        $balance = $this->data['balance'] ?? '';

        if (empty($username) || empty($password) || empty($balance)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username and password and balance are required']);
            return;
        }

        $create = $this->userModel->createUser($username, $password, $balance);
        if ($create) {
            echo json_encode(['success' => true, 'message' => 'User created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unable to create new user']);
        }
    }

    public function retreiveUser($id)
    {
        $authHeader = $_COOKIE['access_token'] ?? null;

        $userData = $this->checkAuthorization($authHeader);
        $decoded = json_decode($userData);

        if (!$decoded->success || $decoded->user->role != 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthoriz! Only admins can view all users']);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if ($user) {
            echo json_encode(['success' => true, 'message' => 'User retrieve successfully', 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No user found', 'user' => []]);
        }
    }

    // user access 
    public function getBalance($id)
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        $userData = json_decode($this->checkAuthorization($authHeader), true);
        if (!$userData['success']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (empty($user)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No user found', 'user' => []]);
            exit;
        }

        $balance = $user['balance'];

        if ($user) {
            echo json_encode(['success' => true, 'message' => 'Balance retrieve successfully', 'balance' => $balance]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unable to retreive balance']);
        }
    }

    public function depositBalance($id)
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        $userData = json_decode($this->checkAuthorization($authHeader), true);
        if (!$userData['success']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        $balance = $user['balance'];

        $amount = $this->data['amount'] ?? '';

        if (empty($balance) || empty($amount)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Balance and amount are required']);
            return;
        }

        $balance += $amount;

        $update = $this->userModel->updateBalanceById($balance, $id);
        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Deposit successfully', 'balance' => $balance]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unable to deposit balance', 'balance' => []]);
        }
    }

    public function withdrawBalance($id)
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        $userData = json_decode($this->checkAuthorization($authHeader), true);
        if (!$userData['success']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized user']);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        $balance = $user['balance'];

        $amount = $this->data['amount'] ?? '';

        if (empty($balance) || empty($amount)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Balance and amount are required']);
            return;
        }

        if ($amount > intval($balance)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Can not withdraw a larger amount than balance', 'balance' => $balance]);
            exit;
        }

        $balance -= $amount;

        $update = $this->userModel->updateBalanceById($balance, $id);
        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Withdraw successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unable to withdraw balance', 'balance' => []]);
        }
    }

    public function checkAuthorization($authHeader)
    {
        // $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_COOKIE['access_token'] ?? null;

        if (!$authHeader) {
            return json_encode(["success" => false, "message" => "Unauthorized user"]);
        } else {
            $token = str_replace('Bearer ', '', $authHeader);
            $key = $_ENV['SECRET_KEY'] ?? $_SERVER['SECRET_KEY'] ?? false;

            try {
                $decoded = Firebase\JWT\JWT::decode($token, new Key($key, 'HS256'));
                return json_encode(['success' => true, 'message' => 'Token valid', 'user' => $decoded->user]);
            } catch (Exception $e) {
                return json_encode(["success" => false, "message" => "Invalid token"]);
            }
        }
    }

    public function getAuthorizationUser()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            echo json_encode(['success' => false, "message" => "Unauthorized user"]);
        } else {
            $token = str_replace('Bearer ', '', $authHeader);
            $key = $_ENV['SECRET_KEY'] ?? $_SERVER['SECRET_KEY'] ?? false;

            try {
                $decoded = Firebase\JWT\JWT::decode($token, new Key($key, 'HS256'));
                $id = $decoded->user->id;

                if ($id) {
                    echo json_encode(['success' => true, 'id' => $id]);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'id' => []]);
                    exit;
                }
            } catch (Exception $e) {
                echo json_encode(["success" => false, "message" => "Invalid token"]);
            }
        }
    }

    public function generateToken($userData)
    {
        $key = $_ENV['SECRET_KEY'] ?? $_SERVER['SECRET_KEY'] ?? false;

        if (!$key) {
            echo json_encode(['success' => 'error', 'message' => 'JWT secret key is missing!']);
            return;
        }

        $accessTokenPayload = [
            "iss" => "localhost",
            "aud" => "localhost",
            "iat" => time(),
            "exp" => time() + 259200, //three days
            "user" => $userData
        ];

        $accessToken = Firebase\JWT\JWT::encode($accessTokenPayload, $key, 'HS256');
        setcookie("access_token", $accessToken, time() + (7 * 24 * 60 * 60), "/", "localhost", true, true);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $accessToken
        ]);
    }

    public function login()
    {
        $username = $this->data['username'] ?? null;
        $password = $this->data['password'] ?? null;

        $getUsername = $this->userModel->getAdminByUsername($username);

        if ($getUsername > 0) {

            $user = $this->userModel->getAdminByUsername($username);
            $userPassword = $user['password'];

            if (password_verify($password, $userPassword)) {
                // //get user
                $user = $this->userModel->getAdminByUsername($username);
                $userData = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];

                $this->generateToken($userData);
            } else {
                echo json_encode(['success' => false, 'message' => 'Wrong password!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Can not find email!']);
        }
    }

    public function logout()
    {
        setcookie("access_token", "", time() - 259200, "/", "localhost", true, false);
        echo json_encode(['success' => true, 'message' => 'Logout sucessfully']);
    }
}
