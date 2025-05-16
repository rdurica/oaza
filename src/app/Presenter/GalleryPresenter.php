<?php

declare(strict_types=1);

namespace App\Presenter;

/**
 * GalleryPresenter
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class GalleryPresenter extends Presenter
{
    /**
     * Scan directory and get images.
     *
     * @return void
     */
    public function renderDefault(): void
    {
        $dir = scandir("../www/assets/images/gallery/shortcut");
        unset($dir[0], $dir[1]);
        $this->getTemplate()->images = $dir;
    }
}
