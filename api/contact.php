<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido. Utilice POST."
    ]);
    exit();
}

$autoloadPath = __DIR__ . '/../backend/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Falta instalar PHPMailer en el backend. Ejecuta Composer en la carpeta backend."
    ]);
    exit();
}

require_once $autoloadPath;
require_once __DIR__ . '/../backend/config/env.php';
backend_load_env(__DIR__ . '/../backend/.env');

$configPath = __DIR__ . '/../backend/config/mail.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Falta el archivo de configuración del correo."
    ]);
    exit();
}

$config = require $configPath;
$inputData = json_decode(file_get_contents("php://input"), true);

if (!$inputData) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Datos de entrada inválidos o vacíos."
    ]);
    exit();
}

$nombre = trim($inputData['nombre'] ?? '');
$email = trim($inputData['email'] ?? '');
$asunto = trim($inputData['asunto'] ?? '');
$mensaje = trim($inputData['mensaje'] ?? '');

if ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '') {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Por favor, complete todos los campos correctamente."
    ]);
    exit();
}

$email = filter_var($email, FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "El correo electrónico no es válido."
    ]);
    exit();
}

$nombreSafe = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$emailSafe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$asuntoSafe = htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8');
$mensajeSafe = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));

if (empty($config['smtp_username']) || empty($config['smtp_password'])) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Faltan las credenciales SMTP. Configura SMTP_USERNAME y SMTP_PASSWORD en backend/config/mail.php."
    ]);
    exit();
}

$mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mailer->isSMTP();
    $mailer->Host = $config['smtp_host'] ?? 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = $config['smtp_username'];
    $mailer->Password = $config['smtp_password'];
    $mailer->SMTPSecure = $config['smtp_secure'] ?? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = (int)($config['smtp_port'] ?? 587);
    $mailer->CharSet = 'UTF-8';

    $fromEmail = $config['from_email'] ?? $config['smtp_username'];
    $fromName = $config['from_name'] ?? 'Portfolio de Estiven';
    $recipientEmail = $config['recipient_email'] ?? 'davila.va.23@gmail.com';
    $recipientName = $config['recipient_name'] ?? 'Estiven';

    $mailer->setFrom($fromEmail, $fromName);
    $mailer->addAddress($recipientEmail, $recipientName);
    $mailer->addReplyTo($email, $nombre);

    $mailer->Subject = "[Portfolio] $asunto";
    $mailer->isHTML(true);
    $mailer->Body = "
        <h2>Nuevo mensaje desde el formulario de contacto</h2>
        <p><strong>Nombre:</strong> {$nombreSafe}</p>
        <p><strong>Email:</strong> {$emailSafe}</p>
        <p><strong>Asunto:</strong> {$asuntoSafe}</p>
        <p><strong>Mensaje:</strong><br>{$mensajeSafe}</p>
    ";
    $mailer->AltBody = "Nuevo mensaje desde el formulario de contacto\n\nNombre: {$nombre}\nEmail: {$email}\nAsunto: {$asunto}\n\nMensaje:\n{$mensaje}";

    $mailer->send();

    $logDir = __DIR__ . '/../backend/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $logFile = $logDir . '/mensajes_contacto.log';
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[$timestamp] Nombre: $nombre | Email: $email | Asunto: $asunto | Mensaje: $mensaje\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "¡Muchas gracias, $nombre! Tu mensaje ha sido enviado correctamente."
    ]);
} catch (\PHPMailer\PHPMailer\Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "No se pudo enviar el correo. Revisa la configuración SMTP de Gmail."
    ]);
}
