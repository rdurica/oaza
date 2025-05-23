<?php

declare(strict_types=1);

namespace App\Model\Service\Mail;

use App\Dto\CanceledReservationDto;
use Latte\Engine;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use SensitiveParameter;

/**
 * MailService.
 *
 * @copyright Copyright (c) 2025, Robert Durica
 * @since 2025-05-23
 */
class MailService
{
    private const string RESERVATION_NEW = 'Rezervace byla úspěšně vytvořena';

    private const string RESERVATION_CANCELED = 'Rezervace byla zrušena';

    private const string CONTACT_US = 'Nový dotaz z webu OÁZA';

    private const string PASSWORD_RESET = 'Obnovení hesla k účtu';

    private const string PASSWORD_CHANGED = 'Heslo bylo úspěšně změněno';

    private const string NEW_USER = 'Registrace byla úspěšně dokončena';

    private Engine $latte;

    private SmtpMailer $mail;

    /**
     * Constructor.
     *
     * @param string $host
     * @param string $emailAdmin
     * @param string $emailPassword
     * @param string $port
     * @param string $encryption
     * @param string $emailFrom
     * @param string $emailContact
     */
    public function __construct(
        public readonly string $host,
        public readonly string $emailAdmin,
        #[SensitiveParameter] string $emailPassword,
        public readonly string $port,
        public readonly string $encryption,
        public readonly string $emailFrom,
        public readonly string $emailContact,
    )
    {
        $this->latte = new Engine();
        $this->mail = new SmtpMailer($host, $emailAdmin, $emailPassword, (int)$port, $encryption);
    }

    /**
     * Sends email of cancelled reservation.
     *
     * @param string $emailAddress
     *
     * @return void
     */
    public function sendReservationCancellation(string $emailAddress): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($emailAddress)
            ->addBcc($this->emailContact)
            ->setSubject(self::RESERVATION_CANCELED)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/reservationCancellation.latte'));

        $this->mail->send($mail);
    }

    /**
     * Sends email of cancelled reservation.
     *
     * @param CanceledReservationDto[] $canceledReservations
     *
     * @return void
     */
    public function sendReservationCancellationByAdministrator(array $canceledReservations): void
    {
        // Todo: refactor.
        foreach ($canceledReservations as $canceledReservation)
        {
            $mail = new Message();
            $mail->setFrom($this->emailFrom)
                ->addTo($canceledReservation->email)
                ->setSubject(self::RESERVATION_CANCELED)
                ->setHtmlBody(
                    $this->latte->renderToString(__DIR__ . '/templates/reservationCancellationByAdministrator.latte', [
                        'fullName' => $canceledReservation->name,
                        'date'     => $canceledReservation->date,
                        'count'     => $canceledReservation->count,
                    ])
                );

            $this->mail->send($mail);
        }
    }

    /**
     * Sends email to administrator from ContactUs form.
     *
     * @param string $from
     * @param string $message
     *
     * @return void
     */
    public function sendContactFormMessage(string $from, string $message): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($this->emailContact)
            ->setSubject(self::CONTACT_US)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/contactFormMessage.latte', [
                    'from'    => $from,
                    'message' => $message,
                ])
            );

        $this->mail->send($mail);
    }

    /**
     * Sends email with reservation detail.
     *
     * @param string $email
     * @param string $name
     * @param string $date
     * @param bool   $hasChildren
     * @param int    $count
     * @param string $comment
     *
     * @return void
     */
    public function sendNewReservationDetails(
        string $email,
        string $name,
        string $date,
        bool $hasChildren,
        int $count,
        string $comment
    ): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->addBcc($this->emailContact)
            ->setSubject(self::RESERVATION_NEW)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/newReservationDetails.latte', [
                    'email'       => $email,
                    'name'        => $name,
                    'date'        => $date,
                    'hasChildren' => $hasChildren,
                    'count'       => $count,
                    'comment'     => $comment,
                ])
            );

        $this->mail->send($mail);
    }

    /**
     * Sends new temporary password.
     *
     * @param string $email
     * @param string $newPassword
     *
     * @return void
     */
    public function sendTemporaryPassword(string $email, string $newPassword): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->setSubject(self::PASSWORD_RESET)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/temporaryPassword.latte', [
                    'password' => $newPassword,
                ])
            );

        $this->mail->send($mail);
    }

    /**
     * Confirmation email when user change password.
     *
     * @param string $email
     *
     * @return void
     */
    public function sendPasswordChangedNotification(string $email): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->setSubject(self::PASSWORD_CHANGED)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/passwordChangedNotification.latte'));

        $this->mail->send($mail);
    }

    /**
     * Send email about new user.
     *
     * @param string $email
     *
     * @return void
     */
    public function sendUserRegisteredNotification(string $email): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->setSubject(self::NEW_USER)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/userRegisteredNotification.latte'));

        $this->mail->send($mail);
    }
}
