<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Kategorie</title>
  <link rel="stylesheet" href="/styles/base.css">
  <link rel="stylesheet" href="/styles/dashboard.css">
</head>
<body>

<div class="dashboard-shell">
  <div class="dashboard-container">

    <div class="topbar">
      <div class="brand">
        <h1>Kategorie</h1>
        <p>Zarządzaj swoimi kategoriami</p>
      </div>

      <div class="actions">
        <a class="btn btn-ghost" href="/dashboard" style="text-decoration:none; display:inline-flex; align-items:center;">
          ← Dashboard
        </a>
        <a class="btn btn-ghost" href="/logout" style="text-decoration:none; display:inline-flex; align-items:center;">
          Wyloguj
        </a>
      </div>
    </div>

    <?php if (!empty($msg)): ?>
      <div class="card" style="border: 1px solid rgba(34,197,94,0.4); background: rgba(34,197,94,0.10);">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($err)): ?>
      <div class="card" style="border: 1px solid rgba(220,38,38,0.45); background: rgba(220,38,38,0.10);">
        <?= htmlspecialchars($err) ?>
      </div>
    <?php endif; ?>

    <div class="grid" style="grid-template-columns: 1fr 1fr; margin-top: 16px;">
      <!-- Dodaj -->
      <div class="card">
        <h2 style="margin-top:0;">Dodaj kategorię</h2>
        <form method="POST" action="/categories" class="form-grid">
          <div>
            <label for="name">Nazwa</label>
            <input id="name" name="name" type="text" placeholder="np. Siłownia" required>
          </div>
          <div class="modal-actions" style="justify-content:flex-start;">
            <button class="btn btn-primary" type="submit">Dodaj</button>
          </div>
        </form>
      </div>

      <!-- Lista -->
      <div class="card">
        <h2 style="margin-top:0;">Twoje kategorie</h2>

        <?php if (empty($categories)): ?>
          <p style="color: rgba(0,0,0,0.6); margin: 0;">Brak kategorii.</p>
        <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:10px; margin-top: 10px;">
            <?php foreach ($categories as $c): ?>
              <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                <span class="badge"><?= htmlspecialchars($c['name']) ?></span>

                <form method="POST" action="/categories/delete" style="margin:0;">
                  <input type="hidden" name="category_id" value="<?= (int)$c['id'] ?>">
                  <button class="btn btn-ghost" type="submit">Usuń</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

</body>
</html>
