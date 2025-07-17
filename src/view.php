<?php
/**
 * @param SQLite3Result $rs
 * @param int           $page  текущая страница
 */
function renderDealsTable(SQLite3Result $rs, int $page)
{
    file_put_contents('php://stdout'," [INFO] Запуск renderDealsTable\n");
    echo '<h1>Сделки валютного рынка ММВБ</h1>';
    echo '<p><a href="?reload=1">Перезагрузить данные</a></p>';

    echo '<table border="1" cellpadding="5" cellspacing="0">';
    // Заголовок: ровно как в CSV
    echo '<thead><tr>'
        . '<th>NO</th>'
        . '<th>SECCODE</th>'
        . '<th>BUYSELL</th>'
        . '<th>TIME</th>'
        . '<th>ORDERNO</th>'
        . '<th>ACTION</th>'
        . '<th>PRICE</th>'
        . '<th>VOLUME</th>'
        . '</tr></thead>';

    echo '<tbody>';

    while ($row = $rs->fetchArray(SQLITE3_ASSOC)) {
        // Преобразуем TIME из формата HHMMSSffffff
        $raw = $row['trade_time'] ?? '';
        $dt  = DateTime::createFromFormat('Hisu', $raw);
        $time = $dt
            ? $dt->format('H:i:s.u')
            : htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        printf(
            '<tr>'
            . '<td>%d</td>'
            . '<td>%s</td>'           // SECCODE
            . '<td>%s</td>'           // BUYSELL
            . '<td>%s</td>'           // TIME
            . '<td>%d</td>'           // ORDERNO
            . '<td>%d</td>'           // ACTION
            . '<td>%.4f</td>'         // PRICE
            . '<td>%d</td>'           // VOLUME
            . '</tr>',
            (int)   ($row['no']         ?? 0),
            htmlspecialchars($row['seccode']   ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            htmlspecialchars($row['buysell']   ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $time,
            (int)   ($row['orderno']    ?? 0),
            (int)   ($row['action']     ?? 0),
            (float) ($row['price']      ?? 0.0),
            (int)   ($row['volume']     ?? 0)
        );
    }
    echo '</tbody>';
    echo '</table>';

    // Простая навигация
    $prev = $page > 1 ? $page - 1 : null;
    $next = $page + 1;
    echo '<div style="margin-top:1em">';
    if ($prev) {
        echo '<a href="?page=' . $prev . '">← Предыдущие</a> ';
    }
    echo 'Страница ' . $page;
    echo ' <a href="?page=' . $next . '">Следующие →</a>';
    echo '</div>';
}
