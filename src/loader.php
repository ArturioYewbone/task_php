<?php

use App\Database;

/**
 * Пересоздаёт БД и загружает данные из указанного CSV.
 *
 * @param string $csvFile Путь к CSV-файлу
 * @throws Exception
 */
function loadCsvToDatabase(string $csvFile)
{
    file_put_contents('php://stdout'," [INFO] Запуск loadCsvToDatabase\n");

    // Открываем CSV
    $handle = fopen($csvFile, 'r');
    if ($handle === false) {
        file_put_contents('php://stdout'," [INFO] Не удалось открыть CSV-файл\n");
        throw new Exception("Не удалось открыть CSV-файл: $csvFile");
    }

    file_put_contents('php://stdout'," [INFO] Открываем CSV\n");

    // Определяем разделитель по первой строке
    $firstLine = fgets($handle);
    rewind($handle);
    $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

    // Пропускаем заголовок
    $header = fgetcsv($handle, 0, $delimiter);
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); // убираем BOM

    file_put_contents('php://stdout'," [INFO] Чтение и вставка\n");

    // Чтение и вставка
    $counter = 0;

    Database::beginTransaction();

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if (count($row) < 8) {
            continue;
        }

        if($counter < 2){
            file_put_contents('php://stdout', "DEBUG ROW: " . var_export($row, true) . "\n");
        }

        list($no, $seccode, $buysell, $time, $orderno, $action, $price, $volume) = $row;
        $counter++;

        Database::insertRow([
            'no'         => $no,
            'seccode'    => $seccode,
            'buysell'    => $buysell,
            'trade_time' => $time,
            'orderno'    => $orderno,
            'action'     => $action,
            'price'      => $price,
            'volume'     => $volume,
        ]);
    }
    file_put_contents('php://stdout', "Загружено: " . $counter . " строк\n");

    // Завершаем транзакцию
    Database::commit();

    fclose($handle);

    file_put_contents('php://stdout'," [INFO] Завершение loadCsvToDatabase" . $counter . "\n");
}
