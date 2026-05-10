<?php

declare(strict_types=1);

namespace App\Model\Service\Mail;

/**
 * E-mail subjects.
 *
 * @copyright Copyright (c) 2026, Robert Durica
 */
enum MailSubject: string
{
    case RESERVATION_NEW = 'Rezervace byla úspěšně vytvořena';

    case RESERVATION_CANCELED = 'Rezervace byla zrušena';

    case RESERVATION_DATE_CHANGED = 'Změna termínu rezervace';

    case RESERVATION_COUNT_CHANGED = 'Změna počtu osob v rezervaci';

    case RESERVATION_CHILDREN_CHANGED = 'Změna údajů o rezervaci';

    case CONTACT_US = 'Nový dotaz z webu OÁZA';

    case PASSWORD_RESET = 'Obnovení hesla k účtu';

    case PASSWORD_CHANGED = 'Heslo bylo úspěšně změněno';

    case NEW_USER = 'Registrace byla úspěšně dokončena';
}
