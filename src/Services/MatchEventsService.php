<?php

namespace App\Services;

use App\Entity\MonitorizableEvent;
use App\Entity\Event;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErroldaService
 *
 * @author ibilbao
 */
class MatchEventsService {
    
    private $em = null;
    private $output = null;
    
    public function __construct($em, $output) {
        $this->em = $em;
	$this->output = $output;
    }
    
    public function execute() {
	$output = $this->output;
	$output->writeln("Matching start");
//	$isConnectable = $this->getContainer()->get('secit.imap')->testConnection('office365');
	$em = $this->em;
	$mevents = $em->getRepository(MonitorizableEvent::class)->findAll([],['date' => 'ASC']);
	foreach ($mevents as $mevent) {
	    $output->writeln('Monitorizable Event: '.$mevent->getId());
	    $lastEvent = $em->getRepository(Event::class)->findLastMatchedEvent($mevent);
	    $lastDate = null;
	    $from = null;
	    if ($lastEvent !== null ) {
		$output->writeln('LastEvent: '.$lastEvent->getMailId().': '.$lastEvent->getSubject().'on date '.$lastEvent->getDate()->format('Y-m-d H:i:s'));
		$lastDate = $lastEvent->getDate();
	    }
	    parse_str($mevent->getFilterCondition(),$criteria);
	    if ($lastDate !== null) {
		$interval = new \DateInterval('PT1S');
		$from = date_add($lastDate,$interval);
		$output->writeln('Fecha +1:'.$from->format('Y-m-d H:i:s'));
	    }
	    $events = $em->getRepository(Event::class)->findAllFromTo($criteria,$from,new \DateTime());
	    $output->writeln('Success Condition: '.$mevent->getSuccessCondition());
	    $output->writeln('Failure Condition: '.$mevent->getFailureCondition());
	    foreach ($events as $event) {
		$output->writeln('Events found!!!!');
		if ( $lastDate !== null ) {
		     $output->writeln('Since Date: '.$lastDate->format('Y-m-d H:i:s'));
		}
		$output->writeln($event->getMailId().': '.$event->getSubject().' on date '.$event->getDate()->format('Y-m-d H:i:s'));
		$event->setMonitorizableEvent($mevent);
		$output->writeln('Test Success: '.$mevent->testSuccess($event));
		$output->writeln('Test Failure: '.$mevent->testFailure($event));
		$em->persist($event);
	    }
	}
	$em->flush();
	$output->writeln("Matching end");
    }
}