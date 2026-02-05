<?php

class TransactionRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(
        int $userId,
        int $categoryId,
        string $type,
        string $amount,
        ?string $description,
        string $occurredOn
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO transactions (user_id, category_id, type, amount, description, occurred_on)
             VALUES (:user_id, :category_id, :type, :amount, :description, :occurred_on)'
        );

        $stmt->execute([
            ':user_id' => $userId,
            ':category_id' => $categoryId,
            ':type' => $type,
            ':amount' => $amount,
            ':description' => $description,
            ':occurred_on' => $occurredOn,
        ]);
    }

    public function latestForUser(int $userId, int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.id, t.type, t.amount, t.description, t.occurred_on, c.name AS category_name
             FROM transactions t
             JOIN categories c ON c.id = t.category_id
             WHERE t.user_id = :user_id
             ORDER BY t.occurred_on DESC, t.id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function monthSummary(int $userId, string $monthStart, string $monthEndExclusive): array
{
    $stmt = $this->pdo->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount END), 0) AS income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS expense
         FROM transactions
         WHERE user_id = :user_id
           AND occurred_on >= :start
           AND occurred_on < :end"
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':start' => $monthStart,
        ':end' => $monthEndExclusive,
    ]);

    $row = $stmt->fetch() ?: ['income' => 0, 'expense' => 0];

    // Upewniamy się, że to liczby (Postgres często zwraca string dla NUMERIC)
    $income = (float)$row['income'];
    $expense = (float)$row['expense'];

    return [
        'income' => $income,
        'expense' => $expense,
        'balance' => $income - $expense,
    ];
}

public function expensesByCategory(int $userId, string $start, string $endExclusive): array
{
    $stmt = $this->pdo->prepare(
        "SELECT c.name AS category_name,
                COALESCE(SUM(t.amount), 0) AS total
         FROM transactions t
         JOIN categories c ON c.id = t.category_id
         WHERE t.user_id = :user_id
           AND t.type = 'expense'
           AND t.occurred_on >= :start
           AND t.occurred_on < :end
         GROUP BY c.name
         ORDER BY total DESC"
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':start'   => $start,
        ':end'     => $endExclusive,
    ]);

    $rows = $stmt->fetchAll() ?: [];

    // Postgres NUMERIC często wraca jako string -> rzutujemy
    foreach ($rows as &$r) {
        $r['total'] = (float)$r['total'];
    }

    return $rows;
}

public function listForUserInRangeFiltered(
    int $userId,
    string $start,
    string $endExclusive,
    int $categoryId = 0,
    string $query = '',
    int $limit = 50
): array {
    $sql =
        'SELECT t.id, t.type, t.category_id, t.amount, t.description, t.occurred_on, c.name AS category_name
         FROM transactions t
         JOIN categories c ON c.id = t.category_id
         WHERE t.user_id = :user_id
           AND t.occurred_on >= :start
           AND t.occurred_on < :end';

    if ($categoryId > 0) {
        $sql .= ' AND t.category_id = :category_id';
    }

    if ($query !== '') {
        $sql .= ' AND COALESCE(t.description, \'\') ILIKE :q';
    }

    $sql .= ' ORDER BY t.occurred_on DESC, t.id DESC LIMIT :limit';

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start);
    $stmt->bindValue(':end', $endExclusive);

    if ($categoryId > 0) {
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    }

    if ($query !== '') {
        $stmt->bindValue(':q', '%' . $query . '%', PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}


public function topExpensesForUserInRange(
    int $userId,
    string $start,
    string $endExclusive,
    int $categoryId = 0,
    string $query = '',
    int $limit = 5
): array {
    $sql =
        'SELECT t.id, t.amount, t.description, t.occurred_on, c.name AS category_name
         FROM transactions t
         JOIN categories c ON c.id = t.category_id
         WHERE t.user_id = :user_id
           AND t.type = \'expense\'
           AND t.occurred_on >= :start
           AND t.occurred_on < :end';

    if ($categoryId > 0) {
        $sql .= ' AND t.category_id = :category_id';
    }

    if ($query !== '') {
        $sql .= ' AND COALESCE(t.description, \'\') ILIKE :q';
    }

    $sql .= ' ORDER BY t.amount DESC, t.occurred_on DESC, t.id DESC LIMIT :limit';

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start);
    $stmt->bindValue(':end', $endExclusive);

    if ($categoryId > 0) {
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    }

    if ($query !== '') {
        $stmt->bindValue(':q', '%' . $query . '%', PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}





public function findForUser(int $txId, int $userId): ?array
{
    $stmt = $this->pdo->prepare(
        'SELECT id, user_id, category_id, type, amount, description, occurred_on
         FROM transactions
         WHERE id = :id AND user_id = :user_id
         LIMIT 1'
    );
    $stmt->execute([
        ':id' => $txId,
        ':user_id' => $userId,
    ]);

    $row = $stmt->fetch();
    return $row ?: null;
}

public function updateForUser(
    int $txId,
    int $userId,
    int $categoryId,
    string $type,
    string $amount,
    ?string $description,
    string $occurredOn
): void {
    $stmt = $this->pdo->prepare(
        'UPDATE transactions
         SET category_id = :category_id,
             type = :type,
             amount = :amount,
             description = :description,
             occurred_on = :occurred_on
         WHERE id = :id AND user_id = :user_id'
    );

    $stmt->execute([
        ':category_id' => $categoryId,
        ':type' => $type,
        ':amount' => $amount,
        ':description' => $description,
        ':occurred_on' => $occurredOn,
        ':id' => $txId,
        ':user_id' => $userId,
    ]);
}

public function deleteForUser(int $txId, int $userId): void
{
    $stmt = $this->pdo->prepare(
        'DELETE FROM transactions WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        ':id' => $txId,
        ':user_id' => $userId,
    ]);
}

public function update(int $userId, int $txId, array $data): array
{
    $type = $data['type'] ?? '';
    $categoryId = (int)($data['category_id'] ?? 0);
    $amount = trim((string)($data['amount'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));
    $occurredOn = $data['occurred_on'] ?? date('Y-m-d');

    $errors = [];

    // sprawdzenie czy transakcja należy do usera
    $stmt = $this->pdo->prepare(
        'SELECT id FROM transactions WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        ':id' => $txId,
        ':user_id' => $userId
    ]);

    if (!$stmt->fetch()) {
        return ['ok' => false, 'errors' => ['Nie znaleziono transakcji.']];
    }

    if (!in_array($type, ['income', 'expense'], true)) {
        $errors[] = 'Nieprawidłowy typ.';
    }

    if ($categoryId <= 0) {
        $errors[] = 'Nieprawidłowa kategoria.';
    }

    if ($amount === '' || !is_numeric($amount)) {
        $errors[] = 'Nieprawidłowa kwota.';
    }

    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $stmt = $this->pdo->prepare(
        'UPDATE transactions
         SET category_id = :category_id,
             type = :type,
             amount = :amount,
             description = :description,
             occurred_on = :occurred_on
         WHERE id = :id AND user_id = :user_id'
    );

    $stmt->execute([
        ':category_id' => $categoryId,
        ':type' => $type,
        ':amount' => $amount,
        ':description' => $description ?: null,
        ':occurred_on' => $occurredOn,
        ':id' => $txId,
        ':user_id' => $userId,
    ]);

    return ['ok' => true];
}


}
