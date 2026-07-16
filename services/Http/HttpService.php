<?php

namespace Hooma\Core\Services\Http;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Implementación del cliente HTTP para Hooma Core.
 *
 * Envuelve las llamadas wp_remote_request interceptando objetos WP_Error
 * y traduciéndolos a excepciones estándar de PHP, retornando instancias de HttpResponse.
 */
class HttpService implements HttpInterface
{
    /**
     * Realiza una petición GET.
     *
     * @param string $url URL destino.
     * @param array $args Argumentos adicionales.
     * @return HttpResponse
     * @throws \RuntimeException Si la petición remota falla en la conexión.
     */
    public function get(string $url, array $args = array()): HttpResponse
    {
        return $this->request('GET', $url, $args);
    }

    /**
     * Realiza una petición POST.
     *
     * @param string $url URL destino.
     * @param array $data Datos del cuerpo.
     * @param array $args Argumentos adicionales.
     * @return HttpResponse
     * @throws \RuntimeException Si la petición remota falla en la conexión.
     */
    public function post(string $url, array $data = array(), array $args = array()): HttpResponse
    {
        $args['body'] = $data;
        return $this->request('POST', $url, $args);
    }

    /**
     * Realiza una petición HTTP genérica.
     *
     * @param string $method Método HTTP (ej: 'GET', 'POST', 'PUT', 'DELETE').
     * @param string $url URL destino.
     * @param array $args Argumentos de cabecera y cuerpo.
     * @return HttpResponse
     * @throws \RuntimeException Si la conexión o resolución remota falla (WP_Error).
     */
    public function request(string $method, string $url, array $args = array()): HttpResponse
    {
        $args['method'] = strtoupper($method);

        // Realizar la petición externa a través del API de WordPress
        $response = wp_remote_request($url, $args);

        // Control de errores de bajo nivel (ej. DNS no resuelto, tiempo excedido)
        if (is_wp_error($response)) {
            throw new \RuntimeException(
                'Hooma HTTP Request Failed: ' . $response->get_error_message()
            );
        }

        return new HttpResponse($response);
    }
}
