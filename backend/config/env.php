<?php
/**
 * Lector y Cargador de Variables de Entorno (.env)
 * 
 * Este archivo contiene la infraestructura necesaria para cargar variables
 * de entorno desde un archivo de texto plano de configuración (.env).
 * Facilita que las contraseñas, hosts y puertos no queden expuestos
 * directamente en el código fuente.
 * 
 * Permite compatibilidad local en XAMPP/WAMP (cargando .env) y entornos cloud
 * como Vercel (donde las variables del sistema se inyectan dinámicamente).
 */

// Comprobar si la función 'backend_load_env' ya fue definida previamente
// para evitar errores de redifinición de funciones de PHP.
if (!function_exists('backend_load_env')) {
    /**
     * Lee un archivo .env, analiza cada línea y registra las variables de entorno en PHP.
     * 
     * @param string $path Ruta absoluta hacia el archivo .env
     * @return void
     */
    function backend_load_env(string $path): void
    {
        // Verificar físicamente si el archivo .env existe en la ruta dada.
        // Si no existe (como sucede en Vercel, donde las variables ya están en el sistema),
        // salimos de la función silenciosamente para no generar advertencias en el servidor.
        if (!file_exists($path)) {
            return;
        }

        // Leer todas las líneas del archivo .env ignorando saltos de línea y omitiendo líneas vacías.
        // Se utiliza la bandera FILE_IGNORE_NEW_LINES para evitar caracteres de control de nueva línea (\n, \r),
        // y FILE_SKIP_EMPTY_LINES para evitar procesar líneas sin contenido.
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        // Iterar línea por línea sobre el archivo cargado para procesar cada par clave=valor.
        foreach ($lines as $line) {
            // Eliminar espacios en blanco adicionales al inicio y al final de la línea.
            $line = trim($line);
            
            // Si la línea está vacía o es un comentario (inicia con '#'), la ignoramos.
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Dividir la línea utilizando el primer carácter '=' encontrado.
            // El tercer parámetro '2' indica que solo dividiremos en máximo 2 partes (clave y todo el valor).
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue; // Si la línea no contiene un '=', la omitimos por invalidez.
            }

            // Extraer y limpiar de espacios adicionales el nombre de la variable y su valor.
            $name = trim($parts[0]);
            $value = trim($parts[1]);

            // Comprobar y remover comillas dobles (") al inicio y final del valor si existen.
            if (($value[0] ?? '') === '"' && str_ends_with($value, '"')) {
                $value = substr($value, 1, -1);
            // Comprobar y remover comillas simples (') al inicio y final del valor si existen.
            } elseif (($value[0] ?? '') === "'" && str_ends_with($value, "'")) {
                $value = substr($value, 1, -1);
            }

            // Registrar la variable procesada en el array superglobal $_ENV de PHP.
            $_ENV[$name] = $value;
            // Registrar la variable procesada en el array superglobal $_SERVER de PHP.
            $_SERVER[$name] = $value;
            // Registrar la variable en el entorno real del sistema operativo usando putenv().
            // Esto permite que bibliotecas externas o llamadas nativas de PHP puedan recuperar
            // las variables mediante getenv('CLAVE').
            putenv($name . '=' . $value);
        }
    }
}
