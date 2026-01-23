<?php

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /views/register.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordRepeat = $_POST['password_repeat'] ?? [];

$errors = [];

// backendowa walidacja
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Nieprawidłowy email';
}

if (strlen($password) < 8) {
    $errors[] = 'Hasło za krótkie';
}

if ($password !== $passwordRepeat) {
    $errors[] = 'Hasła się nie zgadzają';
}

if (!empty($errors)) {
    // na razie prosto
    die(implode('<br>', $errors));
}

// hash hasła
$hash = password_hash($password, PASSWORD_DEFAULT);

// zapis do bazy
$stmt = $pdo->prepare(
    'INSERT INTO users (email, password_hash) VALUES (:email, :hash)'
);

try {
    $stmt->execute([
        'email' => $email,
        'hash'  => $hash
    ]);
} catch (PDOException $e) {
    die('Email już istnieje');
}

header('Location: /views/login.php?registered=1');
exit;
