<?php

declare(strict_types=1);

namespace App\Component;

/**
 * Default renderer for components. Automatically set file to default.latte.
 *
 * @package   App\Component
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
trait ComponentRenderer
{
    /**
     * Render default template at same directory.
     *
     * @return void
     */
    public function render(): void
    {
        $reflector = new \ReflectionClass(\get_class($this));
        $this->getTemplate()->setFile(\dirname($reflector->getFileName()) . '/default.latte');
        $this->getTemplate()->render();
    }
}
