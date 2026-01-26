<?php

<?php

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,    // ⚠️ true na HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../src/repository/UserRepository.php';
require_once __DIR__ . '/../src/services/AuthService.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';
require_once __DIR__ . '/../src/repository/CategoryRepository.php';
require_once __DIR__ . '/../src/services/CategoryService.php';
require_once __DIR__ . '/../src/repository/CategoryRepository.php';
require_once __DIR__ . '/../src/repository/TransactionRepository.php';
require_once __DIR__ . '/../src/services/CategoryService.php';
require_once __DIR__ . '/../src/services/TransactionService.php';



$pdo = Database::connection();
$userRepo = new UserRepository($pdo);
$authService = new AuthService($userRepo);
$authController = new AuthController($authService);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ROUTING
if ($method === 'GET' && ($path === '/' || $path === '/login')) {
    $authController->showLogin();
    exit;
}

if ($method === 'GET' && $path === '/register') {
    $authController->showRegister();
    exit;
}

if ($method === 'POST' && $path === '/register') {
    $authController->register();
    exit;
}

if ($method === 'POST' && $path === '/login') {
    $authController->login();
    exit;
}

if ($method === 'GET' && $path === '/dashboard') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $year = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));

// twarda walidacja
if ($year < 2000 || $year > 2100) $year = (int)date('Y');
if ($month < 1 || $month > 12) $month = (int)date('n');

// zakres: [start, end)
$startDate = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
$endDate = $startDate->modify('+1 month');

$start = $startDate->format('Y-m-d');
$end = $endDate->format('Y-m-d');

// do widoku (żeby selecty wiedziały co jest wybrane)
$selectedYear = $year;
$selectedMonth = $month;


    $categoryRepo = new CategoryRepository($pdo);
    $categoryService = new CategoryService($categoryRepo);
    $categories = $categoryService->listForUser($_SESSION['user_id']);

    $txRepo = new TransactionRepository($pdo);
    $latestTransactions = $txRepo->listForUserInRange($_SESSION['user_id'], $start, $end, 50);
    $kpi = $txRepo->monthSummary($_SESSION['user_id'], $start, $end);

    $start = (new DateTimeImmutable('first day of this month'))->format('Y-m-d');
    $end = (new DateTimeImmutable('first day of next month'))->format('Y-m-d');

    require __DIR__ . '/../public/views/dashboard/dashboard.php';
    exit;
}

if ($method === 'POST' && $path === '/transactions') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $categoryRepo = new CategoryRepository($pdo);
    $txRepo = new TransactionRepository($pdo);
    $txService = new TransactionService($txRepo, $categoryRepo);

    $result = $txService->add($_SESSION['user_id'], $_POST);

    if (!$result['ok']) {
        // najprościej: wróć na dashboard z błędem w query string
        // (docelowo zrobimy flash w sesji)
        header('Location: /dashboard?tx_error=1');
        exit;
    }

    $ry = (int)($_POST['redirect_year'] ?? date('Y'));
$rm = (int)($_POST['redirect_month'] ?? date('n'));
if ($ry < 2000 || $ry > 2100) $ry = (int)date('Y');
if ($rm < 1 || $rm > 12) $rm = (int)date('n');

header('Location: /dashboard?year=' . $ry . '&month=' . $rm . '&tx_added=1');
exit;

}

if ($method === 'GET' && $path === '/categories') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $categoryRepo = new CategoryRepository($pdo);
    $categoryService = new CategoryService($categoryRepo);

    $categories = $categoryService->listForUser($_SESSION['user_id']);

    // proste komunikaty z query string
    $msg = $_GET['msg'] ?? '';
    $err = $_GET['err'] ?? '';

    require __DIR__ . '/views/categories.php';
    exit;
}


if ($method === 'POST' && $path === '/categories') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $categoryRepo = new CategoryRepository($pdo);
    $categoryService = new CategoryService($categoryRepo);

    $result = $categoryService->add($_SESSION['user_id'], $_POST['name'] ?? '');

    if (!$result['ok']) {
        header('Location: /categories?err=' . urlencode($result['error']));
        exit;
    }

    header('Location: /categories?msg=' . urlencode('Dodano kategorię.'));
    exit;
}


if ($method === 'POST' && $path === '/categories/delete') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $categoryId = (int)($_POST['category_id'] ?? 0);

    $categoryRepo = new CategoryRepository($pdo);
    $categoryService = new CategoryService($categoryRepo);

    $result = $categoryService->delete($_SESSION['user_id'], $categoryId);

    if (!$result['ok']) {
        header('Location: /categories?err=' . urlencode($result['error']));
        exit;
    }

    header('Location: /categories?msg=' . urlencode('Usunięto kategorię.'));
    exit;
}

if ($method === 'POST' && $path === '/transactions/update') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $ry = (int)($_POST['redirect_year'] ?? date('Y'));
    $rm = (int)($_POST['redirect_month'] ?? date('n'));
    if ($ry < 2000 || $ry > 2100) $ry = (int)date('Y');
    if ($rm < 1 || $rm > 12) $rm = (int)date('n');

    $categoryRepo = new CategoryRepository($pdo);
    $txRepo = new TransactionRepository($pdo);
    $txService = new TransactionService($txRepo, $categoryRepo);

    $txId = (int)($_POST['tx_id'] ?? 0);
    $result = $txService->update($_SESSION['user_id'], $txId, $_POST);

    if (!$result['ok']) {
        header('Location: /dashboard?year=' . $ry . '&month=' . $rm . '&tx_error=1');
        exit;
    }

    header('Location: /dashboard?year=' . $ry . '&month=' . $rm . '&tx_updated=1');
    exit;
}

if ($method === 'POST' && $path === '/transactions/delete') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $ry = (int)($_POST['redirect_year'] ?? date('Y'));
    $rm = (int)($_POST['redirect_month'] ?? date('n'));
    if ($ry < 2000 || $ry > 2100) $ry = (int)date('Y');
    if ($rm < 1 || $rm > 12) $rm = (int)date('n');

    $categoryRepo = new CategoryRepository($pdo);
    $txRepo = new TransactionRepository($pdo);
    $txService = new TransactionService($txRepo, $categoryRepo);

    $txId = (int)($_POST['tx_id'] ?? 0);
    $result = $txService->delete($_SESSION['user_id'], $txId);

    if (!$result['ok']) {
        header('Location: /dashboard?year=' . $ry . '&month=' . $rm . '&tx_error=1');
        exit;
    }

    header('Location: /dashboard?year=' . $ry . '&month=' . $rm . '&tx_deleted=1');
    exit;
}

if ($method === 'GET' && $path === '/logout') {
    // (opcjonalnie) jeśli nie jesteś zalogowany, i tak wyczyść
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();

    header('Location: /login');
    exit;
}


// 404
http_response_code(404);
echo '404 Not Found';
