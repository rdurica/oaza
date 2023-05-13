<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

/**
 * RouterFactory
 *
 * @package   App\Router
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
final class RouterFactory
{
    use Nette\StaticClass;

    /**
     * Create router.
     *
     * @return RouteList
     */
    public static function createRouter(): RouteList
    {
        $router = new RouteList();
        $router->addRoute('<presenter>/<action>[/<id>]', 'Homepage:default');
        return $router;
    }
}
