<?php

namespace Bocum\Service;

use Twig\Environment;
use Bocum\Entity\Order;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private ParameterBagInterface $params
    ) {}

    public function sendEmail(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from($this->params->get('mailer_sender'))
            ->to($to)
            ->subject($subject)
            ->html($content);

        $this->mailer->send($email);
    }

    public function sendInvoiceEmail(Order $order): void
    {

        $userEmail = $order->getUser()->getEmail();
        $invoicePath = $this->params->get('kernel.project_dir') . '/public' . $order->getInvoicePath();

        $email = (new Email())
            ->from($this->params->get('mailer_sender'))
            ->to($userEmail)
            ->subject('Your Order Invoice')
            ->html($this->twig->render('emails/invoice_email.html.twig', ['order' => $order]))
            ->attachFromPath($invoicePath, 'invoice.pdf');

        $this->mailer->send($email);
    }
}
