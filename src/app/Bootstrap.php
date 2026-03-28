<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;
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
        $appEnv = strtolower((string)(getenv('APP_ENV') ?: 'prod'));

        $configurator->setDebugMode((bool)(getenv('DEBUG') ?: false)); // enable for your remote IP
        $configurator->enableTracy($appDir . '/log');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory($appDir . '/temp');
        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();
        $configurator->addDynamicParameters([
            'env' => getenv(),
        ]);

        $secretsFile = __DIR__ . '/Config/secrets.neon';
        $secretsEnvFile = __DIR__ . '/Config/secrets.env.neon';
        if ($appEnv === 'dev') {
            $configurator->addConfig($secretsEnvFile);
        } else {
            if (!is_file($secretsFile)) {
                throw new RuntimeException(sprintf(
                    'Missing required configuration file "%s" for APP_ENV=%s.',
                    $secretsFile,
                    $appEnv
                ));
            }

            $configurator->addConfig($secretsFile);
        }

        $configurator->addConfig(__DIR__ . '/Config/database.neon');
        $configurator->addConfig(__DIR__ . '/Config/config.neon');
        $configurator->addConfig(__DIR__ . '/Config/oaza.neon');

        return $configurator;
    }
}
