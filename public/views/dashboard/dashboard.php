<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="/styles/base.css">
  <link rel="stylesheet" href="/styles/dashboard.css">
</head>
<body>

<?php
  // Bezpieczniki, żeby widok nie walił warningami
  $months = $months ?? [1=>'Styczeń',2=>'Luty',3=>'Marzec',4=>'Kwiecień',5=>'Maj',6=>'Czerwiec',7=>'Lipiec',8=>'Sierpień',9=>'Wrzesień',10=>'Październik',11=>'Listopad',12=>'Grudzień'];
  $latestTransactions = $latestTransactions ?? [];
  $categories = $categories ?? [];
  $kpi = $kpi ?? ['income' => 0, 'expense' => 0, 'balance' => 0];
  $selectedMonth = (int)($selectedMonth ?? date('n'));
  $selectedYear = (int)($selectedYear ?? date('Y'));
?>

<div class="dashboard-shell">
  <div class="dashboard-container">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="brand">
        <h1>Dziennik finansowy</h1>
        <p>Zalogowany jako: <?= htmlspecialchars($_SESSION['user_email']) ?></p>
      </div>

      <div class="actions">
        <button class="btn btn-primary" id="openAddTx">+ Dodaj transakcję</button>

        <a class="btn btn-ghost" href="/categories" style="text-decoration:none; display:inline-flex; align-items:center;">
          Kategorie
        </a>

        <a class="btn btn-ghost" href="/logout" style="text-decoration:none; display:inline-flex; align-items:center;">
          Wyloguj
        </a>
      </div>
    </div>

    <!-- KPI CARDS -->
    <div class="grid kpi-grid">
      <div class="card">
        <p class="kpi-title">Saldo</p>
        <p class="kpi-value">
          <?= number_format(($kpi['balance'] ?? 0), 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          Zakres: <?= htmlspecialchars($months[$selectedMonth] ?? '') ?> <?= (int)$selectedYear ?>
        </p>
      </div>

      <div class="card">
        <p class="kpi-title">Przychody</p>
        <p class="kpi-value">
          <?= number_format(($kpi['income'] ?? 0), 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          Zakres: <?= htmlspecialchars($months[$selectedMonth] ?? '') ?> <?= (int)$selectedYear ?>
        </p>
      </div>

      <div class="card">
        <p class="kpi-title">Wydatki</p>
        <p class="kpi-value">
          <?= number_format(($kpi['expense'] ?? 0), 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          Zakres: <?= htmlspecialchars($months[$selectedMonth] ?? '') ?> <?= (int)$selectedYear ?>
        </p>
      </div>
    </div>

    <!-- HISTORIA -->
    <div class="section">
      <div class="section-header">
        <div>
          <h2>Historia transakcji</h2>
          <div class="section-sub">
            <?= htmlspecialchars($months[$selectedMonth] ?? '') ?> <?= (int)$selectedYear ?>
          </div>
        </div>

        <!-- FILTR PO PRAWEJ -->
        <form method="GET" action="/dashboard" class="filter-bar">
          <select name="month">
            <?php foreach ($months as $m => $label): ?>
              <option value="<?= $m ?>" <?= ($selectedMonth === (int)$m) ? 'selected' : '' ?>>
                <?= $label ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select name="year">
            <?php
              $currentYear = (int)date('Y');
              for ($y = $currentYear - 3; $y <= $currentYear + 1; $y++):
            ?>
              <option value="<?= $y ?>" <?= ($selectedYear === (int)$y) ? 'selected' : '' ?>>
                <?= $y ?>
              </option>
            <?php endfor; ?>
          </select>

          <button class="btn btn-ghost" type="submit">Filtruj</button>
        </form>
      </div>

      <div class="card table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Data</th>
              <th>Typ</th>
              <th>Kategoria</th>
              <th>Opis</th>
              <th>Kwota</th>
              <th>Akcje</th>
            </tr>
          </thead>

          <tbody>
            <?php if (empty($latestTransactions)): ?>
              <tr>
                <td colspan="6" style="color: rgba(0,0,0,0.55); padding: 18px 10px;">
                  Brak transakcji w wybranym okresie. Kliknij „+ Dodaj transakcję”.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($latestTransactions as $t): ?>
                <tr>
                  <td><?= htmlspecialchars($t['occurred_on']) ?></td>
                  <td>
                    <span class="badge"><?= ($t['type'] === 'income') ? 'Przychód' : 'Wydatek' ?></span>
                  </td>
                  <td><?= htmlspecialchars($t['category_name']) ?></td>
                  <td><?= htmlspecialchars($t['description'] ?? '') ?></td>
                  <td class="amount"><?= htmlspecialchars($t['amount']) ?> zł</td>

                  <td>
                    <button
                      type="button"
                      class="btn btn-ghost btn-small js-edit"
                      data-id="<?= (int)$t['id'] ?>"
                      data-type="<?= htmlspecialchars($t['type']) ?>"
                      data-category-id="<?= (int)$t['category_id'] ?>"
                      data-amount="<?= htmlspecialchars($t['amount']) ?>"
                      data-description="<?= htmlspecialchars($t['description'] ?? '') ?>"
                      data-occurred-on="<?= htmlspecialchars($t['occurred_on']) ?>"
                    >Edytuj</button>

                    <form method="POST" action="/transactions/delete" class="js-delete-form" style="display:inline; margin-left:8px;">
                      <input type="hidden" name="tx_id" value="<?= (int)$t['id'] ?>">
                      <input type="hidden" name="redirect_year" value="<?= (int)$selectedYear ?>">
                      <input type="hidden" name="redirect_month" value="<?= (int)$selectedMonth ?>">
                      <button type="submit" class="btn btn-ghost btn-small">Usuń</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- MODAL: DODAJ / EDYTUJ TRANSAKCJĘ -->
<div class="modal-backdrop" id="txModalBackdrop" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="txModalTitle">
    <div class="modal-header">
      <h3 id="txModalTitle">Dodaj transakcję</h3>
      <button class="modal-close" id="closeTxModal" aria-label="Zamknij">×</button>
    </div>

    <div class="modal-body">
      <form method="POST" action="/transactions" id="txForm">
        <input type="hidden" name="tx_id" id="tx_id" value="">
        <input type="hidden" name="redirect_year" value="<?= (int)$selectedYear ?>">
        <input type="hidden" name="redirect_month" value="<?= (int)$selectedMonth ?>">

        <div class="form-grid">

          <div class="form-row">
            <div>
              <label for="type">Typ</label>
              <select id="type" name="type">
                <option value="expense">Wydatek</option>
                <option value="income">Przychód</option>
              </select>
            </div>

            <div>
              <label for="occurred_on">Data</label>
              <input id="occurred_on" type="date" name="occurred_on" value="<?= date('Y-m-d') ?>">
            </div>
          </div>

          <div>
            <label for="category_id">Kategoria</label>
            <select id="category_id" name="category_id" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div>
              <label for="amount">Kwota</label>
              <input id="amount" type="number" step="0.01" min="0.01" name="amount" placeholder="np. 29.99" required>
            </div>
            <div>
              <label for="description">Opis</label>
              <input id="description" type="text" name="description" placeholder="np. Zakupy">
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn btn-ghost" id="closeTxModalSecondary">Anuluj</button>
            <button type="submit" class="btn btn-primary" id="txSubmitBtn">Zapisz</button>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>

<script src="/js/dashboard.js"></script>
<script>
  document.getElementById('closeTxModalSecondary')?.addEventListener('click', () => {
    document.getElementById('txModalBackdrop')?.classList.remove('is-open');
  });
</script>

</body>
</html>
