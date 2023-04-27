<?php

declare(strict_types=1);

namespace App\Model\Service\Mail;

use Latte\Engine;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;

class MailService
{
    private Engine $latte;
    private SmtpMailer $mail;


    function __construct(
        public readonly string $emailAdmin,
        #[\SensitiveParameter] string $emailPassword
    ) {
        $this->latte = new Engine();
        $this->mail = new SmtpMailer([
            'host' => 'smtp.zoner.com',
            'username' => $emailAdmin,
            'password' => $emailPassword,
        ]);
    }


    /**
     * Email on reservation cancel
     */
    public function reservationCanceled(string $emailAddress): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailAdmin)
            ->addTo($emailAddress)
            ->addBcc($this->emailAdmin)
            ->setSubject(MailSubject::RESERVATION_CANCELED)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/canceled.latte'));

        $this->mail->send($mail);
    }


    /**
     * Send email from Contact us form
     */
    public function contactUs(string $from, string $message): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailAdmin)
            ->addTo($this->emailAdmin)
            ->setSubject(MailSubject::CONTACT_US)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/contact.latte', [
                    'from' => $from,
                    'message' => $message,
                ])
            );

        $this->mail->send($mail);
    }


    /**
     * Email on new reservation
     */
    public function newReservation(
        string $email,
        string $name,
        string $date,
        int $child,
        int $count,
        string $comment
    ): void {
        $mail = new Message();
        $mail->setFrom($this->emailAdmin)
            ->addTo($email)
            ->addBcc($this->emailAdmin)
            ->setSubject(MailSubject::RESERVATION_NEW)
            ->setHtmlBody(
                $this->latte->renderToString(__DIR__ . '/templates/create.latte', [
                    'email' => $email,
                    'name' => $name,
                    'date' => $date,
                    'child' => $child,
                    'count' => $count,
                    'comment' => $comment,
                ])
            );

        $this->mail->send($mail);
    }


    /**
     * Send email with new password
     */
    public function sendNewPassword(string $email, string $newPassword): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailAdmin)
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
     * Password changed email
     */
    public function passwordChanged(string $email): void
    {
        $mail = new Message();
        $mail->setFrom($this->emailAdmin)
            ->addTo($email)
            ->setSubject(MailSubject::PASSWORD_CHANGED)
            ->setHtmlBody($this->latte->renderToString(__DIR__ . '/templates/passwordChanged.latte'));

        $this->mail->send($mail);
    }
}
