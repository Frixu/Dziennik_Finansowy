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
        <p>Zalogowany jako: <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
      </div>

      <div class="actions">
        <!-- WAŻNE: type="button" żeby nigdy nie próbował submitować formularza -->
        <button type="button" class="btn btn-primary" id="openAddTx">+ Dodaj transakcję</button>
        <a class="btn btn-ghost" href="/categories">Kategorie</a>
        <a class="btn btn-ghost" href="/logout">Wyloguj</a>
      </div>
    </div>

    <!-- KPI -->
    <div class="grid kpi-grid">
      <div class="card">
        <p class="kpi-title">Saldo</p>
        <p class="kpi-value">
          <?= number_format((float)($kpi['balance'] ?? 0), 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          <?= htmlspecialchars($months[$selectedMonth] ?? '') ?> <?= (int)$selectedYear ?>
        </p>
      </div>

      <div class="card">
        <p class="kpi-title">Przychody</p>
        <p class="kpi-value">
          <?= number_format((float)($kpi['income'] ?? 0), 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          <?= htmlspecialchars($months[$selectedMonth] ?? '') ?> <?= (int)$selectedYear ?>
        </p>
      </div>

      <div class="card">
        <p class="kpi-title">Wydatki</p>
        <p class="kpi-value">
          <?= number_format((float)($kpi['expense'] ?? 0), 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          <?= htmlspecialchars($months[$selectedMonth] ?? '') ?> <?= (int)$selectedYear ?>
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

        <form method="GET" action="/dashboard" class="filter-bar">
          <select name="month">
            <?php foreach ($months as $m => $label): ?>
              <option value="<?= (int)$m ?>" <?= $selectedMonth === (int)$m ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select name="year">
            <?php
              $currentYear = (int)date('Y');
              for ($y = $currentYear - 3; $y <= $currentYear + 1; $y++):
            ?>
              <option value="<?= (int)$y ?>" <?= $selectedYear === (int)$y ? 'selected' : '' ?>>
                <?= (int)$y ?>
              </option>
            <?php endfor; ?>
          </select>

          <select name="category_id">
  <option value="">Wszystkie kategorie</option>
  <?php foreach ($categories as $cat): ?>
    <option value="<?= (int)$cat['id'] ?>"
      <?= ((string)($selectedCategoryId ?? '') === (string)$cat['id']) ? 'selected' : '' ?>>
      <?= htmlspecialchars($cat['name']) ?>
    </option>
  <?php endforeach; ?>
</select>


          <button class="btn btn-ghost" type="submit">Filtruj</button>

          <input
  type="text"
  name="q"
  placeholder="Szukaj w opisie..."
  value="<?= htmlspecialchars($searchQuery ?? '', ENT_QUOTES, 'UTF-8') ?>"
  maxlength="100"
/>

          <a class="btn btn-ghost"
             href="/transactions/export?year=<?= (int)$selectedYear ?>&month=<?= (int)$selectedMonth ?>">
            Eksport CSV
          </a>
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
                <td colspan="6">Brak transakcji</td>
              </tr>
            <?php else: ?>
              <?php foreach ($latestTransactions as $t): ?>
                <?php
                  $isExpense = ($t['type'] ?? '') === 'expense';
                  $sign = $isExpense ? '-' : '+';
                ?>
                <tr>
                  <td><?= htmlspecialchars($t['occurred_on'] ?? '') ?></td>
                  <td><?= $isExpense ? 'Wydatek' : 'Przychód' ?></td>
                  <td><?= htmlspecialchars($t['category_name'] ?? '') ?></td>
                  <td><?= htmlspecialchars($t['description'] ?? '') ?></td>
                  <td class="<?= $isExpense ? 'amount-negative' : 'amount-positive' ?>">
                    <?= $sign . number_format((float)($t['amount'] ?? 0), 2, ',', ' ') ?> zł
                  </td>

                  <!-- Jeśli kiedyś dodasz Edytuj/Usuń, dashboard.js już ma delegację eventów -->
                  <td>—</td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="section" style="margin-top:16px;">
  <div class="section-header">
    <div>
      <h2>Top 5 wydatków w wybranym okresie</h2>
      <div class="section-sub">
        <?= htmlspecialchars($months[$selectedMonth] ?? '', ENT_QUOTES, 'UTF-8') ?> <?= (int)$selectedYear ?>
      </div>
    </div>
  </div>

  <div class="card">
    <?php if (empty($topExpenses)): ?>
      <p style="margin:0; color: rgba(0,0,0,0.6);">Brak wydatków dla wybranych filtrów.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Kategoria</th>
            <th>Opis</th>
            <th>Kwota</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($topExpenses as $t): ?>
            <tr>
              <td><?= htmlspecialchars($t['occurred_on']) ?></td>
              <td><?= htmlspecialchars($t['category_name']) ?></td>
              <td><?= htmlspecialchars($t['description'] ?? '') ?></td>
              <td class="amount amount-negative">
                -<?= number_format((float)$t['amount'], 2, ',', ' ') ?> zł
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>


    <!-- WYKRES -->
    <div class="section">
      <div class="section-header">
        <div>
          <h2>Porównanie: przychody vs wydatki</h2>
          <div class="section-sub">
            <?= htmlspecialchars($months[$selectedMonth] ?? '') ?> <?= (int)$selectedYear ?>
          </div>
        </div>
      </div>

      <div class="card">
        <canvas id="incomeExpenseChart" height="120"></canvas>
      </div>
    </div>

  </div>
</div>

<!-- MODAL: DODAJ / EDYTUJ TRANSAKCJĘ (wymagany przez dashboard.js) -->
<div class="modal-backdrop" id="txModalBackdrop" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="txModalTitle">
    <div class="modal-header">
      <h3 id="txModalTitle">Dodaj transakcję</h3>
      <button type="button" class="modal-close" id="closeTxModal" aria-label="Zamknij">×</button>
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
              <input id="occurred_on" type="date" name="occurred_on" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
            </div>
          </div>

          <div>
            <label for="category_id">Kategoria</label>
            <select id="category_id" name="category_id" required>
              <?php if (empty($categories)): ?>
                <option value="">Brak kategorii</option>
              <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int)($cat['id'] ?? 0) ?>">
                    <?= htmlspecialchars($cat['name'] ?? '') ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
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

<script src="/js/dashboard.js" defer></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const values = <?= json_encode([(float)($kpi['income'] ?? 0), (float)($kpi['expense'] ?? 0)]) ?>;

  const ctx = document.getElementById('incomeExpenseChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Przychody', 'Wydatki'],
        datasets: [{
          data: values,
          backgroundColor: ['#22c55e', '#ef4444']
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }
</script>

</body>
</html>
