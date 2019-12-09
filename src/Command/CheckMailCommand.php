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
use SecIT\ImapBundle\Service\Imap;

class CheckMailCommand extends Command
{
    private $em = null;
    private $imap = null;

    public function __construct(EntityManagerInterface $em, Imap $imap)
    {
        parent::__construct();
        $this->em = $em;
        $this->imap = $imap;
    }

    protected function configure()
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailId = $input->getArgument('mailId');
        $mailbox = $this->imap->get('office365');
        $mail = $mailbox->getMail($mailId);
        dump($mail);
        die;
        $event = Event::__parseEvent($mail);
//        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
//        $this->em->persist($event);
//        $this->em->flush();
        dump($event);
        $output->writeln($mailId);
    }
}
