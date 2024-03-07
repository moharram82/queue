<?php

namespace Moharram82\Queue\Exceptions;

class InvalidPayloadException extends \InvalidArgumentException
{
    public $value;

    public function __construct($message = null, $value = null)
    {
        parent::__construct($message ?: json_last_error());

        $this->value = $value;
    }
}
