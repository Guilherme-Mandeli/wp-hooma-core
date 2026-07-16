<?php

namespace Hooma\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Proveedor de Servicios de Hooma Core.
 *
 * Registra todos los servicios por defecto de la infraestructura en el contenedor.
 */
class ServiceProvider
{
    /**
     * El contenedor de servicios.
     *
     * @var ServiceContainer
     */
    protected $container;

    /**
     * Constructor del ServiceProvider.
     *
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Registra todos los servicios de la infraestructura de Hooma Core.
     */
    public function register()
    {
        // Registro del servicio de Configuración/Settings
        $this->container->singleton('settings', function () {
            return new \Hooma\Core\Services\Settings\SettingsService();
        });

        // Registro del servicio de Logs/Logger
        $this->container->singleton('logger', function () {
            return new \Hooma\Core\Services\Logger\LoggerService();
        });

        // Registro del servicio de Módulos/Modules
        $this->container->singleton('modules', function () {
            return new \Hooma\Core\Services\Modules\ModulesService();
        });

        // Registro del servicio de Paquetes (Packages)
        $this->container->singleton('packages', function () {
            return new \Hooma\Core\Services\Packages\PackagesService();
        });

        // Registro del servicio de Assets
        $this->container->singleton('assets', function () {
            return new \Hooma\Core\Services\Assets\AssetsService();
        });

        // Registro del servicio de Configuración de Entorno (Config)
        $this->container->singleton('config', function () {
            return new \Hooma\Core\Services\Config\ConfigService();
        });

        // Registro del servicio de Caché (Cache)
        $this->container->singleton('cache', function () {
            return new \Hooma\Core\Services\Cache\CacheService();
        });

        // Registro del servicio de HTTP
        $this->container->singleton('http', function () {
            return new \Hooma\Core\Services\Http\HttpService();
        });

        // Registro del servicio de Base de Datos (Database)
        $this->container->singleton('database', function () {
            return new \Hooma\Core\Services\Database\DatabaseService();
        });

        // Registro del servicio de Filesystem
        $this->container->singleton('filesystem', function () {
            return new \Hooma\Core\Services\Filesystem\FilesystemService();
        });

        // Registro del servicio de Notices (Avisos)
        $this->container->singleton('notices', function () {
            return new \Hooma\Core\Services\Notices\NoticesService();
        });

        // Registro del servicio de Permisos (Auth)
        $this->container->singleton('auth', function () {
            return new \Hooma\Core\Services\Auth\AuthService();
        });

        // Registro del servicio de Eventos (Events)
        $this->container->singleton('events', function () {
            return new \Hooma\Core\Services\Events\EventsService();
        });

        // Registro del servicio de Planificación (Scheduler)
        $this->container->singleton('scheduler', function () {
            return new \Hooma\Core\Services\Scheduler\SchedulerService();
        });
    }
}
