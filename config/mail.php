<?php
return [
    'host'      => $_ENV['MAIL_HOST']      ?? 'sandbox.smtp.mailtrap.io',
    'port'      => $_ENV['MAIL_PORT']      ?? 587,
    'username'  => $_ENV['MAIL_USERNAME']  ?? '5be72e2707e637',
    'password'  => $_ENV['MAIL_PASSWORD']  ?? '287f205dfb924e',
    'from'      => $_ENV['MAIL_FROM']      ?? 'noreply@vitegourmand.fr',
    'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Vite & Gourmand',
];