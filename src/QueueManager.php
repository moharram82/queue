<?php

namespace Moharram82\Queue;

use Moharram82\Queue\Exceptions\MissingQueueDriverException;

class QueueManager
{
    public function __construct(
        protected QueueConfig $config,
        protected QueueInterface|null $driver = null
    ) {
        if (!$this->driver && empty($this->config->getDriver())) {
            throw new MissingQueueDriverException();
        }

        $this->driver = $config->getDriver();
    }

    public function run(): void
    {
        $job = $this->driver->pop($this->config->getQueue());

        $job?->fire();
    }

    public function __call($method, $parameters)
    {
        return $this->driver->$method(...$parameters);
    }
}
