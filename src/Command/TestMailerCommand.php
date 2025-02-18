<?php

namespace Bocum\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:test-mailer')]
class TestMailerCommand extends Command
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('test@example.com')
            ->to('recipient@example.com')
            ->subject('Test Email from Symfony')
            ->text('This is a test email from Symfony Mailer.')
            ->html('<p>This is a test email from Symfony Mailer.</p>');

        try {
            $this->mailer->send($email);
            $output->writeln('<info>Email sent successfully!</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to send email: ' . $e->getMessage() . '</error>');
        }

        return Command::SUCCESS;
    }
}
