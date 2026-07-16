<?php

namespace Hooma\Core\Services\Http;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interfaz para el cliente HTTP (HTTP Service).
 *
 * Define contratos para realizar llamadas externas e integrar datos remotos de forma limpia.
 */
interface HttpInterface
{
    /**
     * Realiza una petición GET.
     *
     * @param string $url URL destino.
     * @param array $args Argumentos de cabeceras, cookies, timeout, etc.
     * @return HttpResponse Objeto estructurado de respuesta.
     */
    public function get(string $url, array $args = array()): HttpResponse;

    /**
     * Realiza una petición POST.
     *
     * @param string $url URL destino.
     * @param array $data Datos a enviar en el cuerpo de la petición.
     * @param array $args Argumentos opcionales de cabecera.
     * @return HttpResponse Objeto estructurado de respuesta.
     */
    public function post(string $url, array $data = array(), array $args = array()): HttpResponse;

    /**
     * Realiza una petición HTTP genérica.
     *
     * @param string $method Método HTTP (ej: 'GET', 'POST', 'PUT', 'DELETE', 'PATCH').
     * @param string $url URL destino.
     * @param array $args Argumentos de cabecera, cuerpo y configuración.
     * @return HttpResponse Objeto estructurado de respuesta.
     */
    public function request(string $method, string $url, array $args = array()): HttpResponse;
}
