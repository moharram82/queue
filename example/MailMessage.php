<?php

namespace Example;

class MailMessage
{
    public string $from = 'admin@ibeone.com';
    public string $to = 'moharram82@hotmail.com';
    public string $subject = 'Test Email';
    public string $body = '<h1>This is a test Email message</h1><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Assumenda atque aut blanditiis dolorem laborum mollitia ratione rerum sit. Animi aspernatur dicta distinctio, fugiat laudantium rerum veritatis vero? Quo, rerum, totam!</p>';

    public function send()
    {
        echo 'sending...';
    }
}
