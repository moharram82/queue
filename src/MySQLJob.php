<?php

namespace Moharram82\Queue;

use PDO;

class MySQLJob extends Job implements JobInterface
{
    protected QueueInterface $database;

    protected array $job;

    protected string $queue;

    public function __construct(QueueInterface $database, array $job, string $queue = 'default')
    {
        $this->database = $database;
        $this->job = $job;
        $this->queue = $queue;
    }

    public function getJobId(): int
    {
        return $this->job['id'];
    }

    public function getJobRecord(): array
    {
        return $this->job;
    }

    public function getRawBody(): string
    {
        return $this->job['data'];
    }

    public function tries(): int
    {
        return $this->job['tries'];
    }

    public function release($delay = 0): void
    {
        parent::release($delay);

        $this->database->deleteAndRelease($this->queue, $this, $delay);
    }

    public function delete(): void
    {
        parent::delete();

        $this->database->deleteReserved($this->queue, $this->job['id']);
    }
}
