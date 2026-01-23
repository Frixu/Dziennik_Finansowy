<?php

class CategoryService
{
    public function __construct(private CategoryRepository $categories) {}

    public function listForUser(int $userId): array
    {
        return $this->categories->getAllForUser($userId);
    }

    public function add(int $userId, string $name): array
    {
        $name = trim($name);

        if ($name === '' || mb_strlen($name) > 100) {
            return ['ok' => false, 'error' => 'Podaj nazwę kategorii (1–100 znaków).'];
        }

        try {
            $this->categories->createForUser($userId, $name);
        } catch (PDOException $e) {
            // najczęściej duplikat (uniq_user_category)
            return ['ok' => false, 'error' => 'Taka kategoria już istnieje.'];
        }

        return ['ok' => true];
    }

    public function delete(int $userId, int $categoryId): array
    {
        if ($categoryId <= 0) {
            return ['ok' => false, 'error' => 'Nieprawidłowa kategoria.'];
        }

        // blokada usuwania, jeśli są transakcje
        $count = $this->categories->countTransactionsInCategory($categoryId, $userId);
        if ($count > 0) {
            return ['ok' => false, 'error' => 'Nie możesz usunąć kategorii, która ma transakcje.'];
        }

        $this->categories->deleteForUser($categoryId, $userId);
        return ['ok' => true];
    }
}
