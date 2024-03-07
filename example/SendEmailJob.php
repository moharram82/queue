<?php

namespace Example;

class SendEmailJob
{
    public MailMessage $message;

    public function __construct(MailMessage $message)
    {
        $this->message = $message;
    }

    public function handle()
    {
        $this->message->send();
    }
}
