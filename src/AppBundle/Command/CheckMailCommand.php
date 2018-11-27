<?php
// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle
namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Entity\Event;

class CheckMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:get-mailId')
            // the short description shown while running "php bin/console list"
            ->setDescription('Check a mailId.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Check a mailId.")
	    ->addArgument('mailId', InputArgument::REQUIRED, 'Which mailId?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	$mailId = $input->getArgument('mailId');
	$mailbox = $this->getContainer()->get('secit.imap')->get('office365');
	$mail = $mailbox->getMail($mailId);
	dump($mail);
	$output->writeln(mb_detect_encoding($mail->textHtml));
	$event = Event::__parseEvent($mail);
	$em = $this->getContainer()->get('doctrine.orm.entity_manager');
//	$em->persist($event);
//	$em->flush();
	dump($event);
	$output->writeln($mailId);
    }
}