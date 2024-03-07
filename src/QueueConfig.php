<?php

namespace Moharram82\Queue;

class QueueConfig
{
    protected string $queue = 'default';

    protected int $retry_after = 90;

    protected string $table = 'queues';

    protected QueueInterface|null $driver = null;

    public function __construct(array|null $options = null)
    {
        if ($options) {
            foreach ($options as $key => $value) {
                if (in_array($key, array_keys(get_class_vars(get_class($this))))) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function setQueue(string $queue): void
    {
        $this->queue = $queue;
    }

    public function getRetryAfter(): string
    {
        return $this->retry_after;
    }

    public function setRetryAfter(int $retry_after): void
    {
        $this->retry_after = $retry_after;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function setDriver(QueueInterface $driver): void
    {
        $this->driver = $driver;
    }

    public function getDriver(): QueueInterface
    {
        return $this->driver;
    }
}
