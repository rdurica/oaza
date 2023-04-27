<?php

declare(strict_types=1);

namespace App\Util;

class SodexoPass
{


    public function __construct(
        public string $name,
        public string $imagePath
    ) {
    }

}