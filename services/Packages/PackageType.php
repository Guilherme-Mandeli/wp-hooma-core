<?php

namespace Hooma\Core\Services\Packages;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tipos de paquetes admitidos en Hooma Core.
 */
enum PackageType: string
{
    case JavaScript = 'javascript';
    case Php        = 'php';
    case Binary     = 'binary';
    case Asset      = 'asset';
    case Template   = 'template';
    case Schema     = 'schema';
}
