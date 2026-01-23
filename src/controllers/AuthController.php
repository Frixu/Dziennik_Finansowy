<?php

class AuthController
{
    public function __construct(private AuthService $authService) {}

    public function showLogin(): void
    {
        require __DIR__ . '/../../public/views/auth/login.php';
    }

    public function showRegister(array $data = []): void
    {
        // $data może zawierać errors itd.
        $errors = $data['errors'] ?? [];
        require __DIR__ . '/../../public/views/auth/register.php';
    }

    public function register(): void
    {
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
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = $this->authService->login($email, $password);

    if (!$result['ok']) {
        $error = $result['error'];
        require __DIR__ . '/../../public/views/auth/login.php';
        return;
    }

    // SESJA
    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['user_email'] = $result['user']['email'];

    header('Location: /dashboard');
    exit;
}

}
