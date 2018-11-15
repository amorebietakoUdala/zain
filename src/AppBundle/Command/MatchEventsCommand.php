<?php
// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle
namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use AppBundle\Entity\Event;
use AppBundle\Entity\MonitorizableEvent;
use AppBundle\Services\MatchEventsService;

class MatchEventsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:match-events')
            // the short description shown while running "php bin/console list"
            ->setDescription('Match monitorizable events with events.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("It tryes to match monitorizable events with events.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
	$matchEventsService = new MatchEventsService($em = $this->getContainer()->get('doctrine.orm.entity_manager'),$output);
	$matchEventsService->execute();
    }
    
//    protected function execute(InputInterface $input, OutputInterface $output)
//    {
//	$output->writeln("Matching start");
////	$isConnectable = $this->getContainer()->get('secit.imap')->testConnection('office365');
//	$em = $this->getContainer()->get('doctrine.orm.entity_manager');
//	$mevents = $em->getRepository(MonitorizableEvent::class)->findAll([],['date' => 'ASC']);
//	foreach ($mevents as $mevent) {
//	    $output->writeln('Monitorizable Event: '.$mevent->getId());
//	    $lastEvent = $em->getRepository(Event::class)->findLastMatchedEvent($mevent);
//	    $lastDate = null;
//	    $from = null;
//	    if ($lastEvent !== null ) {
//		$output->writeln('LastEvent: '.$lastEvent->getMailId().': '.$lastEvent->getSubject().'on date '.$lastEvent->getDate()->format('Y-m-d H:i:s'));
//		$lastDate = $lastEvent->getDate();
//	    }
//	    parse_str($mevent->getFilterCondition(),$criteria);
//	    if ($lastDate !== null) {
//		$interval = new \DateInterval('PT1S');
//		$from = date_add($lastDate,$interval);
//		$output->writeln('Fecha +1:'.$from->format('Y-m-d H:i:s'));
//	    }
//	    $events = $em->getRepository(Event::class)->findAllFromTo($criteria,$from,new \DateTime());
//	    $output->writeln('Success Condition: '.$mevent->getSuccessCondition());
//	    $output->writeln('Failure Condition: '.$mevent->getFailureCondition());
//	    foreach ($events as $event) {
//		$output->writeln('Events found!!!!');
//		if ( $lastDate !== null ) {
//		     $output->writeln('Since Date: '.$lastDate->format('Y-m-d H:i:s'));
//		}
//		$output->writeln($event->getMailId().': '.$event->getSubject().' on date '.$event->getDate()->format('Y-m-d H:i:s'));
//		$event->setMonitorizableEvent($mevent);
//		$output->writeln('Test Success: '.$mevent->testSuccess($event));
//		$output->writeln('Test Failure: '.$mevent->testFailure($event));
//		$em->persist($event);
//	    }
//	}
//	$em->flush();
//	$output->writeln("Matching end");
//    }
}