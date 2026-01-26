<?php

class AuthController
{
    public function __construct(private AuthService $authService) {}

    public function showLogin(array $data = []): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $error = $data['error'] ?? null;

    require __DIR__ . '/../../public/views/auth/login.php';
}

public function showRegister(array $data = []): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $errors = $data['errors'] ?? [];

    require __DIR__ . '/../../public/views/auth/register.php';
}

private function requireValidCsrf(): void
{
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        echo 'Nieprawidłowy token CSRF';
        exit;
    }
}

public function register(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Location: /register');
        exit;
    }

    $this->requireValidCsrf();

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordRepeat = $_POST['password_repeat'] ?? '';

    $result = $this->authService->register($email, $password, $passwordRepeat);

    if (!$result['ok']) {
        $this->showRegister(['errors' => $result['errors']]);
        return;
    }

    header('Location: /login?registered=1');
    exit;
}

public function login(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Location: /login');
        exit;
    }

    $this->requireValidCsrf();

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = $this->authService->login($email, $password);

    if (!$result['ok']) {
        $this->showLogin(['error' => $result['error']]);
        return;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['user_email'] = $result['user']['email'];

    // nowy token po zalogowaniu
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header('Location: /dashboard');
    exit;
}

}
