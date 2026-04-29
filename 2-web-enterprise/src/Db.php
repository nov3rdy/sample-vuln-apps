<?php
declare(strict_types=1);

namespace CompanyHub;

use PDO;

class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'] ?? 'db',
                $_ENV['DB_PORT'] ?? '3306',
                $_ENV['DB_NAME'] ?? 'companyhub'
            );
            self::$pdo = new PDO(
                $dsn,
                $_ENV['DB_USER'] ?? 'companyhub',
                $_ENV['DB_PASS'] ?? 'companyhub',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // V1: Setting emulated prepares ON makes string-concat SQL injection trivial.
                    PDO::ATTR_EMULATE_PREPARES => true,
                ]
            );
        }
        return self::$pdo;
    }

    /**
     * Run a raw SQL string (no binding). Used by the login flow for V1 SQLi.
     * @return array<int,array<string,mixed>>
     */
    public static function rawAll(string $sql): array
    {
        $stmt = self::pdo()->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * @param array<int|string,mixed> $params
     * @return array<int,array<string,mixed>>
     */
    public static function all(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * @param array<int|string,mixed> $params
     * @return array<string,mixed>|null
     */
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::all($sql, $params)[0] ?? null;
        return $row ?: null;
    }

    /**
     * @param array<int|string,mixed> $params
     */
    public static function exec(string $sql, array $params = []): int
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function lastInsertId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }
}
