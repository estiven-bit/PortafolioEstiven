<?php

if (!function_exists('backend_env')) {
    function backend_env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

return [
    'recipient_email' => backend_env('MAIL_TO', 'davila.va.23@gmail.com'),
    'recipient_name' => 'Estiven',
    'from_name' => backend_env('MAIL_FROM_NAME', 'Portfolio de Estiven'),
    'smtp_host' => backend_env('MAIL_SMTP_HOST', 'smtp.gmail.com'),
    'smtp_port' => (int) backend_env('MAIL_SMTP_PORT', 587),
    'smtp_secure' => backend_env('MAIL_SMTP_SECURE', 'tls') === 'ssl'
        ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
    'smtp_username' => backend_env('MAIL_SMTP_USER', ''),
    'smtp_password' => backend_env('MAIL_SMTP_PASS', ''),
];
