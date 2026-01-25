<?php

class TransactionService
{
    public function __construct(
        private TransactionRepository $txRepo,
        private CategoryRepository $catRepo
    ) {}

    public function add(int $userId, array $data): array
    {
        $type = $data['type'] ?? '';
        $categoryId = (int)($data['category_id'] ?? 0);
        $amount = trim((string)($data['amount'] ?? ''));
        $description = trim($data['description'] ?? '');

if (strlen($description) > 255) {
    return ['ok' => false, 'error' => 'Opis jest za długi (max 255 znaków).'];
}

        $occurredOn = $data['occurred_on'] ?? date('Y-m-d');

        $errors = [];

        if (!in_array($type, ['income', 'expense'], true)) {
            $errors[] = 'Nieprawidłowy typ transakcji.';
        }

        if ($categoryId <= 0 || !$this->catRepo->belongsToUser($categoryId, $userId)) {
            $errors[] = 'Nieprawidłowa kategoria.';
        }

        // kwota: prosta walidacja
        if ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
            $errors[] = 'Podaj poprawną kwotę większą od 0.';
        }

        // data: bardzo prosta walidacja formatu
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $occurredOn)) {
            $errors[] = 'Nieprawidłowa data.';
        }

        if ($errors) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->txRepo->create(
            $userId,
            $categoryId,
            $type,
            $amount,
            $description !== '' ? $description : null,
            $occurredOn
        );

        return ['ok' => true];
    }

    public function update(int $userId, int $txId, array $data): array
{
    $type = $data['type'] ?? '';
    $categoryId = (int)($data['category_id'] ?? 0);
    $amount = trim((string)($data['amount'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));

if (strlen($description) > 255) {
    return ['ok' => false, 'error' => 'Opis jest za długi (max 255 znaków).'];
}

    $occurredOn = $data['occurred_on'] ?? date('Y-m-d');

    $errors = [];

    if ($txId <= 0 || !$this->txRepo->findForUser($txId, $userId)) {
        $errors[] = 'Nie znaleziono transakcji.';
    }

    if (!in_array($type, ['income', 'expense'], true)) {
        $errors[] = 'Nieprawidłowy typ transakcji.';
    }

    if ($categoryId <= 0 || !$this->catRepo->belongsToUser($categoryId, $userId)) {
        $errors[] = 'Nieprawidłowa kategoria.';
    }

    if ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
        $errors[] = 'Podaj poprawną kwotę większą od 0.';
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $occurredOn)) {
        $errors[] = 'Nieprawidłowa data.';
    }

    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $this->txRepo->updateForUser(
        $txId,
        $userId,
        $categoryId,
        $type,
        $amount,
        $description !== '' ? $description : null,
        $occurredOn
    );

    return ['ok' => true];
}

public function delete(int $userId, int $txId): array
{
    if ($txId <= 0 || !$this->txRepo->findForUser($txId, $userId)) {
        return ['ok' => false, 'errors' => ['Nie znaleziono transakcji.']];
    }

    $this->txRepo->deleteForUser($txId, $userId);
    return ['ok' => true];
}

}
