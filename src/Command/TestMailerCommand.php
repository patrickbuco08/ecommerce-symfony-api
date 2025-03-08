<?php

namespace Bocum\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:test-mailer')]
class TestMailerCommand extends Command
{

    public function __construct(private MailerInterface $mailer, private ParameterBagInterface $params)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from($this->params->get('mailer_sender'))
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
