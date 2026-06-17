<?php
/**
 * Configuración de Correo SMTP para el Backend
 * 
 * Este archivo define la configuración de correo electrónico que utiliza PHPMailer.
 * Implementa una función helper `backend_env` para leer variables de entorno de forma segura,
 * devolviendo un valor por defecto en caso de que la variable no esté definida.
 */

// Comprobar si la función helper 'backend_env' ya existe para evitar colisiones
if (!function_exists('backend_env')) {
    /**
     * Obtiene el valor de una variable de entorno de $_ENV o getenv()
     * 
     * @param string $key Clave de la variable de entorno a buscar
     * @param mixed $default Valor de contingencia si la variable de entorno no se encuentra definida
     * @return mixed El valor recuperado o el valor por defecto provisto
     */
    function backend_env(string $key, mixed $default = null): mixed
    {
        // Priorizar el array $_ENV. Si no está definido, intentar recuperarlo de getenv() (variables de sistema).
        // Si sigue sin existir o está vacío, retornar el valor del parámetro $default.
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

// Retornar un array asociativo que contiene toda la configuración de correo electrónico.
// Resuelve las claves leyendo las variables del entorno del sistema para evitar exponer credenciales.
return [
    // Dirección de correo del destinatario que recibirá los mensajes enviados desde el portafolio
    // Se lee de 'MAIL_TO' y tiene como respaldo la dirección del desarrollador por defecto.
    'recipient_email' => backend_env('MAIL_TO', 'davila.va.23@gmail.com'),
    
    // Nombre del destinatario del correo (Estiven, propietario del portafolio)
    'recipient_name' => 'Estiven',
    
    // Nombre que aparecerá en la bandeja del receptor indicando quién envió el mensaje
    'from_name' => backend_env('MAIL_FROM_NAME', 'Portfolio de Estiven'),
    
    // Servidor SMTP utilizado para la salida (ej: smtp.gmail.com para cuentas de Google Gmail)
    'smtp_host' => backend_env('MAIL_SMTP_HOST', 'smtp.gmail.com'),
    
    // Puerto de conexión SMTP. Normalmente 587 para cifrado TLS/STARTTLS o 465 para cifrado explícito SSL
    'smtp_port' => (int) backend_env('MAIL_SMTP_PORT', 587),
    
    // Tipo de seguridad cifrada a negociar con el servidor SMTP.
    // Compara el valor contra 'ssl' y decide mapear a la constante de PHPMailer ENCRYPTION_SMTPS (SSL)
    // o ENCRYPTION_STARTTLS (TLS/STARTTLS) por defecto.
    'smtp_secure' => backend_env('MAIL_SMTP_SECURE', 'tls') === 'ssl'
        ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
        
    // Cuenta de usuario/correo para la autenticación en el servidor SMTP (ej: tu_correo@gmail.com)
    'smtp_username' => backend_env('MAIL_SMTP_USER', ''),
    
    // Contraseña segura de aplicación generada desde la configuración de tu cuenta SMTP (ej: Google App Password)
    // Se lee de 'MAIL_SMTP_PASS' de forma totalmente segura.
    'smtp_password' => backend_env('MAIL_SMTP_PASS', ''),
];
