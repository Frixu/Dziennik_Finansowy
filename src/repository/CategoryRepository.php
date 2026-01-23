<?php

class CategoryRepository
{
    public function __construct(private PDO $pdo) {}

    public function getAllForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name FROM categories WHERE user_id = :user_id ORDER BY name'
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function belongsToUser(int $categoryId, int $userId): bool
{
    $stmt = $this->pdo->prepare(
        'SELECT 1 FROM categories WHERE id = :id AND user_id = :user_id LIMIT 1'
    );
    $stmt->execute([
        ':id' => $categoryId,
        ':user_id' => $userId
    ]);
    return (bool)$stmt->fetchColumn();
}

public function createForUser(int $userId, string $name): void
{
    $stmt = $this->pdo->prepare(
        'INSERT INTO categories (user_id, name) VALUES (:user_id, :name)'
    );
    $stmt->execute([
        ':user_id' => $userId,
        ':name' => $name
    ]);
}

public function countTransactionsInCategory(int $categoryId, int $userId): int
{
    $stmt = $this->pdo->prepare(
        'SELECT COUNT(*) 
         FROM transactions t
         JOIN categories c ON c.id = t.category_id
         WHERE t.category_id = :category_id AND c.user_id = :user_id'
    );
    $stmt->execute([
        ':category_id' => $categoryId,
        ':user_id' => $userId
    ]);
    return (int)$stmt->fetchColumn();
}

public function deleteForUser(int $categoryId, int $userId): void
{
    $stmt = $this->pdo->prepare(
        'DELETE FROM categories WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        ':id' => $categoryId,
        ':user_id' => $userId
    ]);
}


}
