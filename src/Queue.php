<?php

namespace Moharram82\Queue;

use Moharram82\Queue\Exceptions\InvalidPayloadException;

abstract class Queue
{
    protected function preparePayload(object $job)
    {
        $payload_array = [
            'command' => serialize(clone $job),
        ];

        $payload = json_encode($payload_array, \JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidPayloadException(
                'Unable to JSON encode payload. Error ('.json_last_error().'): '.json_last_error_msg(),
                $payload_array
            );
        }

        return $payload;
    }
}
