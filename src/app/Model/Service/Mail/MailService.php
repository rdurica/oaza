<?php

declare(strict_types=1);

namespace App\Model\Service\Mail;

use App\Dto\CanceledReservationDto;
use Latte\Engine;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use SensitiveParameter;

/**
 * Service which sends emails.
 *
 * @package   App\Model\Service\Mail
 * @author    Robert Durica <r.durica@gmail.com>
 * @copyright Copyright (c) 2023, Robert Durica
 */
class MailService
{
    private Engine $latte;

    private SmtpMailer $mail;

    /**
     * Constructor.
     *
     * @param string $host
     * @param string $emailAdmin
     * @param string $emailPassword
     * @param string $port
     */
    public function __construct(
        public readonly string $host,
        public readonly string $emailAdmin,
        #[SensitiveParameter] string $emailPassword,
        public readonly string $port,
        public readonly string $encription,
        public readonly string $emailFrom,
        public readonly string $emailContact,
    )
    {
        $this->latte = new Engine();
        $this->mail = new SmtpMailer($host, $emailAdmin, $emailPassword, (int)$port, $encription);
    }

    /**
     * Sends email of cancelled reservation.
     *
     * @param string $emailAddress
     *
     * @return void
     */
    public function reservationCanceled(string $emailAddress): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($emailAddress)
            ->addBcc($this->emailContact)
            ->setSubject(MailSubject::RESERVATION_CANCELED)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/canceled.latte'));

        $this->mail->send($mail);
    }

    /**
     * Sends email of cancelled reservation.
     *
     * @param CanceledReservationDto[] $canceledReservations
     *
     * @return void
     */
    public function reservationsCanceledByAdministrator(array $canceledReservations): void
    {
        // Todo: refactor.
        foreach ($canceledReservations as $canceledReservation)
        {
            $mail = new Message();
            $mail->setFrom($this->emailFrom)
                ->addTo($canceledReservation->email)
                ->setSubject(MailSubject::RESERVATION_CANCELED)
                ->setHtmlBody(
                    $this->latte->renderToString(__DIR__ . '/templates/canceledByAdmin.latte', [
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
    public function contactUs(string $from, string $message): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($this->emailContact)
            ->setSubject(MailSubject::CONTACT_US)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/contact.latte', [
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
    public function newReservation(
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
            ->setSubject(MailSubject::RESERVATION_NEW)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/create.latte', [
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
    public function sendNewPassword(string $email, string $newPassword): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->setSubject(MailSubject::PASSWORD_RESET)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/newPassword.latte', [
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
    public function passwordChanged(string $email): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->setSubject(MailSubject::PASSWORD_CHANGED)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/passwordChanged.latte'));

        $this->mail->send($mail);
    }

    /**
     * Send email about new user.
     *
     * @param string $email
     *
     * @return void
     */
    public function userRegistered(string $email): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->setSubject(MailSubject::NEW_USER)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/registration.latte'));

        $this->mail->send($mail);
    }
}
