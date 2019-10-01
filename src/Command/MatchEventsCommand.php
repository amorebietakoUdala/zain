<?php

// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use App\Services\MatchEventsService;
use Doctrine\ORM\EntityManagerInterface;

class MatchEventsCommand extends Command
{
    private $em = null;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:match-events')
            // the short description shown while running "php bin/console list"
            ->setDescription('Match monitorizable events with events.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('It tryes to match monitorizable events with events.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $matchEventsService = new MatchEventsService($this->em, $output);
        $matchEventsService->execute();
    }
}
