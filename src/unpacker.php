<?php

/**
 * Распаковывает ZIP и возвращает путь к первому .csv-файлу внутри.
 *
 * @param string $zipPath Путь к ZIP-архиву
 * @return string Путь к найденному CSV
 * @throws Exception
 */
function unpackOrderLog(string $zipPath): string
{
    file_put_contents('php://stdout'," [INFO] Запуск unpackOrderLog\n");

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        file_put_contents('php://stdout'," [INFO] Не удалось открыть ZIP-файл\n");
        throw new Exception("Не удалось открыть ZIP-файл: $zipPath");
    }

    // Временная папка
    $tempDir = sys_get_temp_dir() . '/parser_' . uniqid();
    if (!mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
        file_put_contents('php://stdout'," [INFO] Не удалось создать временную папку\n");
        throw new Exception("Не удалось создать временную папку: $tempDir");
    }

    $zip->extractTo($tempDir);
    $zip->close();

    // Ищем первый CSV
    $files = glob($tempDir . '/*.csv');
    if (empty($files)) {
        file_put_contents('php://stdout'," [INFO] В архиве не найден CSV-файл\n");
        throw new Exception("В архиве не найден CSV-файл");
    }

    // Возвращаем путь к CSV
    return $files[0];
}
