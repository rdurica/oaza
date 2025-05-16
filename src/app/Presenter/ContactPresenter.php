<?php declare(strict_types=1);

namespace App\Presenter;

use App\Component\Form\ContactUs\ContactUs;
use App\Component\Form\ContactUs\ContactUsFormFactory;

/**
 * ContactPresenter.
 *
 * @package   App\Presenter
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class ContactPresenter extends Presenter
{
    /**
     * Constructor.
     *
     * @param ContactUsFormFactory $contactUsFormFactory
     */
    public function __construct(private readonly ContactUsFormFactory $contactUsFormFactory)
    {
        parent::__construct();
    }

    /**
     * Create contact-us form
     *
     * @return ContactUs
     */
    protected function createComponentContactUsForm(): ContactUs
    {
        return $this->contactUsFormFactory->create();
    }
}
