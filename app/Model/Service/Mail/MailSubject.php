<?php

declare(strict_types=1);

namespace App\Model\Service\Mail;

final readonly class MailSubject
{
    public const RESERVATION_NEW = "Rezervace úspěšně vytvořena";
    public const RESERVATION_CANCELED = "Rezervace byla zrušena";
    public const CONTACT_US = 'Dotaz';

    public const PASSWORD_RESET = 'Reset hesla';
    public const PASSWORD_CHANGED = 'Heslo bylo úspěšně zmněneno';

}
