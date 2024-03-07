<?php

namespace Moharram82\Queue;

use stdClass;

interface JobInterface
{
    public function getJobId(): int;

    public function getRawBody(): string|null;

    public function payload(): stdClass|null;

    public function fire(): void;

    public function release(): void;

    public function delete(): void;

    public function tries(): int;

    public function getQueue(): string;

    public function fail(): void;
}
