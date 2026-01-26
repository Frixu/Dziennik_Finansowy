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
        <p>Zalogowany jako: <?= htmlspecialchars($_SESSION['user_email']) ?></p>
      </div>

      <div class="actions">
        <button class="btn btn-primary" id="openAddTx">+ Dodaj transakcję</button>
        <a class="btn btn-ghost" href="/categories">Kategorie</a>
        <a class="btn btn-ghost" href="/logout">Wyloguj</a>
      </div>
    </div>

    <!-- KPI -->
    <div class="grid kpi-grid">
      <div class="card">
        <p class="kpi-title">Saldo</p>
        <p class="kpi-value">
          <?= number_format($kpi['balance'], 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          <?= htmlspecialchars($months[$selectedMonth]) ?> <?= $selectedYear ?>
        </p>
      </div>

      <div class="card">
        <p class="kpi-title">Przychody</p>
        <p class="kpi-value">
          <?= number_format($kpi['income'], 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          <?= htmlspecialchars($months[$selectedMonth]) ?> <?= $selectedYear ?>
        </p>
      </div>

      <div class="card">
        <p class="kpi-title">Wydatki</p>
        <p class="kpi-value">
          <?= number_format($kpi['expense'], 2, ',', ' ') ?> zł
        </p>
        <p class="kpi-sub">
          <?= htmlspecialchars($months[$selectedMonth]) ?> <?= $selectedYear ?>
        </p>
      </div>
    </div>

    <!-- HISTORIA -->
    <div class="section">
      <div class="section-header">
        <div>
          <h2>Historia transakcji</h2>
          <div class="section-sub">
            <?= htmlspecialchars($months[$selectedMonth]) ?> <?= $selectedYear ?>
          </div>
        </div>

        <form method="GET" action="/dashboard" class="filter-bar">
          <select name="month">
            <?php foreach ($months as $m => $label): ?>
              <option value="<?= $m ?>" <?= $selectedMonth === $m ? 'selected' : '' ?>>
                <?= $label ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select name="year">
            <?php
              $currentYear = (int)date('Y');
              for ($y = $currentYear - 3; $y <= $currentYear + 1; $y++):
            ?>
              <option value="<?= $y ?>" <?= $selectedYear === $y ? 'selected' : '' ?>>
                <?= $y ?>
              </option>
            <?php endfor; ?>
          </select>

          <button class="btn btn-ghost">Filtruj</button>
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
            <?php if (!$latestTransactions): ?>
              <tr>
                <td colspan="6">Brak transakcji</td>
              </tr>
            <?php else: ?>
              <?php foreach ($latestTransactions as $t): ?>
                <?php
                  $isExpense = $t['type'] === 'expense';
                  $sign = $isExpense ? '-' : '+';
                ?>
                <tr>
                  <td><?= htmlspecialchars($t['occurred_on']) ?></td>
                  <td><?= $isExpense ? 'Wydatek' : 'Przychód' ?></td>
                  <td><?= htmlspecialchars($t['category_name']) ?></td>
                  <td><?= htmlspecialchars($t['description'] ?? '') ?></td>
                  <td class="<?= $isExpense ? 'amount-negative' : 'amount-positive' ?>">
                    <?= $sign . number_format($t['amount'], 2, ',', ' ') ?> zł
                  </td>
                  <td>—</td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- WYKRES -->
    <div class="section">
      <div class="section-header">
        <div>
          <h2>Porównanie: przychody vs wydatki</h2>
          <div class="section-sub">
            <?= htmlspecialchars($months[$selectedMonth]) ?> <?= $selectedYear ?>
          </div>
        </div>
      </div>

      <div class="card">
        <canvas id="incomeExpenseChart" height="120"></canvas>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const values = <?= json_encode([(float)$kpi['income'], (float)$kpi['expense']]) ?>;

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
