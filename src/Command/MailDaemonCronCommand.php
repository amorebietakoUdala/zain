<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Services\MatchEventsService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand('app:mail-daemon-cron', 'Daemon that reads emails.')]
class MailDaemonCronCommand extends Command
{
    protected ?ClientManager $cm = null;
    protected ?array $token = null;
    protected ?\DateTime $tokenExpirationDate = null;
    protected ?\Webklex\PHPIMAP\Client $IMAPClient = null;

    public function __construct(
        protected HttpClientInterface $client, 
        protected ?string $tenantId, 
        protected ?string $clientId, 
        protected ?string $clientSecret, 
        protected ?string $mailbox, 
        protected ?EntityManagerInterface $em, 
        protected EventRepository $eventRepo, 
        protected ?MatchEventsService $mEventService, 
        protected string $imapArchiveFolder, 
        protected string $imapInboxFolder, 
        protected int $limitMailsPerRound=10)
    {
        parent::__construct();
        $this->cm = new ClientManager();
    }

    protected function configure(): void
    {
        $this
            // the "--help" option
            ->setHelp('This command starts the daemon that reads emails and writes them into the database.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $output->writeln("Cron Start");
        /** @var Event $event */
        $event = $this->eventRepo->findOneBy([], ['mailId' => 'DESC']);
        if (null !== $event) {
            $lastMaxMailId = $event->getMailId();
        } else {
            $lastMaxMailId = 0;
        }
        if ($this->token === null) {
            $this->getToken($output);
        }
            $this->getIMAPClient($output);
            $output->writeln('Test Connection Successfull.');
            try {
                $start = new \DateTime();
                $output->writeln('Start of add events: ' . $start->format('Y-m-d H:i:s'));
                /** @var  \Webklex\PHPIMAP\Folder $folder */
                $folder = $this->IMAPClient->getFolderByName($this->imapInboxFolder);                         
                $output->writeln('Mailbox: ' . $folder->name);
                $output->writeln('LastMaxMailId: '.$lastMaxMailId);
                $output->writeln('Imap INBOX folder: ' . $this->imapInboxFolder);
                $output->writeln('Imap Archive folder: ' . $this->imapArchiveFolder);
                $output->writeln('Limit mails per round: ' . $this->limitMailsPerRound);
                $messages = $folder->messages()->limit($this->limitMailsPerRound)->getByUidGreater($lastMaxMailId);
                $eventsAdded = 0;
                $newEvents = [];
                $currentMailIds = [];
                /** @var  \Webklex\PHPIMAP\Message $message */
                $output->writeln('New Messages: '. count($messages));
                foreach ($messages->collect() as $message) {
                    $output->writeln('Start loop '.$eventsAdded);
                    $output->writeln('MailId: ' . $message->getUid() . ' LastMaxMailId: ' . $lastMaxMailId . ' MessageDate: '. $message->getDate());
                    $currentMailIds[] = $message->getUid();
                    $message = $folder->messages()->getMessageByUid($message->getUid());
                    $event = Event::__parseEventFromIMAPMessage($message);
                    $this->em->persist($event);
                    ++$eventsAdded;
                    $newEvents[] = $event;
                }
                $this->em->flush();
                if ( $eventsAdded > 0) {
                    $lastMaxMailId = max($currentMailIds);
                    $output->writeln('New LastMaxMailId: ' . $lastMaxMailId);
                }
                $output->writeln('New Events: ' . $eventsAdded);
                $end = new \DateTime();
                $elapsedTime = ($start->diff($end))->format('%H:%I:%S');
                $output->writeln('End of add events: ' . (new \DateTime())->format('Y-m-d H:i:s'));
                $output->writeln('ElapsedTime: ' . $elapsedTime);
                $start = new \DateTime();
                $output->writeln('Start of match events: ' . $start->format('Y-m-d H:i:s'));
                $this->mEventService->setOutput($output);
                $matchedEvents = $this->mEventService->execute($newEvents);
                $output->writeln('MatchedEvents: '.count($matchedEvents));
                $this->moveMails($matchedEvents, $output);
                $end = new \DateTime();
                $elapsedTime = ($start->diff($end))->format('%H:%I:%S');
                $output->writeln('End of match events: ' . (new \DateTime())->format('Y-m-d H:i:s'));
                $output->writeln('ElapsedTime: ' . $elapsedTime);
                $output->writeln('Going To Sleep...');
    
            } catch (\Exception $exception) {
                $output->writeln('EXCEPTION: ' . $exception->getMessage());
            }

        $output->writeln("Cron End");
        return Command::SUCCESS;
    }

    protected function getToken(OutputInterface $output) {
        $scope='https://outlook.office365.com/.default';
        $body = 
            'client_id='.$this->clientId.
            '&client_secret='.$this->clientSecret.
            '&scope='.urlencode($scope).
            '&grant_type=client_credentials';
        try {
            $response = $this->client->request(
                'POST',
                "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token",[
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'body' => $body
                ],
            );
            $content = $response->getContent();
            $content = $response->toArray();
            $this->token = $content;
            $this->tokenExpirationDate = (new \DateTime())->add(new \DateInterval('PT' . $this->token['expires_in'] . 'S'));
            $output->writeln("Access Token: ".$this->token['access_token']);
            $output->writeln("Token expiration date: ".$this->tokenExpirationDate->format('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            $output->writeln("EXCEPTION: Can't obtain token");
            $output->writeln('EXCEPTION: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getIMAPClient(OutputInterface $output): Client {
        $isConnected = false;
        while (!$isConnected) {
            if ($this->token === null || $this->checkAccessTokenExpired($output) ) {
                $this->getToken($output);
            }
            try {
                $this->IMAPClient = $this->cm->make([
                    'host'           => 'outlook.office365.com',
                    'port'           => 993,
                    'encryption'     => 'ssl',
                    'validate_cert'  => true,
                    'protocol'       => 'imap',
                    'username'       => $this->mailbox,
                    'password'       => $this->token['access_token'],
                    'authentication' => "oauth",
                ]);
                $this->IMAPClient->connect();
                $isConnected = $this->IMAPClient->isConnected();
            } catch (\Exception $exception) {
                $output->writeln("EXCEPTION: Can't connect to Mail server!!!");
                $output->writeln('EXCEPTION: ' . $exception->getMessage());
                $output->writeln('Retrying in one minute...');
                sleep(1 * 60); // Itxoin minutu bat eta berriro saiatu.
            }
        }
        $output->writeln('Connected');
        return $this->IMAPClient;
    }

    protected function checkAccessTokenExpired(OutputInterface $output) {
        /** Check if expires in less than 15 minutes */
        $interval = new \DateInterval("PT15M");
        $interval->invert = 1;
        $copy = clone $this->tokenExpirationDate;
        $copy->add($interval);
        if (new \DateTime() > $copy) {
            $output->writeln('Token about to expire, need to renew');
        }       
        return ( new \DateTime() > $copy );
    }

    protected function moveMails(array $matchedEvents, OutputInterface $output) {
        foreach ($matchedEvents as $event) {
            if (null !== $event->getMailId()) {
                try {
                    /** @var \Webklex\PHPIMAP\Folder $folder */
                    $folder = $this->IMAPClient->getFolder($this->imapInboxFolder);
                    /** @var \Webklex\PHPIMAP\Message $message */
                    $message = $folder->query()->getMessageByUid($event->getMailId());
                    $message->move($this->imapArchiveFolder);
                    $output->writeln("MailId ".$event->getMailId(). ' correctly moved.');
                } catch (\Exception $e) {
                    $output->writeln('ERROR:' .$e->getMessage());
                    $output->writeln("MailId ".$event->getMailId(). ' could not be moved.');
                }
            }
        }
    }
}
