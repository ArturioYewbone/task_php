<?php

set_time_limit(300);

require __DIR__ . '/unpacker.php';
require __DIR__ . '/loader.php';

$url    = "https://fs.moex.com/files/18307/orderlog20251229.zip";

// Куда сохраняем ZIP
$tmpDir = sys_get_temp_dir();
$zipPath = $tmpDir . '/moex_orders.zip';

if (!copy($url, $zipPath)) {
    $err = error_get_last();
    fwrite(STDERR, "Ошибка при копировании: {$err['message']}\n");
    file_put_contents('php://stdout'," [INFO]Ошибка при копировании: {$err['message']}\n");
    exit(1);
}

file_put_contents('php://stdout'," [INFO] ZIP скачен\n");

$csvFile = unpackOrderLog($zipPath);

// Загрузили CSV в БД
loadCsvToDatabase($csvFile);

// удалить временный CSV и папку
$tempDir = dirname($csvFile);
array_map('unlink', glob("$tempDir/*"));
rmdir($tempDir);

header('Location: /');