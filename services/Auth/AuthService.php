<?php

namespace Hooma\Core\Services\Auth;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Servicio de Autenticación y Autorización (Auth Service).
 *
 * Abstrae el manejo de usuarios de WordPress y mapea permisos del Framework (ej: booking.edit)
 * a las capabilities nativas correspondientes de WordPress (ej: edit_posts). Permite a los
 * módulos extender este mapa dinámicamente mediante filtros.
 */
class AuthService
{
    /**
     * Mapa de permisos de Hooma y sus capabilities nativas asociadas en WordPress.
     *
     * @var array
     */
    protected $permission_map = array();

    /**
     * Constructor de AuthService.
     */
    public function __construct()
    {
        $this->load_default_permission_map();
    }

    /**
     * Comprueba si el usuario actual posee un permiso de Hooma o capability nativa de WordPress.
     *
     * @param string $permission Identificador del permiso (ej. 'booking.edit' o 'edit_posts').
     * @return bool True si posee el permiso, false en caso contrario.
     */
    public function can(string $permission): bool
    {
        $mapped_permission = $this->resolve_permission($permission);
        return current_user_can($mapped_permission);
    }

    /**
     * Obtiene el identificador único del usuario actualmente logueado.
     *
     * @return int ID del usuario, o 0 si no está autenticado.
     */
    public function user_id(): int
    {
        return (int) get_current_user_id();
    }

    /**
     * Comprueba si el visitante actual es un usuario autenticado en el sistema.
     *
     * @return bool True si es usuario logueado, false de lo contrario.
     */
    public function check(): bool
    {
        return is_user_logged_in();
    }

    /**
     * Carga y expone a filtrado el mapa de permisos por defecto de Hooma.
     */
    protected function load_default_permission_map(): void
    {
        $this->permission_map = array(
            'admin.view'     => 'manage_options',
            'modules.manage' => 'manage_options',
        );

        // Permitir a los módulos registrar dinámicamente sus propios permisos
        $this->permission_map = apply_filters('hooma_auth_permission_map', $this->permission_map);
    }

    /**
     * Resuelve un string de permiso buscando equivalentes en el mapa.
     *
     * @param string $permission
     * @return string Capability de WordPress resultante.
     */
    protected function resolve_permission(string $permission): string
    {
        if (array_key_exists($permission, $this->permission_map)) {
            return $this->permission_map[$permission];
        }
        return $permission;
    }
}
