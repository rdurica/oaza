<?php

declare(strict_types=1);

namespace App\Component;

use Nette\Application\UI\Control;

/**
 * Default abstract class for Components.
 *
 * @package   App\Component
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
abstract class Component extends Control
{
    use ComponentRenderer;
}
