<?php

// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use App\Entity\Event;
use App\Services\MatchEventsService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use SecIT\ImapBundle\Service\Imap;
use PhpImap\Mailbox;
use Doctrine\Persistence\ManagerRegistry;

class MailDaemonCommand extends Command
{
    private $em = null;
    private $imap = null;
    private $doctrine;
    private $params = null;

    public function __construct(EntityManagerInterface $em, Imap $imap, ManagerRegistry $doctrine, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->em = $em;
        $this->imap = $imap;
        $this->doctrine = $doctrine;
        $this->params = $params;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:mail-daemon')
            // the short description shown while running "php bin/console list"
            ->setDescription('Daemon that reads emails.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command starts the daemon that reads emails and writes them into the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Daemon's Start");
        $event = $this->em->getRepository('App:Event')->findOneBy([], ['mailId' => 'DESC']);
        if (null !== $event) {
            $lastMaxMailId = $event->getMailId();
        } else {
            $lastMaxMailId = 0;
        }

        while (true) {
            $this->waitUntillConnectable($output);
            $output->writeln('Test Connection Successfull.');
            try {
                $start = new \DateTime();
                $output->writeln('Start of add events: ' . $start->format('Y-m-d H:i:s'));
                $mailbox = $this->imap->get('office365');
                $mailbox->switchMailbox($this->params->get('imap_inbox_folder'));
                $output->writeln('Mailbox: ' . $mailbox->getImapPath());
                $output->writeln('Connected.');
                $mailsIds = $mailbox->searchMailbox('ALL');
                $eventsAdded = 0;
                $newEvents = [];
                foreach ($mailsIds as $mailId) {
                    $output->writeln('MailId: ' . $mailId . ' LastMaxMailId: ' . $lastMaxMailId);
                    if ($mailId > $lastMaxMailId) {
                        $mail = $mailbox->getMail($mailId);
                        $mailbox->markMailAsUnread($mailId);
                        $output->writeln($mailId);
                        $output->writeln($mail->date);
                        $event = Event::__parseEvent($mail);
                        try {
                            $this->em->persist($event);
                            ++$eventsAdded;
                            $newEvents[] = $event;
                        } catch (\Exception $exception) {
                            $output->writeln('EXCEPTION: ' . $exception->getMessage());
                            $output->writeln('Message: ' . $mailId . ' skipped.');
                        }
                    }
                }
                $this->em->flush();
                if (count($mailsIds) > 0) {
                    $lastMaxMailId = max($mailsIds);
                }
                $output->writeln('New Events: ' . $eventsAdded);
                $end = new \DateTime();
                $elapsedTime = ($start->diff($end))->format('%H:%I:%S');
                $output->writeln('End of add events: ' . (new \DateTime())->format('Y-m-d H:i:s'));
                $output->writeln('ElapsedTime: ' . $elapsedTime);
                $output->writeln('Matching Events...');
                $start = new \DateTime();
                $output->writeln('Start of match events: ' . $start->format('Y-m-d H:i:s'));
                $matchEventsService = new MatchEventsService($this->em, $output);
                $matchedEvents = $matchEventsService->execute($newEvents);
                $output->writeln('MatchedEvents: '.count($matchedEvents));
                $output->writeln('Before moving mails.');
                $this->__moveMails($matchedEvents, $mailbox, $output);
                $output->writeln('After moving mails.');
                $output->writeln('Events Matched.');
                $end = new \DateTime();
                $elapsedTime = ($start->diff($end))->format('%H:%I:%S');
                $output->writeln('End of match events: ' . (new \DateTime())->format('Y-m-d H:i:s'));
                $output->writeln('ElapsedTime: ' . $elapsedTime);
                $output->writeln('Going To Sleep...');
    
                sleep(5 * 60); // Bost minuturo
            } catch (\Exception $exception) {
                $output->writeln('EXCEPTION: ' . $exception->getMessage());
                $this->em = $this->doctrine->getManager();
                $output->writeln('Retrying in one minute...');
                sleep(1 * 60); // Itxoin minutu bat eta berriro saiatu.
            }
        }
        $output->writeln("Daemon's End");
    }

    private function waitUntillConnectable(OutputInterface $output) {
        $isConnectable = false;
        try {
            $isConnectable = $this->imap->testConnection('office365', true);
        } catch (\Exception $exception) {
            $output->writeln("EXCEPTION: Can't connect to Mail server!!!");
            $output->writeln('EXCEPTION: ' . $exception->getMessage());
            $output->writeln('Retrying in one minute...');
            sleep(1 * 60); // Itxoin minutu bat eta berriro saiatu.
        }
        while (!$isConnectable) {
            try {
                $isConnectable = $this->imap->testConnection('office365', true);
            } catch (\Exception $exception) {
                $output->writeln("EXCEPTION: Can't connect to Mail server!!!");
                $output->writeln('EXCEPTION: ' . $exception->getMessage());
                $output->writeln('Retrying in one minute...');
                sleep(1 * 60); // Itxoin minutu bat eta berriro saiatu.
            }
        }
        $output->writeln('Test Connection Successfull.');
    }    

    private function __moveMails(array $matchedEvents, Mailbox $mailbox, OutputInterface $output) {
        $archive_folder = $this->params->get('imap_archive_folder');
        foreach ($matchedEvents as $event) {
            if (null !== $event->getMailId()) {
                try {
                    $mailbox->moveMail($event->getMailId(), $archive_folder);
                    $output->writeln("MailId ".$event->getMailId(). ' correctly moved.');
                } catch (\Exception $e) {
                    $output->writeln('ERROR:' .$e->getMessage());
                    $output->writeln("MailId ".$event->getMailId(). ' could not be moved.');
                }
            }
        }
    }
}
