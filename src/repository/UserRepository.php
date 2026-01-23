<?php

class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(string $email, string $passwordHash): int
{
    $stmt = $this->pdo->prepare(
        'INSERT INTO users (email, password_hash) VALUES (:email, :hash) RETURNING id'
    );
    $stmt->execute([
        ':email' => $email,
        ':hash'  => $passwordHash,
    ]);

    return (int)$stmt->fetchColumn();
}


    public function findByEmail(string $email): ?array
{
    $stmt = $this->pdo->prepare(
        'SELECT id, email, password_hash FROM users WHERE email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $email]);

    $user = $stmt->fetch();
    return $user ?: null;
}

public function addCategory(int $userId, string $name): void
{
    $stmt = $this->pdo->prepare(
        'INSERT INTO categories (user_id, name) VALUES (:user_id, :name)'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':name' => $name
    ]);
}


}
