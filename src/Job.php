<?php

namespace Moharram82\Queue;

use Moharram82\Queue\Exceptions\InvalidCommandException;
use Moharram82\Queue\Exceptions\InvalidPayloadException;
use stdClass;

abstract class Job
{
    protected bool $deleted = false;

    protected bool $released = false;

    protected bool $failed = false;

    protected string $queue;

    abstract public function getJobId(): int;

    abstract public function getRawBody(): string;

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function payload()
    {
        $raw_body = $this->getRawBody();

        return !$raw_body ? null : json_decode($raw_body);
    }

    public function fire(): void
    {
        $payload = $this->payload();

        if (!$payload instanceof stdClass || !property_exists($payload, 'command')) {
            static::fail();

            throw new InvalidPayloadException('Payload not found!', $payload);
        }

        $command = unserialize($payload->command);

        if (!is_object($command) || !method_exists($command, 'handle')) {
            throw new InvalidCommandException();
        }

        $command->handle();

        static::delete();
    }

    public function release($delay = 0): void
    {
        $this->released = true;
    }

    public function isReleased(): bool
    {
        return $this->released;
    }

    public function delete(): void
    {
        $this->deleted = true;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function isDeletedOrReleased(): bool
    {
        return $this->isDeleted() || $this->isReleased();
    }

    public function fail(): void
    {
        $this->markAsFailed();

        if ($this->isDeleted()) {
            return;
        }

        $this->delete();
    }

    public function markAsFailed(): void
    {
        $this->failed = true;
    }

    public function hasFailed(): bool
    {
        return $this->failed;
    }
}
