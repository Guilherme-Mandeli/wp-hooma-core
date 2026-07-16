<?php

if (class_exists('Hooma')) {
    return;
}

/**
 * Fachada principal del Framework Hooma.
 *
 * Provee acceso estático global y unificado a todos los servicios del contenedor.
 */
class Hooma
{
    /**
     * Instancia del contenedor de servicios.
     *
     * @var \Hooma\Core\ServiceContainer
     */
    protected static $container;

    /**
     * Define el contenedor de servicios global.
     *
     * @param \Hooma\Core\ServiceContainer $container
     */
    public static function set_container(\Hooma\Core\ServiceContainer $container)
    {
        self::$container = $container;
    }

    /**
     * Obtiene la instancia del contenedor de servicios.
     *
     * @return \Hooma\Core\ServiceContainer
     */
    public static function container()
    {
        return self::$container;
    }

    /**
     * Resuelve un servicio del contenedor.
     *
     * @param string $id Identificador del servicio.
     * @return mixed
     */
    public static function get($id)
    {
        if (null === self::$container) {
            error_log('Hooma Core Error: El contenedor de servicios no ha sido inicializado.');
            return null;
        }
        return self::$container->get($id);
    }

    /**
     * Obtiene el servicio de configuración (Settings Service).
     *
     * @return \Hooma\Core\Services\Settings\SettingsInterface
     */
    public static function settings()
    {
        return self::get('settings');
    }

    /**
     * Obtiene el servicio de logs (Logger Service).
     *
     * @return \Hooma\Core\Services\Logger\LoggerInterface
     */
    public static function logger()
    {
        return self::get('logger');
    }

    /**
     * Obtiene el servicio de caché (Cache Service).
     *
     * @return \Hooma\Core\Services\Cache\CacheInterface
     */
    public static function cache()
    {
        return self::get('cache');
    }

    /**
     * Obtiene el servicio de base de datos (Database Service).
     *
     * @return \Hooma\Core\Services\Database\DatabaseService
     */
    public static function database()
    {
        return self::get('database');
    }

    /**
     * Obtiene el servicio de sistema de archivos (Filesystem Service).
     *
     * @return \Hooma\Core\Services\Filesystem\FilesystemInterface
     */
    public static function filesystem()
    {
        return self::get('filesystem');
    }

    /**
     * Obtiene el servicio HTTP (HTTP Service).
     *
     * @return \Hooma\Core\Services\Http\HttpInterface
     */
    public static function http()
    {
        return self::get('http');
    }

    /**
     * Obtiene el servicio de administración de módulos (Modules Service).
     *
     * @return \Hooma\Core\Services\Modules\ModulesInterface
     */
    public static function modules()
    {
        return self::get('modules');
    }

    /**
     * Obtiene el servicio de gestión de paquetes (Packages Service).
     *
     * @return \Hooma\Core\Services\Packages\PackagesInterface
     */
    public static function packages()
    {
        return self::get('packages');
    }

    /**
     * Obtiene el servicio de assets (Assets Service).
     *
     * @return \Hooma\Core\Services\Assets\AssetsInterface
     */
    public static function assets()
    {
        return self::get('assets');
    }

    /**
     * Obtiene el servicio de configuración de entorno (Config Service).
     *
     * @return \Hooma\Core\Services\Config\ConfigService
     */
    public static function config()
    {
        return self::get('config');
    }

    /**
     * Obtiene el servicio de notificaciones administrativas (Notices Service).
     *
     * @return \Hooma\Core\Services\Notices\NoticesService
     */
    public static function notices()
    {
        return self::get('notices');
    }

    /**
     * Obtiene el servicio de autorización y permisos (Auth Service).
     *
     * @return \Hooma\Core\Services\Auth\AuthService
     */
    public static function auth()
    {
        return self::get('auth');
    }

    /**
     * Obtiene el servicio de eventos (Events Service).
     *
     * @return \Hooma\Core\Services\Events\EventsService
     */
    public static function events()
    {
        return self::get('events');
    }

    /**
     * Obtiene el servicio de planificación (Scheduler Service).
     *
     * @return \Hooma\Core\Services\Scheduler\SchedulerService
     */
    public static function scheduler()
    {
        return self::get('scheduler');
    }
}
