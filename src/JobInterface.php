<?php

namespace Moharram82\Queue;

interface JobInterface
{
    public function getJobId(): int;

    public function getRawBody(): string;

    public function payload();

    public function fire(): void;

    public function release(): void;

    public function delete(): void;

    public function tries(): int;

    public function getQueue(): string;

    public function fail(): void;
}
