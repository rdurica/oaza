<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
use Nette\Neon\Neon;
use RuntimeException;

/**
 * Bootstrap.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
class Bootstrap
{
    /**
     * Boot.
     *
     * @return Configurator
     */
    public static function boot(): Configurator
    {
        $configurator = new Configurator();
        $appDir = dirname(__DIR__);
        $secretsFile = __DIR__ . '/Config/secrets.neon';
        if (!is_file($secretsFile)) {
            throw new RuntimeException(sprintf(
                'Missing required configuration file "%s". Copy secrets.neon.dist to secrets.neon and fill values.',
                $secretsFile
            ));
        }

        $secrets = Neon::decodeFile($secretsFile);
        $debugMode = (bool)($secrets['parameters']['debugMode'] ?? false);
        $configurator->setDebugMode($debugMode);
        $configurator->enableTracy($appDir . '/log');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory($appDir . '/temp');
        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();
        $configurator->addDynamicParameters([
            'env' => getenv(),
        ]);

        $configurator->addConfig($secretsFile);
        $configurator->addConfig(__DIR__ . '/Config/database.neon');
        $configurator->addConfig(__DIR__ . '/Config/config.neon');
        $configurator->addConfig(__DIR__ . '/Config/oaza.neon');

        return $configurator;
    }
}
