<?php declare(strict_types=1);

namespace App\Presenter;

/**
 * GalleryPresenter.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since     2025-05-16
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
        $dir = scandir('../www/assets/images/gallery/shortcut');
        unset($dir[0], $dir[1]);
        $this->getTemplate()->images = $dir;
    }
}
