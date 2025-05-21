<?php

declare(strict_types=1);

namespace App\Model\Service\Mail;

/**
 * Contains all subjects of emails which are send from system.
 *
 * @package   App\Model\Service\Mail
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
final class MailSubject
{
    public const RESERVATION_NEW = 'Rezervace byla úspěšně vytvořena';

    public const RESERVATION_CANCELED = 'Rezervace byla zrušena';

    public const CONTACT_US = 'Nový dotaz z webu OÁZA';

    public const PASSWORD_RESET = 'Obnovení hesla k účtu';

    public const PASSWORD_CHANGED = 'Heslo bylo úspěšně změněno';

    public const NEW_USER = 'Registrace byla úspěšně dokončena';
}
