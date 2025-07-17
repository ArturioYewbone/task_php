<?php

require_once __DIR__ . '/db_module.php';

use App\Database;

file_put_contents('php://stdout'," [INFO] Старт\n");

// Точка входа — показывает таблицу и, при ?reload=1, запускает fetch.php
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Нажатие «Перезагрузить» приведёт к GET-параметру reload=1
if (isset($_GET['reload'])) {
    Database::initSchema();
    file_put_contents('php://stdout'," [INFO] Запуск fetch\n");
    require __DIR__ . '/fetch.php'; // внутри себя сделает header('Location: /');
    exit;
}

// Иначе рендерим текущие данные

$rs = Database::fetchDeals($limit, $offset);
require __DIR__ . '/view.php';
renderDealsTable($rs, $page);
