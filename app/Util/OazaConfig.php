<?php

declare(strict_types=1);

namespace App\Util;

use App\Exception\OazaException;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

trait OazaConfig
{
    /**
     * @throws OazaException
     */
    protected function getConfig(string $key): mixed
    {

        try {
            $config = Neon::decodeFile(__DIR__ . "/../Config/oaza.neon")["parameters"];
        } catch (Exception $e) {
            throw new OazaException("Oaza configuration do not exist");
        }

        if (!\array_key_exists($key, $config)) {
            throw new OazaException("Key {$key} does not found in configuration");
        }

        return $config[$key];
    }
}
