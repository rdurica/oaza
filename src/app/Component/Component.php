<?php declare(strict_types=1);

namespace App\Component;

use Nette\Application\UI\Control;
use ReflectionClass;
use ReflectionException;

use function dirname;

/**
 * Default abstract class for Components.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
 */
abstract class Component extends Control
{
    /** @var string Default template for components. */
    private const DEFAULT_LATTE = '/default.latte';

    /**
     * Render component.
     *
     * @return void
     * @throws ReflectionException
     */
    public function render(): void
    {
        $reflector = new ReflectionClass($this::class);
        $this->getTemplate()->setFile(dirname($reflector->getFileName()) . self::DEFAULT_LATTE);
        $this->getTemplate()->render();
    }
}
