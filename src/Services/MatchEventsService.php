<?php

namespace App\Services;

use App\Entity\MonitorizableEvent;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\MonitorizableEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Description of MatchEventsService
 * Searches emails in the specified mailbox and persisted them in the database.
 *
 * @author ibilbao
 */
class MatchEventsService
{
    private EntityManagerInterface $em;
    private OutputInterface $output;
    private EventRepository $eventRepo;
    private MonitorizableEventRepository $meventRepo;

    public function __construct(EntityManagerInterface $em, EventRepository $eventRepo, MonitorizableEventRepository $meventRepo)
    {
        $this->em = $em;
        $this->eventRepo = $eventRepo;
        $this->meventRepo = $meventRepo;
        $this->output = new NullOutput();
    }

    public function execute($events): array
    {
        $this->output->writeln('Matching start');
        $matchedEvents = [];
        /** @var MonitorizableEvent[] $mevents */
        $mevents = $this->meventRepo->findAll([], ['date' => 'ASC']);
        foreach ($events as $event) {
            foreach ($mevents as $mevent) {
                /** If matches filter condition and success or failure condition add to matched events.
                 *  Also add the monitorizableEvent object to the event object.
                 */
                /** @var Event $event */
                if ( ($mevent->testSuccess($event) || $mevent->testFailure($event)) && $mevent->testFilterCondition($event) ) {
                    $this->output->writeln('Monitorizable Event: '.$mevent->getId());     
                    $this->output->writeln('Filter Condition: '.$mevent->getFilterCondition());
                    $this->output->writeln('Success Condition: '.$mevent->getSuccessCondition());
                    $this->output->writeln('Failure Condition: '.$mevent->getFailureCondition());
                    /** @var Event $lastEvent */
                    $lastEvent = $this->eventRepo->findLastMatchedEvent($mevent);
                    if ( null !== $lastEvent ) {
                        $this->output->writeln('LastEvent: '.$lastEvent->getMailId().': '.$lastEvent->getSubject().'on date '.$lastEvent->getDate()->format('Y-m-d H:i:s'));
                        if (trim($lastEvent->getMailId()) !== trim($event->getMailId())) {
                            $this->output->writeln('newEvent: '.$event->getMailId().': '.$event->getSubject().'on date '.$event->getDate()->format('Y-m-d H:i:s'));
                            $this->output->writeln('newEvent success: '.$mevent->testSuccess($event));
                            $this->output->writeln('newEvent failure: '.$mevent->testFailure($event));
                            $event->setMonitorizableEvent($mevent);
                            $this->em->persist($event);
                            $matchedEvents[] = $event;
                        } else {
                            $this->output->writeln('Already matched: LastEventId '. $lastEvent->getMailId(). ' = NewEventId '.$event->getMailId());
                        }
                    } else {
                        $this->output->writeln('No last event found.');
                        $this->output->writeln('newEvent: '.$event->getMailId().': '.$event->getSubject().'on date '.$event->getDate()->format('Y-m-d H:i:s'));
                        $this->output->writeln('newEvent success: '.$mevent->testSuccess($event));
                        $this->output->writeln('newEvent failure: '.$mevent->testFailure($event));
                        $event->setMonitorizableEvent($mevent);
                        $this->em->persist($event);
                        $matchedEvents[] = $event;
                    }
                    break;
                }
            }
        }
        $this->em->flush();
        $this->output->writeln('Matching end');

        return $matchedEvents;
    }

    public function setOutput(OutputInterface $output) {
        $this->output = $output;
        return $this;
    }
}
