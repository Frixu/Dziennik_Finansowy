<?php

class AuthService
{
    public function __construct(private UserRepository $users) {}

    public function register(string $email, string $password, string $passwordRepeat): array
    {
        $errors = [];

        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Nieprawidłowy email.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Hasło musi mieć minimum 8 znaków.';
        }

        if ($password !== $passwordRepeat) {
            $errors[] = 'Hasła nie są takie same.';
        }

        if (!$errors && $this->users->existsByEmail($email)) {
            $errors[] = 'Konto o takim emailu już istnieje.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->users->create($email, $hash);

        $userId = $this->users->create($email, $hash);

        $defaultCategories = ['Jedzenie','Transport','Rachunki','Rozrywka','Inne','Wynagrodzenie'];
        foreach ($defaultCategories as $name) {
        $this->users->addCategory($userId, $name);
        }

        return ['ok' => true, 'errors' => []]; 
    }

    public function login(string $email, string $password): array
{
    $email = trim($email);

    $user = $this->users->findByEmail($email);

    if (!$user) {
        return ['ok' => false, 'error' => 'Nieprawidłowy email lub hasło'];
    }

    if (!password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'error' => 'Nieprawidłowy email lub hasło'];
    }

    return ['ok' => true, 'user' => $user];
}

}
