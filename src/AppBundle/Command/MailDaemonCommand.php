<?php
// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle
namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Entity\Event;
use AppBundle\Services\MatchEventsService;

class MailDaemonCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:mail-daemon')
            // the short description shown while running "php bin/console list"
            ->setDescription('Daemon that reads emails.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command starts the daemon that reads emails and writes them into the database.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
	$output->writeln("Daemon's Start");
	$em = $this->getContainer()->get('doctrine.orm.entity_manager');
	$event = $em->getRepository('AppBundle:Event')->findOneBy([],['mailId' => 'DESC']);
	if ($event !== null) {
	    $lastMaxMailId = $event->getMailId();
	} else {
	    $lastMaxMailId = 0;
	}
	$matchEventsService = new MatchEventsService($em,$output);
	while (true) {
	    $isConnectable = false;
	    try {
		$isConnectable = $this->getContainer()->get('secit.imap')->testConnection('office365', true);
	    } catch (\Exception $exception) {
		$output->writeln("EXCEPTION: Can't connect to Mail server!!!");
		$output->writeln("EXCEPTION: ".$exception->getMessage());
		$output->writeln("Retrying in one minute...");
		sleep(1*60); // Itxoin minutu bat eta berriro saiatu.
	    }
	    while (!$isConnectable) {
		try {
		    $isConnectable = $this->getContainer()->get('secit.imap')->testConnection('office365', true);
		} catch (\Exception $exception) {
		    $output->writeln("EXCEPTION: Can't connect to Mail server!!!");
		    $output->writeln("EXCEPTION: ".$exception->getMessage());
		    $output->writeln("Retrying in one minute...");
		    sleep(1*60); // Itxoin minutu bat eta berriro saiatu.
		}
	    }
	    $output->writeln("Test Connection Successfull.");
	    try {
		$mailbox = $this->getContainer()->get('secit.imap')->get('office365');
		$output->writeln("Connected.");
		$mailsIds = $mailbox->searchMailbox('ALL');
		$eventsAdded = 0;
		foreach ($mailsIds as $mailId) {
		    if ($mailId > $lastMaxMailId ) {
			$mail = $mailbox->getMail($mailId);
			$mailbox->markMailAsUnread($mailId);
			$output->writeln($mailId);
			$output->writeln($mail->date);
			$event = Event::__parseEvent($mail);
			$em->persist($event);
			$em->flush();
			$eventsAdded++;
		    }
		}
		if ( count($mailsIds) > 0 ) {
		    $lastMaxMailId = max($mailsIds);
		}
		$output->writeln('New Events:'.$eventsAdded);
		$output->writeln('Matching Events...');
		$matchEventsService->execute();
		$output->writeln('Events Matched.');
		$output->writeln('Going To Sleep...');

		sleep(5*60); // Bost minuturo
	    } catch (\Exception $exception) {
		$output->writeln("EXCEPTION: ".$exception->getMessage());
		$output->writeln("Retrying in one minute...");
		sleep(1*60); // Itxoin minutu bat eta berriro saiatu.
	    }
	}
	$output->writeln("Daemon's End");
    }
}