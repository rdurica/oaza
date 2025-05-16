<?php

namespace App\Presenter;

use App\Component\Form\ContactUs\ContactUs;
use App\Component\Form\ContactUs\IContactUs;
use Nette\DI\Attributes\Inject;

/**
 * ContactPresenter
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class ContactPresenter extends Presenter
{
    #[Inject]
    public IContactUs $contactUsForm;

    /**
     * Create contact-us form
     *
     * @return ContactUs
     */
    protected function createComponentContactUsForm(): ContactUs
    {
        return $this->contactUsForm->create();
    }
}
