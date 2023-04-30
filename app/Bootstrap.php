<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;

class Bootstrap
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();
        $appDir = dirname(__DIR__);

        $configurator->setDebugMode(false); // enable for your remote IP
        $configurator->enableTracy($appDir . '/log');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory($appDir . '/temp');
        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();
        $configurator->addDynamicParameters([
            'env' => getenv(),
        ]);
        $configurator->addStaticParameters([
            "CAPTCHA_SECRET_KEY" => getenv()["CAPTCHA_SECRET_KEY"],
            "CAPTCHA_SITE_KEY" => getenv()["CAPTCHA_SITE_KEY"],
        ]);

        $configurator->addConfig(__DIR__ . '/Config/database.neon');
        $configurator->addConfig(__DIR__ . '/Config/config.neon');
        $configurator->addConfig(__DIR__ . '/Config/oaza.neon');

        return $configurator;
    }
}
