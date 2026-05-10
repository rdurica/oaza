<?php

declare(strict_types=1);

namespace App\Model\Service\Mail;

use App\Dto\CanceledReservationDto;
use Latte\Engine;
use Nette\Application\LinkGenerator;
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
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(
        public readonly string $host,
        public readonly string $emailAdmin,
        #[SensitiveParameter] string $emailPassword,
        public readonly string $port,
        public readonly string $encryption,
        public readonly string $emailFrom,
        public readonly string $emailContact,
        private readonly LinkGenerator $linkGenerator,
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
            ->setSubject(MailSubject::RESERVATION_CANCELED->value)
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
                ->setSubject(MailSubject::RESERVATION_CANCELED->value)
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
     * Sends email about changed reservation date.
     *
     * @param string $email
     * @param string $name
     * @param string $oldDate
     * @param string $newDate
     * @param int    $count
     *
     * @return void
     */
    public function sendReservationDateChanged(
        string $email,
        string $name,
        string $oldDate,
        string $newDate,
        int $count,
    ): void {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->addBcc($this->emailContact)
            ->setSubject(MailSubject::RESERVATION_DATE_CHANGED->value)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/reservationDateChanged.latte', [
                    'name'    => $name,
                    'oldDate' => $oldDate,
                    'newDate' => $newDate,
                    'count'   => $count,
                ])
            );

        $this->mail->send($mail);
    }

    /**
     * Sends email about changed reservation children flag.
     *
     * @param string $email
     * @param string $name
     * @param string $date
     * @param int    $count
     * @param string $hasChildren
     *
     * @return void
     */
    public function sendReservationChildrenChanged(
        string $email,
        string $name,
        string $date,
        int $count,
        string $hasChildren,
    ): void {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->addBcc($this->emailContact)
            ->setSubject(MailSubject::RESERVATION_CHILDREN_CHANGED->value)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/reservationChildrenChanged.latte', [
                    'name'        => $name,
                    'date'        => $date,
                    'count'       => $count,
                    'hasChildren' => $hasChildren,
                ])
            );

        $this->mail->send($mail);
    }

    /**
     * Sends email about changed reservation count.
     *
     * @param string $email
     * @param string $name
     * @param string $date
     * @param int    $oldCount
     * @param int    $newCount
     *
     * @return void
     */
    public function sendReservationCountChanged(
        string $email,
        string $name,
        string $date,
        int $oldCount,
        int $newCount,
    ): void {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->addBcc($this->emailContact)
            ->setSubject(MailSubject::RESERVATION_COUNT_CHANGED->value)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/reservationCountChanged.latte', [
                    'name'     => $name,
                    'date'     => $date,
                    'oldCount' => $oldCount,
                    'newCount' => $newCount,
                ])
            );

        $this->mail->send($mail);
    }

    /**
     * Sends email about cancelled reservation by admin.
     *
     * @param string $email
     * @param string $name
     * @param string $date
     * @param int    $count
     *
     * @return void
     */
    public function sendReservationCancelledByAdmin(
        string $email,
        string $name,
        string $date,
        int $count,
    ): void {
        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->addBcc($this->emailContact)
            ->setSubject(MailSubject::RESERVATION_CANCELED->value)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/reservationCancelledByAdmin.latte', [
                    'name'  => $name,
                    'date'  => $date,
                    'count' => $count,
                ])
            );

        $this->mail->send($mail);
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
            ->setSubject(MailSubject::CONTACT_US->value)
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
            ->setSubject(MailSubject::RESERVATION_NEW->value)
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
     * Sends password reset link.
     *
     * @param string $email
     * @param string $token
     *
     * @return void
     */
    public function sendPasswordResetLink(string $email, string $token): void
    {
        $resetLink = $this->linkGenerator->link('//User:resetPassword', ['token' => $token]);

        $mail = new Message();
        $mail->setFrom($this->emailFrom)
            ->addTo($email)
            ->setSubject(MailSubject::PASSWORD_RESET->value)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/passwordResetLink.latte', [
                    'resetLink' => $resetLink,
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
            ->setSubject(MailSubject::PASSWORD_CHANGED->value)
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
            ->setSubject(MailSubject::NEW_USER->value)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/userRegisteredNotification.latte'));

        $this->mail->send($mail);
    }
}
