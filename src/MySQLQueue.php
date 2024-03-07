<?php

namespace Moharram82\Queue;

use Moharram82\Queue\Exceptions\DatabaseConnectionException;
use PDO;
use PDOException;

class MySQLQueue extends Queue implements QueueInterface
{
    protected PDO $connection;

    protected string $table = 'queues';

    protected string $queue = 'default';

    public function __construct(PDO $connection = null, array $connection_options = [], QueueConfig $config = null)
    {
        if ($config) {
            $this->table = $config->getTable();
            $this->queue = $config->getQueue();
        }

        if ($connection) {
            $this->connection = $connection;
        } elseif (!empty($connection_options['host'])
            && !empty($connection_options['username'])
            && !empty($connection_options['password'])
            && !empty($connection_options['database'])
        ) {
            $connection_options['port'] = $connection_options['port'] ?? 3306;

            $pdo_options = [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET character_set_results=utf8',
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET character_set_client=utf8',
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET character_set_connection=utf8',
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET collation_connection=utf8_general_ci'
            ];

            try {
                $this->connection = new PDO(
                    "mysql:host={$connection_options['host']};dbname={$connection_options['database']};port={$connection_options['port']};charset=utf8",
                    $connection_options['username'],
                    $connection_options['password'],
                    $pdo_options
                );
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new DatabaseConnectionException($e->getMessage());
            }
        } else {
            throw new DatabaseConnectionException('Missing database driver or connection parameters');
        }
    }

    public function size(): int
    {
        $stmnt = $this->connection->prepare("SELECT COUNT(*) AS count FROM {$this->table} WHERE name = :name");
        $stmnt->execute([':name' => $this->table]);

        return $stmnt->fetchAll()[0]['count'];
    }

    public function push($job, string $queue = 'default'): void
    {
        $payload = $this->preparePayload($job);

        $this->pushToDatabase($payload, $queue);
    }

    public function pop(string $queue = 'default')
    {
        $this->connection->beginTransaction();

        if ($job = $this->getNextJob($queue)) {
            if ($affected = $this->lockJob($job)) {
                $job = new MySQLJob($this, $job, $this->queue);
            }
        }

        $this->connection->commit();

        return $job;
    }

    protected function getNextJob(string $queue = 'default')
    {
        $now = time();

        $sql = "SELECT
                    *
                FROM
                    {$this->table}
                WHERE
                    name = :name
                    AND locked_at IS NULL
                    AND due_at <= {$now}
                ORDER BY
                    id ASC
                LIMIT 1
                FOR UPDATE SKIP LOCKED";

        $stmnt = $this->connection->prepare($sql);
        $stmnt->execute([':name' => $this->queue]);

        if (count($result = $stmnt->fetchAll()) > 0) {
            return $result[0];
        }

        return null;
    }

    public function release($queue, $job, $delay): int
    {
        return $this->pushToDatabase($job['data'], $queue, $delay, $job['tries']);
    }

    protected function pushToDatabase($payload, $queue = 'default', $delay = 0, $tries = 0): int
    {
        $now = time();

        $sql = "INSERT
                INTO {$this->table}
                (name, due_at, data, created_at)
                VALUES (:name, :due_at, :data, :created_at)";

        $stmnt = $this->connection->prepare($sql);
        $stmnt->execute([
            ':name' => $queue,
            ':due_at' => $now + $delay,
            ':data' => $payload,
            ':created_at' => $now,
        ]);

        return $this->connection->lastInsertId();
    }

    public function deleteReserved($queue, $id)
    {
        $this->connection->beginTransaction();

        $stmnt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = :id FOR UPDATE");
        $stmnt->execute([':id' => $id]);

        if ($stmnt->rowCount() > 0) {
            $stmnt = $this->connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmnt->execute([':id' => $id]);
        }

        $this->connection->commit();
    }

    public function deleteAndRelease($queue, $job, $delay)
    {
        $this->connection->beginTransaction();

        $stmnt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = :id FOR UPDATE");
        $stmnt->execute([':id' => $job->getJobRecord()['id']]);

        if ($stmnt->rowCount() > 0) {
            $stmnt = $this->connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmnt->execute([':id' => $job->getJobRecord()['id']]);

            $this->release($queue, $job->getJobRecord(), $delay);
        }

        $this->connection->commit();
    }

    protected function lockJob(array $job): int
    {
        $now = time();

        $sql = "UPDATE
                    {$this->table}
                SET
                    locked_at = {$now}
                WHERE
                    id = :id";

        $stmnt = $this->connection->prepare($sql);
        $stmnt->execute([':id' => $job['id']]);

        return $stmnt->rowCount();
    }
}
