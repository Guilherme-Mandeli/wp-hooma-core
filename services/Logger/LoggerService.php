<?php

namespace Hooma\Core\Services\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Implementación del servicio de registro de logs para Hooma Core.
 *
 * Escribe logs con marcas de tiempo en archivos diarios bajo wp-content/uploads/hooma-logs/.
 * Incorpora medidas de seguridad (archivo .htaccess) para proteger la información confidencial.
 */
class LoggerService implements LoggerInterface
{
    /**
     * Directorio donde se almacenan los logs.
     *
     * @var string
     */
    protected $log_dir;

    /**
     * Constructor de LoggerService.
     */
    public function __construct()
    {
        $upload_dir = wp_upload_dir();
        // Usar wp-content/uploads/hooma-logs/
        $this->log_dir = wp_normalize_path($upload_dir['basedir'] . '/hooma-logs');
        $this->ensure_log_directory();
    }

    /**
     * Registra un mensaje de nivel DEBUG.
     */
    public function debug($message, array $context = array())
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Registra un mensaje de nivel INFO.
     */
    public function info($message, array $context = array())
    {
        $this->log('info', $message, $context);
    }

    /**
     * Registra un mensaje de nivel WARNING.
     */
    public function warning($message, array $context = array())
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Registra un mensaje de nivel ERROR.
     */
    public function error($message, array $context = array())
    {
        $this->log('error', $message, $context);
    }

    /**
     * Registra un mensaje de nivel CRITICAL.
     */
    public function critical($message, array $context = array())
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Escribe la entrada en el archivo de log correspondiente al día.
     */
    public function log($level, $message, array $context = array())
    {
        $level = strtoupper($level);
        $timestamp = current_time('mysql'); // Tiempo local configurado en WP
        
        // Formatear el contexto como JSON legible si tiene datos
        $context_str = '';
        if (!empty($context)) {
            $context_str = ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $log_entry = sprintf("[%s] [%s] %s%s\n", $timestamp, $level, $message, $context_str);

        // Nombre de archivo rotativo diario para evitar archivos gigantescos
        $file_name = sprintf('hooma-%s.log', gmdate('Y-m-d'));
        $file_path = wp_normalize_path($this->log_dir . '/' . $file_name);

        // Intentar escribir la entrada en el archivo (modo append)
        @file_put_contents($file_path, $log_entry, FILE_APPEND);
    }

    /**
     * Garantiza la existencia del directorio y su seguridad.
     */
    protected function ensure_log_directory()
    {
        if (!is_dir($this->log_dir)) {
            @mkdir($this->log_dir, 0755, true);
        }

        // Crear archivo .htaccess para denegar el acceso web directo a los logs
        $htaccess_path = wp_normalize_path($this->log_dir . '/.htaccess');
        if (!file_exists($htaccess_path)) {
            @file_put_contents($htaccess_path, "Deny from all\n");
        }
        
        // Crear un index.php vacío para prevenir listado de directorio secundario
        $index_path = wp_normalize_path($this->log_dir . '/index.php');
        if (!file_exists($index_path)) {
            @file_put_contents($index_path, "<?php\n// Silent is golden\n");
        }
    }
}
