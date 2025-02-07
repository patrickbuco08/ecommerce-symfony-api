<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private ParameterBagInterface $params;

    public function __construct(MailerInterface $mailer, Environment $twig, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->params = $params;
    }

    public function sendEmail(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from('noreply@ecommerce.com')
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
            ->from('noreply@ecommerce.com')
            ->to($userEmail)
            ->subject('Your Order Invoice')
            ->html($this->twig->render('emails/invoice_email.html.twig', ['order' => $order]))
            ->attachFromPath($invoicePath, 'invoice.pdf');

        $this->mailer->send($email);
    }
}
