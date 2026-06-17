<?php
/**
 * Controlador del Formulario de Contacto
 * 
 * Este endpoint maneja la recepción de mensajes enviados desde el formulario del portafolio.
 * Valida los datos recibidos (nombre, email, asunto y mensaje), sanitiza las entradas para
 * evitar XSS o inyecciones de código, y envía un correo electrónico por SMTP utilizando PHPMailer.
 * 
 * Soporta configuración tanto local (XAMPP/Apache) cargando un archivo .env, como en la nube (Vercel)
 * leyendo las variables del sistema de forma nativa sin generar problemas de permisos de escritura.
 */

// Cabeceras CORS (Cross-Origin Resource Sharing) para habilitar peticiones desde cualquier origen (por ejemplo, Vercel)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Manejar la solicitud "preflight" de CORS enviada automáticamente por el navegador antes del POST real
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Restringir el acceso para aceptar únicamente solicitudes tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Método no permitido. Utilice POST."
    ]);
    exit();
}

// Cargar el autoloader de Composer para importar PHPMailer
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

// Carga las variables del archivo .env si está disponible localmente (en producción en Vercel se leen variables de sistema directamente)
backend_load_env(__DIR__ . '/../backend/.env');

// Cargar la configuración SMTP del correo electrónico
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

// Capturar el cuerpo de la petición (payload JSON enviado desde Vue) y decodificarlo en un array de PHP
$inputData = json_decode(file_get_contents("php://input"), true);

if (!$inputData) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Datos de entrada inválidos o vacíos."
    ]);
    exit();
}

// Extraer campos y recortar espacios en blanco
$nombre = trim($inputData['nombre'] ?? '');
$email = trim($inputData['email'] ?? '');
$asunto = trim($inputData['asunto'] ?? '');
$mensaje = trim($inputData['mensaje'] ?? '');

// Validación básica de campos obligatorios
if ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '') {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Por favor, complete todos los campos correctamente."
    ]);
    exit();
}

// Validar que el correo electrónico introducido tenga un formato sintáctico correcto
$email = filter_var($email, FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "El correo electrónico no es válido."
    ]);
    exit();
}

// Sanitizar entradas para evitar inyecciones XSS y parsear saltos de línea del mensaje a etiquetas HTML
$nombreSafe = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$emailSafe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$asuntoSafe = htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8');
$mensajeSafe = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));

// Validar que las credenciales obligatorias del servidor SMTP estén configuradas
if (empty($config['smtp_username']) || empty($config['smtp_password'])) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Faltan las credenciales SMTP. Configura SMTP_USERNAME y SMTP_PASSWORD en backend/config/mail.php."
    ]);
    exit();
}

// Instanciar PHPMailer habilitando el lanzamiento de excepciones ante fallos
$mailer = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    // Configurar la conexión al servidor SMTP
    $mailer->isSMTP();
    $mailer->Host = $config['smtp_host'] ?? 'smtp.gmail.com';
    $mailer->SMTPAuth = true;
    $mailer->Username = $config['smtp_username'];
    $mailer->Password = $config['smtp_password'];
    $mailer->SMTPSecure = $config['smtp_secure'] ?? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = (int)($config['smtp_port'] ?? 587);
    $mailer->CharSet = 'UTF-8';

    // Configuración de remitente y destinatarios
    $fromEmail = $config['from_email'] ?? $config['smtp_username'];
    $fromName = $config['from_name'] ?? 'Portfolio de Estiven';
    $recipientEmail = $config['recipient_email'] ?? 'davila.va.23@gmail.com';
    $recipientName = $config['recipient_name'] ?? 'Estiven';

    $mailer->setFrom($fromEmail, $fromName);
    $mailer->addAddress($recipientEmail, $recipientName);
    $mailer->addReplyTo($email, $nombre); // Permite responder al correo del usuario directamente al pulsar "Responder" en Gmail

    // Estructurar el asunto y el cuerpo del correo en HTML
    $mailer->Subject = "[Portfolio] $asunto";
    $mailer->isHTML(true);
    $mailer->Body = "
        <h2>Nuevo mensaje desde el formulario de contacto</h2>
        <p><strong>Nombre:</strong> {$nombreSafe}</p>
        <p><strong>Email:</strong> {$emailSafe}</p>
        <p><strong>Asunto:</strong> {$asuntoSafe}</p>
        <p><strong>Mensaje:</strong><br>{$mensajeSafe}</p>
    ";
    
    // Cuerpo alternativo en texto plano para clientes de correo antiguos que no soporten HTML
    $mailer->AltBody = "Nuevo mensaje desde el formulario de contacto\n\nNombre: {$nombre}\nEmail: {$email}\nAsunto: {$asunto}\n\nMensaje:\n{$mensaje}";

    // Enviar el correo
    $mailer->send();

    // Estructurar el log de auditoría
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[$timestamp] Nombre: $nombre | Email: $email | Asunto: $asunto | Mensaje: $mensaje\n";

    if (getenv('VERCEL')) {
        // En producción en Vercel, redirigimos el log a stderr (consola de logs de Vercel)
        // para evitar errores debido al sistema de archivos de solo lectura de funciones serverless
        error_log($logEntry);
    } else {
        // En entorno local (XAMPP), creamos la carpeta logs si no existe y añadimos el mensaje al archivo local
        $logDir = __DIR__ . '/../backend/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/mensajes_contacto.log';
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    // Responder con éxito en formato JSON
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "¡Muchas gracias, $nombre! Tu mensaje ha sido enviado correctamente."
    ]);
} catch (\PHPMailer\PHPMailer\Exception $e) {
    // Capturar excepciones de PHPMailer ante fallos en SMTP o dirección y retornar código 500
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "No se pudo enviar el correo. Revisa la configuración SMTP de Gmail."
    ]);
}
