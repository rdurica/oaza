<?php

declare(strict_types=1);

namespace App\Component;

/**
 * @method getTemplate()
 */
trait ComponentRenderer
{
    public function render(): void
    {
        $reflector = new \ReflectionClass(\get_class($this));
        $this->getTemplate()->setFile(\dirname($reflector->getFileName()) . '/default.latte');
        $this->getTemplate()->render();
    }
}
