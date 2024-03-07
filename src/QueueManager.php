<?php

namespace Moharram82\Queue;

use Moharram82\Queue\Exceptions\MissingQueueDriverException;

class QueueManager
{
    protected QueueConfig $config;

    protected QueueInterface $driver;

    public function __construct(QueueConfig $config)
    {
        $this->config = $config;

        if (empty($this->config->getDriver())) {
            throw new MissingQueueDriverException();
        }

        $this->driver = $config->getDriver();
    }

    public function run(): void
    {
        $job = $this->driver->pop($this->config->getQueue());

        if ($job) {
            $job->fire();
        }
    }

    public function __call($method, $parameters)
    {
        return $this->driver->$method(...$parameters);
    }
}
