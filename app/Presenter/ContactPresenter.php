<?php

namespace App\Presenter;

use App\Component\Form\ContactUs\IContactUs;

class ContactPresenter extends Presenter
{
    /** @var IContactUs @inject */
    public $contactUs;

    /**
     * Contact us form
     * @return \Oaza\Forms\ContactUs
     */
    protected function createComponentContactUs()
    {
        return $this->contactUs->create();
    }
}
