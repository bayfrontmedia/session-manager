<?php

namespace Bayfront\SessionManager\Handlers;

use Bayfront\SessionManager\HandlerException;
use PDO;
use PDOException;
use SessionHandlerInterface;

class PdoHandler implements SessionHandlerInterface
{

    protected PDO $pdo;

    protected string $table;

    /**
     * PDO constructor.
     *
     * @param PDO $pdo
     * @param string $table
     */
    public function __construct(PDO $pdo, string $table = 'sessions')
    {

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Throw exceptions

        $this->pdo = $pdo;
        $this->table = $table;

    }

    /**
     * @return void
     * @throws HandlerException
     */
    public function up(): void
    {

        try {

            $query = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS $this->table (`id` varchar(32) NOT NULL PRIMARY KEY, `contents` text NOT NULL, `last_active` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP)");

            $query->execute();

        } catch (PDOException $e) {

            throw new HandlerException($e->getMessage(), 0, $e);

        }

    }

    /**
     * @param string $path
     * @param string $name (Name of cookie to be set)
     * @return bool
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Do not set $this->pdo as NULL here, as this causes errors when attempting to
     * regenerate a new id via session_regenerate_id.
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @param string $id
     * @return string
     */
    public function read(string $id): string
    {

        try {

            $stmt = $this->pdo->prepare("SELECT contents FROM $this->table WHERE id = :id");

            $stmt->execute([
                ':id' => $id
            ]);

            $read = $stmt->fetchColumn();

            if ($read) {
                return $read;
            }

            return '';

        } catch (PDOException) {

            return '';

        }

    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write(string $id, string $data): bool
    {

        try {

            $stmt = $this->pdo->prepare("INSERT INTO $this->table (id, contents, last_active) values (:id, :contents, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE contents=:contents, last_active=CURRENT_TIMESTAMP");

            $stmt->execute([
                ':id' => $id,
                ':contents' => $data
            ]);

        } catch (PDOException) {

            return false;

        }

        return true;

    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy(string $id): bool
    {

        try {

            $stmt = $this->pdo->prepare("DELETE FROM $this->table WHERE id = :id");

            return $stmt->execute([
                ':id' => $id
            ]);

        } catch (PDOException) {
            return false;
        }

    }

    /**
     * This method should always return TRUE, even if no rows were deleted.
     *
     * @param int $max_lifetime
     * @return int|false
     */
    public function gc(int $max_lifetime): int|false
    {

        $stmt = $this->pdo->prepare("DELETE FROM $this->table WHERE last_active < DATE_SUB(NOW(), INTERVAL " . $max_lifetime . " SECOND)");

        $stmt->execute();

        return $stmt->rowCount();

    }

}