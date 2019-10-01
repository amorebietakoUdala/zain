<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use PhpImap\IncomingMail;

/**
     * Monitorizable Event Tabble.
     *
     * @ORM\Table(name="events")
     * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
     * @ExclusionPolicy("all")
     */
    class Event
    {
        /**
         * @var string
         * @Expose
         * @ORM\Id
         * @ORM\Column(name="id", type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @var date
         * @Expose
         * @ORM\Column(name="date",type="datetime", nullable=false)
         */
        private $date;

        /**
         * @ORM\Column(name="origin", type="string", nullable=true)
         * @Expose
         */
        private $origin;

        /**
         * @ORM\Column(name="fromAddress", type="string", nullable=true)
         * @Expose
         */
        private $fromAddress;

        /**
         * @ORM\Column(name="subject", type="string", nullable=true)
         * @Expose
         */
        private $subject;

        /**
         * @ORM\Column(name="details", type="text", nullable=true )
         * @Expose
         */
        private $details;

        /**
         * @ORM\Column(name="mailId", type="bigint", nullable=true )
         * @Expose
         */
        private $mailId;

        /**
         * @ORM\Column(name="type", type="text", nullable=true )
         * @Expose
         */
        private $type;

        /**
         * @ORM\ManyToOne(targetEntity="MonitorizableEvent", inversedBy="events")
         */
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
                $html = mb_convert_encoding($mail->textHtml, 'UTF-8');
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
