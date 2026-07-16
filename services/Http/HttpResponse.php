<?php

namespace Hooma\Core\Services\Http;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Objeto de Respuesta HTTP para Hooma Core.
 *
 * Normaliza y encapsula la respuesta cruda de WordPress, proveyendo métodos limpios
 * y tipados de consulta sobre el cuerpo, estado, JSON y cabeceras.
 */
class HttpResponse
{
    /**
     * La estructura de respuesta cruda de WordPress.
     *
     * @var array
     */
    protected $raw_response;

    /**
     * Constructor de HttpResponse.
     *
     * @param array $raw_response Respuesta de wp_remote_request.
     */
    public function __construct(array $raw_response)
    {
        $this->raw_response = $raw_response;
    }

    /**
     * Obtiene el código de estado HTTP (ej. 200, 404, 500).
     *
     * @return int Código de estado.
     */
    public function status(): int
    {
        return (int) wp_remote_retrieve_response_code($this->raw_response);
    }

    /**
     * Obtiene el cuerpo de la respuesta como texto plano.
     *
     * @return string Cuerpo de la respuesta.
     */
    public function body(): string
    {
        return (string) wp_remote_retrieve_body($this->raw_response);
    }

    /**
     * Decodifica el cuerpo de la respuesta desde formato JSON.
     *
     * @param bool $assoc Retornar como array asociativo.
     * @return array Estructura decodificada. Devuelve array vacío en caso de fallo.
     */
    public function json(bool $assoc = true): array
    {
        $body = $this->body();
        $decoded = json_decode($body, $assoc);
        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Obtiene las cabeceras de respuesta normalizadas.
     *
     * @return array
     */
    public function headers(): array
    {
        $headers = wp_remote_retrieve_headers($this->raw_response);

        if (is_object($headers) && method_exists($headers, 'getAll')) {
            return $headers->getAll();
        }

        return is_array($headers) ? $headers : (array) $headers;
    }

    /**
     * Determina si el código de estado HTTP indica éxito (2xx).
     *
     * @return bool
     */
    public function successful(): bool
    {
        $status = $this->status();
        return $status >= 200 && $status < 300;
    }

    /**
     * Determina si el código de estado indica fallo (fuera de la franja 2xx).
     *
     * @return bool
     */
    public function failed(): bool
    {
        return !$this->successful();
    }

    /**
     * Devuelve la respuesta nativa original de WordPress.
     *
     * @return array
     */
    public function raw(): array
    {
        return $this->raw_response;
    }
}
