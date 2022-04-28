<?php

namespace App\Services;

use App\Entity\MonitorizableEvent;
use App\Entity\Event;

/**
 * Description of MatchEventsService
 * Searches emails in the specified mailbox and persisted them in the database.
 *
 * @author ibilbao
 */
class MatchEventsService
{
    private $em = null;
    private $output = null;

    public function __construct($em, $output)
    {
        $this->em = $em;
        $this->output = $output;
    }

    public function execute($events): array
    {
        $output = $this->output;
        $output->writeln('Matching start');
        $em = $this->em;
        $matchedEvents = [];
        /** @var MonitorizableEvent[] $mevents */
        $mevents = $em->getRepository(MonitorizableEvent::class)->findAll([], ['date' => 'ASC']);
        foreach ($events as $event) {
            foreach ($mevents as $mevent) {
                /** If matches filter condition and success or failure condition add to matched events.
                 *  Also add the monitorizableEvent object to the event object.
                 */
                /** @var Event $event */
                if ( ($mevent->testSuccess($event) || $mevent->testFailure($event)) && $mevent->testFilterCondition($event) ) {
                    $output->writeln('Monitorizable Event: '.$mevent->getId());     
                    $output->writeln('Filter Condition: '.$mevent->getFilterCondition());
                    $output->writeln('Success Condition: '.$mevent->getSuccessCondition());
                    $output->writeln('Failure Condition: '.$mevent->getFailureCondition());
                    /** @var Event $lastEvent */
                    $lastEvent = $em->getRepository(Event::class)->findLastMatchedEvent($mevent);
                    if ( null !== $lastEvent ) {
                        $output->writeln('LastEvent: '.$lastEvent->getMailId().': '.$lastEvent->getSubject().'on date '.$lastEvent->getDate()->format('Y-m-d H:i:s'));
                        if (trim($lastEvent->getMailId()) !== trim($event->getMailId())) {
                            $output->writeln('newEvent: '.$event->getMailId().': '.$event->getSubject().'on date '.$event->getDate()->format('Y-m-d H:i:s'));
                            $output->writeln('newEvent success: '.$mevent->testSuccess($event));
                            $output->writeln('newEvent failure: '.$mevent->testFailure($event));
                            $event->setMonitorizableEvent($mevent);
                            $em->persist($event);
                            $matchedEvents[] = $event;
                        } else {
                            $output->writeln('Already matched: LastEventId '. $lastEvent->getMailId(). ' = NewEventId '.$event->getMailId());
                        }
                    } else {
                        $output->writeln('No last event found.');
                        $output->writeln('newEvent: '.$event->getMailId().': '.$event->getSubject().'on date '.$event->getDate()->format('Y-m-d H:i:s'));
                        $output->writeln('newEvent success: '.$mevent->testSuccess($event));
                        $output->writeln('newEvent failure: '.$mevent->testFailure($event));
                        $event->setMonitorizableEvent($mevent);
                        $em->persist($event);
                        $matchedEvents[] = $event;
                    }
                    break;
                }
            }
        }
        $em->flush();
        $output->writeln('Matching end');

        return $matchedEvents;
    }
}
