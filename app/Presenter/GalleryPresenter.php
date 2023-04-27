<?php

namespace App\Presenter;

/**
 * Class GalleryPresenter
 * @package App\Presenters
 */
class GalleryPresenter extends Presenter
{
    /**
     * Gallery images
     */
    public function renderDefault()
    {
        $dir = scandir("../www/assets/images/gallery/shortcut");
        unset($dir[0]);
        unset($dir[1]);
        $this->template->images = $dir;
    }
}
