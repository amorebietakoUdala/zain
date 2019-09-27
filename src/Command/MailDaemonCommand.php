<?php

// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use App\Entity\Event;
use App\Services\MatchEventsService;
use Doctrine\ORM\EntityManagerInterface;
use SecIT\ImapBundle\Service\Imap;
use Doctrine\Common\Persistence\ManagerRegistry;

class MailDaemonCommand extends Command
{
    private $em = null;
    private $imap = null;
    private $doctrine;

    public function __construct(EntityManagerInterface $em, Imap $imap, ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->em = $em;
        $this->imap = $imap;
        $this->doctrine = $doctrine;
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
            ->setHelp('This command starts the daemon that reads emails and writes them into the database.')
        ;
    }

    private static function getEntityManager()
    {
        if (!self::$entityManager->isOpen()) {
            self::$entityManager = self::$entityManager->create(
      self::$entityManager->getConnection(), self::$entityManager->getConfiguration());
        }

        return self::$entityManager;
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
            $isConnectable = false;
            try {
                $isConnectable = $this->imap->testConnection('office365', true);
            } catch (\Exception $exception) {
                $output->writeln("EXCEPTION: Can't connect to Mail server!!!");
                $output->writeln('EXCEPTION: '.$exception->getMessage());
                $output->writeln('Retrying in one minute...');
                sleep(1 * 60); // Itxoin minutu bat eta berriro saiatu.
            }
            while (!$isConnectable) {
                try {
                    $isConnectable = $this->imap->testConnection('office365', true);
                } catch (\Exception $exception) {
                    $output->writeln("EXCEPTION: Can't connect to Mail server!!!");
                    $output->writeln('EXCEPTION: '.$exception->getMessage());
                    $output->writeln('Retrying in one minute...');
                    sleep(1 * 60); // Itxoin minutu bat eta berriro saiatu.
                }
            }
            $output->writeln('Test Connection Successfull.');
            try {
                $mailbox = $this->imap->get('office365');
                $output->writeln('Connected.');
                $mailsIds = $mailbox->searchMailbox('ALL');
                $eventsAdded = 0;
                foreach ($mailsIds as $mailId) {
                    $output->writeln('MailId: '.$mailId.' LastMaxMailId: '.$lastMaxMailId);
                    if ($mailId > $lastMaxMailId) {
                        $mail = $mailbox->getMail($mailId);
                        $mailbox->markMailAsUnread($mailId);
                        $output->writeln($mailId);
                        $output->writeln($mail->date);
                        $event = Event::__parseEvent($mail);
                        try {
                            $this->em->persist($event);
                            ++$eventsAdded;
                        } catch (\Exception $exception) {
                            $output->writeln('EXCEPTION: '.$exception->getMessage());
                            $output->writeln('Message: '.$mailId.' skipped.');
                        }
                    }
                }
                $this->em->flush();
                if (count($mailsIds) > 0) {
                    $lastMaxMailId = max($mailsIds);
                }
                $output->writeln('New Events:'.$eventsAdded);
                $output->writeln('Matching Events...');
                $matchEventsService = new MatchEventsService($this->em, $output);
                $matchEventsService->execute();
                $output->writeln('Events Matched.');
                $output->writeln('Going To Sleep...');

                sleep(5 * 60); // Bost minuturo
            } catch (\Exception $exception) {
                $output->writeln('EXCEPTION: '.$exception->getMessage());
//                $output->writeln('Resetting EntityManager...');
                // Birsortu EntityManager badaezpada,
//                $this->doctrine->resetManager();
                $this->em = $this->doctrine->getManager();
//                $output->writeln('Reset Done...');
                $output->writeln('Retrying in one minute...');
                sleep(1 * 60); // Itxoin minutu bat eta berriro saiatu.
            }
        }
        $output->writeln("Daemon's End");
    }
}
