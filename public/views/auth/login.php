<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <link rel="stylesheet" href="/styles/auth.css">
    <link rel="stylesheet" href="/styles/base.css">
</head>
<body>

<div class="login-container">
    <h1>Dziennik finansowy</h1>
<?php if (!empty($error)): ?>
  <div class="alert alert-error">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

    <form method="POST" action="/login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div class="form-group">
            <label for="email">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="test@test.pl"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Hasło</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
            >
        </div>

        <button type="submit">Zaloguj się</button>

        <p class="register-link">
    Nie masz konta?
    <a href="/register">Zarejestruj się</a>
  </p>
    </form>

    <p class="hint">
        Konto testowe: test@test.pl / 1234
    </p>
</div>

</body>
</html>
