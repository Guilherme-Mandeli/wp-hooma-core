<?php

namespace Hooma\Core\Services\Logger;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaz para el servicio de registro de logs (Logger Service).
 *
 * Provee métodos estandarizados basados en severidad para depuración, rastreo
 * y reporte de incidencias o eventos de sistema en los módulos.
 */
interface LoggerInterface
{
    /**
     * Registra un mensaje de nivel DEBUG (depuración detallada).
     *
     * @param string $message Mensaje a registrar.
     * @param array $context Contexto o variables adicionales de soporte.
     */
    public function debug($message, array $context = array());

    /**
     * Registra un mensaje de nivel INFO (eventos de interés operativo).
     *
     * @param string $message Mensaje a registrar.
     * @param array $context Contexto o variables adicionales de soporte.
     */
    public function info($message, array $context = array());

    /**
     * Registra un mensaje de nivel WARNING (advertencias o anomalías que no detienen el flujo).
     *
     * @param string $message Mensaje a registrar.
     * @param array $context Contexto o variables adicionales de soporte.
     */
    public function warning($message, array $context = array());

    /**
     * Registra un mensaje de nivel ERROR (errores en tiempo de ejecución que deben atenderse).
     *
     * @param string $message Mensaje a registrar.
     * @param array $context Contexto o variables adicionales de soporte.
     */
    public function error($message, array $context = array());

    /**
     * Registra un mensaje de nivel CRITICAL (fallos catastróficos o componentes no disponibles).
     *
     * @param string $message Mensaje a registrar.
     * @param array $context Contexto o variables adicionales de soporte.
     */
    public function critical($message, array $context = array());

    /**
     * Registra un mensaje genérico con un nivel de severidad personalizado.
     *
     * @param string $level Nivel de severidad (ej. 'debug', 'info', etc.).
     * @param string $message Mensaje a registrar.
     * @param array $context Contexto o variables adicionales de soporte.
     */
    public function log($level, $message, array $context = array());
}
