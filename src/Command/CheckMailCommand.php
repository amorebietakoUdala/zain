<?php

// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle

namespace App\Command;

use App\Entity\Event;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Query\WhereQuery;

class CheckMailCommand extends Command
{
    protected ?ClientManager $cm = null;
    protected ?array $token = null;
    protected ?\DateTime $tokenExpirationDate = null;
    protected ?\Webklex\PHPIMAP\Client $IMAPClient = null;

    public function __construct(
        private EntityManagerInterface $em, 
        protected HttpClientInterface $client,
        protected ?string $mailbox, 
        protected ?string $tenantId, 
        protected ?string $clientId, 
        protected ?string $clientSecret, 
        private string $imapInboxFolder
    )
    {
        parent::__construct();
        $this->cm = new ClientManager();    
   }

    protected function configure(): void
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:get-mailId')
            // the short description shown while running "php bin/console list"
            ->setDescription('Check a mailId.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Check a mailId.')
        ->addArgument('mailId', InputArgument::REQUIRED, 'Which mailId?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int 
    {
      $this->getIMAPClient($output);
      //dd($this->imapInboxFolder);
      $mailId = $input->getArgument('mailId');
      try {
         $start = new \DateTime();
         $output->writeln('Start of add events: ' . $start->format('Y-m-d H:i:s'));
         /** @var  \Webklex\PHPIMAP\Folder $folder */
        //$folder = $this->IMAPClient->getFolderByName($this->imapInboxFolder);
        //  $output->writeln('Mailbox: ' . $folder->name);
        //  /** @var \Webklex\PHPIMAP\Query\WhereQuery $query */
        //  $messages = $folder->query()->text('Gorbea')->get();
        //  //$messages = $query->text('Gorbea')->get()->total();
        //  /** @var \Webklex\PHPIMAP\Message $message */
        //  foreach ($messages as $message) {
        //     //dump($message->getMessageId());
        //     dump($message->getUid(),$message->getSubject());
        //  }
        $query = new WhereQuery($this->IMAPClient);
        dump("Before Active Folder");
        $folder = $this->IMAPClient->setActiveFolder($this->imapInboxFolder);
        dump('Before Query');
        $message = $query->getMessageByUid($mailId)->setFetchFlagsOption(false);
        dump('After Query');
         if ($message === null) {
            $output->writeln("Message not Found");
            return Command::FAILURE;
         }
         dump('Before parse');
         $event = Event::__parseEventFromIMAPMessage($message);
         dd($event);
         $output->writeln(mb_detect_encoding($mail->textHtml));
         $event = Event::__parseEvent($mail);
   //        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
   //        $this->em->persist($event);
   //        $this->em->flush();
         dump($event);
         $output->writeln($mailId);
      } catch (Exception $e) {

      }
    }

    protected function getIMAPClient(OutputInterface $output): Client {
    //   $isConnected = false;
    //   while (!$isConnected) {
    //       if ($this->token === null || $this->checkAccessTokenExpired($output) ) {
               $this->getToken($output);
    //       }
    //       try {
            $this->cm = new ClientManager([
                'default' => 'default',
                'accounts' => [
                    'default' => [// account identifier
                        'host'           => 'outlook.office365.com',
                        'port'           => 993,
                        'encryption'     => 'ssl',
                        'validate_cert'  => true,
                        'protocol'       => 'imap',
                        'username'       => $this->mailbox,
                        'password'       => $this->token['access_token'],
                        'authentication' => "oauth",
                    ],
                ],
                'options' => [
                    'delimiter' => '/',
                    'fetch' => \Webklex\PHPIMAP\IMAP::FT_PEEK,
                    'sequence' => \Webklex\PHPIMAP\IMAP::ST_UID,
                    'fetch_body' => false,
                    'fetch_flags' => false,
                    'soft_fail' => false,
                    'rfc822' => true,
                    'debug' => true,
                    'uid_cache' => true,
                    // 'fallback_date' => "01.01.1970 00:00:00",
                    'boundary' => '/boundary=(.*?(?=;)|(.*))/i',
                    'message_key' => 'list',
                    'fetch_order' => 'asc',
                    'dispositions' => ['attachment', 'inline'],
                    'common_folders' => [
                        "root" => "INBOX",
                        "junk" => "INBOX/Junk",
                        "draft" => "INBOX/Drafts",
                        "sent" => "INBOX/Sent",
                        "trash" => "INBOX/Trash",
                    ],
                    'decoder' => [
                        'message' => 'utf-8', // mimeheader
                        'attachment' => 'utf-8' // mimeheader
                    ],
                    'open' => [
                        // 'DISABLE_AUTHENTICATOR' => 'GSSAPI'
                    ]
                ],
                'flags' => ['recent', 'flagged', 'answered', 'deleted', 'seen', 'draft'],
                'events' => [
                    "message" => [
                        'new' => \Webklex\PHPIMAP\Events\MessageNewEvent::class,
                        'moved' => \Webklex\PHPIMAP\Events\MessageMovedEvent::class,
                        'copied' => \Webklex\PHPIMAP\Events\MessageCopiedEvent::class,
                        'deleted' => \Webklex\PHPIMAP\Events\MessageDeletedEvent::class,
                        'restored' => \Webklex\PHPIMAP\Events\MessageRestoredEvent::class,
                    ],
                    "folder" => [
                        'new' => \Webklex\PHPIMAP\Events\FolderNewEvent::class,
                        'moved' => \Webklex\PHPIMAP\Events\FolderMovedEvent::class,
                        'deleted' => \Webklex\PHPIMAP\Events\FolderDeletedEvent::class,
                    ],
                    "flag" => [
                        'new' => \Webklex\PHPIMAP\Events\FlagNewEvent::class,
                        'deleted' => \Webklex\PHPIMAP\Events\FlagDeletedEvent::class,
                    ],
                ],
                'masks' => [
                    'message' => \Webklex\PHPIMAP\Support\Masks\MessageMask::class,
                    'attachment' => \Webklex\PHPIMAP\Support\Masks\AttachmentMask::class
                ]
            ]);
            //   $this->IMAPClient = $this->cm->make([
            //       'host'           => 'outlook.office365.com',
            //       'port'           => 993,
            //       'encryption'     => 'ssl',
            //       'validate_cert'  => true,
            //       'protocol'       => 'imap',
            //       'username'       => $this->mailbox,
            //       'password'       => $this->token['access_token'],
            //       'authentication' => "oauth",
            //       'options' => [
            //         'debug' => true,
            //         'fetch_flags' => false,
            //       ]
            //   ]);
            $this->IMAPClient = $this->cm->account('default');
            $this->cm::get('options.fech_flags');
              $this->IMAPClient->connect();
              dump($this->IMAPClient->getConfig());
              $isConnected = $this->IMAPClient->isConnected();
    //       } catch (\Exception $exception) {
    //           $output->writeln("EXCEPTION: Can't connect to Mail server!!!");
    //           $output->writeln('EXCEPTION: ' . $exception->getMessage());
    //           $output->writeln('Retrying in one minute...');
    //           sleep(1 * 60); // Itxoin minutu bat eta berriro saiatu.
    //       }
    //   }
      $output->writeln('Connected');
      return $this->IMAPClient;
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


}