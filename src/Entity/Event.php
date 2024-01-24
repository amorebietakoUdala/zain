<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\EventRepository;
use JMS\Serializer\Annotation as JMS;
use PhpImap\IncomingMail;
use Webklex\PHPIMAP\Message;

#[JMS\ExclusionPolicy('all')]
#[ORM\Table(name: 'events')]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[JMS\Expose()]
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[JMS\Expose()]
    #[ORM\Column(name: 'date', type: 'datetime', nullable: false)]
    private $date;

    #[JMS\Expose()]
    #[ORM\Column(name: 'origin', type: 'string', nullable: true)]
    private $origin;

    #[JMS\Expose()]
    #[ORM\Column(name: 'fromAddress', type: 'string', nullable: true)]
    private $fromAddress;

    #[JMS\Expose()]
    #[ORM\Column(name: 'subject', type: 'string', nullable: true)]
    private $subject;

    #[JMS\Expose()]
    #[ORM\Column(name: 'details', type: 'text', nullable: true)]
    private $details;

    #[JMS\Expose()]
    #[ORM\Column(name: 'mailId', type: 'bigint', nullable: true)]
    private $mailId;

    #[JMS\Expose()]
    #[ORM\Column(name: 'type', type: 'text', nullable: true)]
    private $type;

    #[ORM\ManyToOne(targetEntity: 'MonitorizableEvent', inversedBy: 'events')]
    private $monitorizableEvent;

    public function getId()
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getFromAddress()
    {
        return $this->fromAddress;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function getMailId()
    {
        return $this->mailId;
    }

    public function setDate($date)
    {
        $this->date = new \DateTime($date);
    }

    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    public function setFromAddress($fromAddress)
    {
        $this->fromAddress = $fromAddress;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function setMailId($mailId)
    {
        $this->mailId = $mailId;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getMonitorizableEvent()
    {
        return $this->monitorizableEvent;
    }

    public function setMonitorizableEvent(MonitorizableEvent $monitorizableEvent = null)
    {
        $this->monitorizableEvent = $monitorizableEvent;
    }

    public static function __parseEvent(IncomingMail $mail): Event
    {
        $event = new self();
        $event->setDate($mail->date);
        $event->setOrigin('mail');
        $event->setSubject($mail->subject);
        if (null != $mail->textHtml) {
            $html = mb_convert_encoding((string) $mail->textHtml, 'UTF-8');
            $event->setDetails($html);
            $event->setType('html');
        } else {
            $event->setDetails($mail->textPlain);
            $event->setType('text');
        }
        $event->setMailId($mail->id);
        $event->setFromAddress($mail->fromAddress);

        return $event;
    }

    public static function __parseEventFromIMAPMessage(Message $message): Event
    {
        $event = new self();
        $event->setDate($message->getDate());
        $event->setOrigin('mail');
        $event->setSubject(mb_convert_encoding((string) $message->getSubject()->first(), 'UTF-8'));
        if (null != $message->getHTMLBody()) {
            $html = mb_convert_encoding($message->getHTMLBody(), 'UTF-8');
            $event->setDetails($html);
            $event->setType('html');
        } else {
            $event->setDetails($message->getTextBody());
            $event->setType('text');
        }
        $event->setMailId($message->getUid());
        $event->setFromAddress($message->getFrom()->first()->mail);

        return $event;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->id,
            'date' => $this->date,
            'origin' => $this->origin,
            'fromAddress' => $this->fromAddress,
            'subject' => $this->subject,
            'details' => $this->details,
            'mailId' => $this->mailId,
            'type' => $this->type,
            'monitorizableEvent' => $this->monitorizableEvent,
        ];

        return $array;
    }
}
