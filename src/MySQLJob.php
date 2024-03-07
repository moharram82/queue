<?php

namespace Moharram82\Queue;

use PDO;

class MySQLJob extends Job implements JobInterface
{
    public function __construct(
        protected QueueInterface $database,
        protected array $job,
        protected string $queue = 'default'
    ) {
    }

    public function getJobId(): int
    {
        return $this->job['id'];
    }

    public function getJobRecord(): array
    {
        return $this->job;
    }

    public function getRawBody(): string|null
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
