<?php

/**
 * @package session-manager
 * @link https://github.com/bayfrontmedia/session-manager
 * @author John Robinson <john@bayfrontmedia.com>
 * @copyright 2020 Bayfront Media
 */

namespace Bayfront\SessionManager\Handlers;

use Bayfront\SessionManager\HandlerException;
use PDOException;
use SessionHandlerInterface;

class PDO implements SessionHandlerInterface
{

    protected $pdo;

    protected $table;

    /**
     * PDO constructor.
     *
     * @param \PDO $pdo
     * @param string $table
     *
     * @throws HandlerException
     */

    public function __construct(\PDO $pdo, string $table = 'sessions')
    {

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // Throw exceptions

        try {

            $query = $pdo->prepare("CREATE TABLE IF NOT EXISTS $table (`id` varchar(32) NOT NULL PRIMARY KEY, `contents` text NOT NULL, `last_active` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP)");

            $query->execute();

        } catch (PDOException $e) {

            throw new HandlerException($e->getMessage(), 0, $e);

        }

        $this->pdo = $pdo;

        $this->table = $table;

    }

    /**
     * @param string $save_path
     * @param string $session_name (Name of cookie to be set)
     *
     * @return bool
     */

    public function open($save_path, $session_name): bool
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
     * @param string $session_id
     *
     * @return string
     */

    public function read($session_id)
    {

        try {

            $stmt = $this->pdo->prepare("SELECT contents FROM $this->table WHERE id = :id");

            $stmt->execute([
                ':id' => $session_id
            ]);

            $read = $stmt->fetchColumn();

            if ($read) {
                return $read;
            }

            return '';

        } catch (PDOException $e) {

            return '';

        }

    }

    /**
     * @param string $session_id
     * @param string $data
     *
     * @return bool
     */

    public function write($session_id, $data): bool
    {

        try {

            $stmt = $this->pdo->prepare("INSERT INTO $this->table (id, contents, last_active) values (:id, :contents, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE contents=:contents, last_active=CURRENT_TIMESTAMP");

            $stmt->execute([
                ':id' => $session_id,
                ':contents' => $data
            ]);

        } catch (PDOException $e) {

            return false;

        }

        return true;

    }

    /**
     * @param string $session_id
     *
     * @return bool
     */

    public function destroy($session_id): bool
    {

        try {

            $stmt = $this->pdo->prepare("DELETE FROM $this->table WHERE id = :id");

            $stmt->execute([
                ':id' => $session_id
            ]);

            if ($stmt->rowCount()) {
                return true;
            }

        } catch (PDOException $e) {

            return false;

        }

        return false; // No rows affected

    }

    /**
     * This method should always return TRUE, even if no rows were deleted.
     *
     * @param int $lifetime
     *
     * @return bool
     */

    public function gc($lifetime): bool
    {

        $stmt = $this->pdo->prepare("DELETE FROM $this->table WHERE last_active < DATE_SUB(NOW(), INTERVAL " . $lifetime . " SECOND)");

        $stmt->execute();

        return true;

    }

}