<?php

namespace App\Command;

use App\Entity\MonitorizableEvent;
use App\Repository\MonitorizableEventRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

#[AsCommand('app:daily-report-command', 'Send an email with the Monitorizable event status')]
class DailyReportCommand extends Command
{
    public function __construct(
        private readonly MonitorizableEventRepository $meventRepo, 
        private readonly MailerInterface $mailer, 
        private Environment $twig, 
        private readonly string $mailerFrom, 
        private readonly array $mailerTo)
    {
        parent::__construct();   
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->sendMessage('[Artzain] Eguneko txostena', $this->mailerTo);
        return Command::SUCCESS;
    }

    /**
     * @return array $counters
     */
    private function __countStatusTypes(array $mevents)
    {
        $counters = [
            MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_SUCCESS] => 0,
            MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_FAILURE] => 0,
            MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_UNDEFINED] => 0,
            MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_EXPIRED] => 0,
        ];
        foreach ($mevents as $mevent) {
            $lastStatus = $mevent->testLastStatus();
            if (MonitorizableEvent::STATUS_SUCCESS == $lastStatus) {
                $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_SUCCESS]] = $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_SUCCESS]] + 1;
            } elseif (MonitorizableEvent::STATUS_FAILURE == $lastStatus) {
                $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_FAILURE]] = $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_FAILURE]] + 1;
            } elseif (MonitorizableEvent::STATUS_UNDEFINED == $lastStatus) {
                $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_UNDEFINED]] = $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_UNDEFINED]] + 1;
            } else {
                $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_EXPIRED]] = $counters[MonitorizableEvent::STATUS_MESSAGES[MonitorizableEvent::STATUS_EXPIRED]] + 1;
            }
        }

        return $counters;
    }

    private function sendMessage($subject, $emails)
    {
        $mevents = $this->meventRepo->findAll();
        $orderedMonitorizableEvents = $this->orderMonitorizableEventsByStatus($mevents);
        $counters = $this->__countStatusTypes($mevents);
        $email = (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to(...$emails)
            ->subject($subject)
            ->htmlTemplate('/mevent/mail.html.twig')
            ->context([
                'mevents' => $orderedMonitorizableEvents,
                'counters' => $counters,
            ]);
        $this->mailer->send($email);
        return;
    }

    private function orderMonitorizableEventsByStatus(array $mevents) {
        $clusteredMonitorizableEvents = [];
        /** @var MonitorizableEvent $mevent */
        foreach ($mevents as $mevent) {
            $clusteredMonitorizableEvents[$mevent->testLastStatus()][] = $mevent;  
        }
        $keys = array_keys($clusteredMonitorizableEvents);
        arsort($keys);
        $orderedMonitorizableEvents = [];
        foreach ($keys as $key) {
            
            if (array_key_exists($key,$clusteredMonitorizableEvents)) {
                $orderedMonitorizableEvents[$key] = $clusteredMonitorizableEvents[$key]; 
            }
        }
        return $orderedMonitorizableEvents;
    }
}
