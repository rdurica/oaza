<?php

declare(strict_types=1);

namespace App\Component;

use Nette\Application\UI\Control;

abstract class Component extends Control
{
    use ComponentRenderer;
}
