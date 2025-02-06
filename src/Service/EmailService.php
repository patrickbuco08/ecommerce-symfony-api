<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use App\Entity\Order;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, Environment $twig, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function sendInvoiceEmail(Order $order): void
    {
        $this->logger->info('Sending ...');

        $userEmail = $order->getUser()->getEmail();
        $invoicePath = $order->getInvoicePath();

        $email = (new Email())
            ->from('no-reply@yourdomain.com')
            ->to('jpbuco@cvsu.edu.ph')
            ->subject('Your Order Invoice')
            ->html('<p>See Twig integration for better HTML integration!</p>');
        // ->html($this->twig->render('emails/invoice_email.html.twig', ['order' => $order]))
        // ->attachFromPath($invoicePath, 'invoice.pdf');

        try {
            $this->mailer->send($email);
            $this->logger->info('Invoice email sent successfully to ' . $userEmail);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send email: ' . $e->getMessage());
        }
    }
}
