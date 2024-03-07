<?php

namespace Moharram82\Queue;

interface QueueInterface
{
    public function size(): int;

    public function push(object $job, string $queue = 'default'): void;

    public function pop(string $queue = 'default'): MySQLJob|null;
}
