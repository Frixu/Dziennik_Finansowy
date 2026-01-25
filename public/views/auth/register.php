<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Rejestracja</title>
  <link rel="stylesheet" href="/styles/auth.css">
  <link rel="stylesheet" href="/styles/base.css">
</head>
<body>

<?php if (!empty($errors)): ?>
  <div class="errors">
    <?php foreach ($errors as $e): ?>
      <p><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

  <div class="login-container">
    <h1>Zarejestruj się</h1>

    <form id="registerForm" class="auth-form" method="post" action="/register">
      <input type="hidden" name="csrf_token"
       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" id="email" required>
        <small class="error"></small>
      </div>

      <div class="form-group">
        <label>Hasło</label>
        <input type="password" name="password" id="password" required>
        <small class="error"></small>
      </div>

      <div class="form-group">
        <label>Powtórz hasło</label>
        <input type="password" name="password_repeat" id="passwordRepeat" required>
        <small class="error"></small>
      </div>

      <button type="submit">Zarejestruj się</button>

      <p class="switch-link">
        Masz już konto?
        <a href="/login">Zaloguj się</a>
      </p>
    </form>
  </div>

<script src="/js/register-validation.js"></script>
</body>
</html>
