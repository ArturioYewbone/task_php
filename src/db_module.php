<?php

namespace App;

use SQLite3;
use SQLite3Stmt;
use SQLite3Result;
use Exception;

/**
 * Модуль работы с базой SQLite3.
 */
class Database {
    /** @var SQLite3|null */
    private static $instance = null;

    /**
     * Инициализирует схему: удаляет старую таблицу и создаёт новую.
     *
     * @throws Exception
     */
    public static function initSchema()
    {
        file_put_contents('php://stdout'," [INFO] Запуск initSchema\n");
        $db = self::getInstance();
        if (!$db->exec('DROP TABLE IF EXISTS deals')) {
            file_put_contents('php://stdout'," [INFO] Не удалось дропнуть таблицу deals\n");
            throw new Exception('Не удалось дропнуть таблицу deals: ' . $db->lastErrorMsg());
        }
        $create =
            'CREATE TABLE IF NOT EXISTS deals (' .
            '   no          INTEGER,' .
            '   seccode     TEXT,' .
            '   buysell     TEXT,' .
            '   trade_time  TEXT,' .
            '   orderno     INTEGER,' .
            '   action      INTEGER,' .
            '   price       REAL,' .
            '   volume      INTEGER' .
            ')';

        if (!$db->exec($create)) {
            file_put_contents('php://stdout'," [INFO] Не удалось создать таблицу deals\n");
            throw new Exception('Не удалось создать таблицу deals: ' . $db->lastErrorMsg());
        }
    }

    /**
     * Подготавливает и кэширует INSERT-запрос.
     *
     * @return SQLite3Stmt
     * @throws Exception
     */
    private static function prepareInsert(): SQLite3Stmt
    {
        $db = self::getInstance();

        static $stmt = null;

        if ($stmt === null) {
            $sql = 'INSERT INTO deals ' .
                '(no, seccode, buysell, trade_time, orderno, action, price, volume) ' .
                'VALUES (:no, :seccode, :buysell, :trade_time, :orderno, :action, :price, :volume)';

            $stmt = $db->prepare($sql);

            if ($stmt === false) {
                file_put_contents('php://stdout'," [INFO] Не удалось подготовить INSERT\n");
                throw new Exception('Не удалось подготовить INSERT: ' . $db->lastErrorMsg());
            }
        }

        return $stmt;
    }

    /**
     * Вставляет одну запись в таблицу deals.
     *
     * @param array $row ['no'=>int, 'seccode'=>string, 'buysell'=>string, 'trade_time'=>string, 'orderno'=>int, 'action'=>int, 'price'=>float, 'volume'=>int]
     * @throws Exception
     */
    public static function insertRow(array $row)
    {
        $stmt = self::prepareInsert();
        $stmt->bindValue(':no',         (int)$row['no'],        SQLITE3_INTEGER);
        $stmt->bindValue(':seccode',    $row['seccode'],        SQLITE3_TEXT);
        $stmt->bindValue(':buysell',    $row['buysell'],        SQLITE3_TEXT);
        $stmt->bindValue(':trade_time', $row['trade_time'],     SQLITE3_TEXT);
        $stmt->bindValue(':orderno',    (int)$row['orderno'],   SQLITE3_INTEGER);
        $stmt->bindValue(':action',     (int)$row['action'],    SQLITE3_INTEGER);
        $stmt->bindValue(':price',      (float)$row['price'],   SQLITE3_FLOAT);
        $stmt->bindValue(':volume',     (int)$row['volume'],    SQLITE3_INTEGER);

        $res = $stmt->execute();
        if ($res === false) {
            file_put_contents('php://stdout'," [INFO] Ошибка вставки\n");
            throw new Exception('Ошибка вставки: ' . self::getInstance()->lastErrorMsg());
        }

        $res->finalize();
    }

    /**
     * Возвращает singleton-экземпляр подключения к базе данных SQLite3.
     *
     * @return SQLite3 Экземпляр подключения к базе данных
     */
    public static function getInstance(): SQLite3
    {
        $dbPath = __DIR__ . '/../data/database.sqlite';

        if (self::$instance === null) {
            // … открываем файл и busyTimeout …
            $db = new SQLite3($dbPath);
            $db->busyTimeout(5000);

            // Оптимизация для bulk-insert
            $db->exec('PRAGMA synchronous = OFF;');
            $db->exec('PRAGMA journal_mode = MEMORY;');

            self::$instance = $db;
        }

        return self::$instance;
    }

    /** Начинает транзакцию */
    public static function beginTransaction()
    {
        self::getInstance()->exec('BEGIN TRANSACTION;');
    }

    /** Фиксирует транзакцию */
    public static function commit()
    {
        self::getInstance()->exec('COMMIT;');
    }

    /**
     * Возвращает результат выборки сделок с пагинацией.
     *
     * @param int $limit
     * @param int $offset
     * @return SQLite3Result
     * @throws Exception
     */
    public static function fetchDeals(int $limit, int $offset): SQLite3Result
    {
        file_put_contents('php://stdout'," [INFO] Запуск fetchDeals\n");
        $db = self::getInstance();
        $stmt = $db->prepare(
            'SELECT * FROM deals ORDER BY no ASC LIMIT :limit OFFSET :offset'
        );
        if ($stmt === false) {
            throw new Exception('Failed to prepare fetch: ' . $db->lastErrorMsg());
        }

        $stmt->bindValue(':limit',  $limit,  SQLITE3_INTEGER);
        $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
        $res = $stmt->execute();

        if ($res === false) {
            file_put_contents('php://stdout'," [INFO] fetchDeals не выполнился\n");
            throw new Exception('fetchDeals не выполнился: ' . $db->lastErrorMsg());
        }

        return $res;
    }
}
