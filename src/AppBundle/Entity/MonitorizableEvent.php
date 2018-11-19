<?php

    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use JMS\Serializer\Annotation as Serializer;
    use JMS\Serializer\Annotation\ExclusionPolicy;
    use JMS\Serializer\Annotation\Expose;
    use JMS\Serializer\Annotation\MaxDepth;
    use Doctrine\Common\Collections\ArrayCollection;
    use \DateTime;

    /**
     * Monitorizable Event Tabble
     * @ORM\Table(name="monitorizableEvent")
     * @ORM\Entity(repositoryClass="AppBundle\Repository\MonitorizableEventRepository")
     * @ExclusionPolicy("all")
     */
    class MonitorizableEvent
    {

	const STATUS_SUCCESS = 0;
	const STATUS_FAILURE = 1;
	const STATUS_UNDEFINED = 2;
	const STATUS_EXPIRED = 3;
	
	const STATUS_MESSAGES = [
	    'status.success',
	    'status.failure',
	    'status.undefined',
	    'status.expired'
	];
	
	const UNITS = [
	    1 => 'label.hours',
	    2 => 'label.days',
	    3 => 'label.weeks',
	    4 => 'label.months',
	    5 => 'label.years',
	];

	/**
         * @var string
         * @Expose
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

	/**
	* @ORM\Column(name="name", type="string", nullable=false)
	* @Expose
	*/
        private $name;

        /**
         * @var string
         * @Expose
         * @ORM\Column(name="successCondition", type="string", nullable=true)
         */
        private $successCondition;

        /**
         * @var string
         * @Expose
         * @ORM\Column(name="failureCondition",type="string", nullable=true)
         */
        private $failureCondition;

        /**
         * @var string
         * @Expose
         * @ORM\Column(name="filterCondition",type="string", nullable=true)
         */
        private $filterCondition;
	
	/**
         * @var integer
         * @Expose
         * @ORM\Column(name="frecuency", type="integer", nullable=true)
         */
	private $frecuency;

	/**
         * @var integer
         * @Expose
         * @ORM\Column(name="unit", type="integer", nullable=true, options={"comment":"1-Hours,2-Days,3-Weeks,4-Months,5-Years"})
         */
        private $unit;
	
	/**
	* @ORM\OneToMany (targetEntity="Event", mappedBy="monitorizableEvent", cascade={"persist"})
	* @ORM\JoinColumn(nullable=true);
	*/
	private $events;

	public function __construct() {
	    $this->events = new ArrayCollection();
	}

	public function setFilterFromEvent(Event $event) {
	    $filter = [ 'subject' => $event->toArray()['subject'] ];
	    $this->setFilterCondition(http_build_query($filter));
	}

	public function getId() {
	    return $this->id;
	}

	public function getName() {
	    return $this->name;
	}

	public function getSuccessCondition() {
	    return $this->successCondition;
	}

	public function getFailureCondition() {
	    return $this->failureCondition;
	}
	public function getFilterCondition() {
	    return $this->filterCondition;
	}

	public function getFrecuency() {
	    return $this->frecuency;
	}

	public function getUnit() {
	    return $this->unit;
	}

	public function setName($name) {
	    $this->name = $name;
	}

	public function setSuccessCondition($successCondition) {
	    $this->successCondition = $successCondition;
	}

	public function setFailureCondition($failureCondition) {
	    $this->failureCondition = $failureCondition;
	}

	public function setFilterCondition($filterCondition) {
	    $this->filterCondition = $filterCondition;
	}

	public function setFrecuency($frecuency) {
	    $this->frecuency = $frecuency;
	}

	public function setUnit($unit) {
	    $this->unit = $unit;
	}
	
	/**
	* 
	* @return ArrayCollection|Event[]
	*/
	public function getEvents() {
	    return $this->events;
	}

	public function addEvents(Event $event)
	{
	    $this->events->add($event);
	}

	public function removeEvents(Event $event)
	{
	    $this->eevents->removeElement($event);
	}
	
	/**
	 * @return Event|null Return the last matched event for this MonitorizableEvent
	 */
	
	public function getLastEvent() {
	    $events = $this->getEvents()->getValues();
	    if ( end($events) == false ) {
		return null;
	    } else {
		return end($events);
	    }
	}

	public function testSuccess (Event $event) {
	    if ( $this->getSuccessCondition() === null || $this->getSuccessCondition() === '' ) {
		return 0;
	    }
	    $patternSuccess=$this->__buildRexEpr($this->getSuccessCondition());
	    $target = strip_tags ( $event->getSubject().$event->getDetails() );
	    $test = preg_match($patternSuccess, $target, $matchSuccess);
	    return $test;
	}

	public function testFailure (Event $event) {
	    if ( $this->getFailureCondition() === null || $this->getFailureCondition() === '' ) {
		return 0;
	    }
	    $patternSuccess=$this->__buildRexEpr($this->getFailureCondition());
	    $target = strip_tags ( $event->getSubject().$event->getDetails() );
	    $test = preg_match($patternSuccess, $target, $matchSuccess);
	    return $test;
	}
	

	public function testExpired () {
	    $today = new \DateTime();
	    if ( $today > $this->getExpirationDate() ) {
		return true;
	    } else { 
		return false;
	    }
	}

	public function getExpirationDate () {
	    $lastEvent = $this->getLastEvent();
	    $frecuency = $this->getFrecuency().' '.$this->getUnitString();
	    if ($lastEvent !== null && $lastEvent !== false ) {
		$date = $lastEvent->getDate();
		$interval = date_interval_create_from_date_string($frecuency);
		$estimated_date = new DateTime($date->format(\DateTime::ISO8601));
		$estimated_date->add($interval);
		return $estimated_date;
	    } else {
		return date_add(new \DateTime(), date_interval_create_from_date_string($frecuency));
	    }
	}
	
	public function testStatus (Event $event = null) {
	    if ($event == null ) {
	    	return self::STATUS_UNDEFINED;
	    }
	    $testSuccess = $this->testSuccess($event);
	    $testFailure = $this->testFailure($event);
	    $testExpired = $this->testExpired();
	    if ( $testExpired == true ) {
		return self::STATUS_EXPIRED;
	    }
	    if ( $testSuccess == 1 && $testFailure == 0 ) {
		return self::STATUS_SUCCESS;
	    } else if ( $testSuccess == 0 && $testFailure == 1 ) {
		return self::STATUS_FAILURE;
	    }
	    return self::STATUS_UNDEFINED;
	}
	
	public function testLastStatus () {
	    $status = $this->testStatus($this->getLastEvent());
	    return $status;
	}

	public function __toString() {
	    return $this->name;
	}

	public function getUnitString () {
	    $units = [
		1 => 'hours',
		2 => 'days',
		3 => 'weeks',
		4 => 'months',
		5 => 'years'
	    ];
	    return $units[$this->unit];
	}

	private function __buildRexEpr($string) {
	    $reg_exp = '/(.*)';
	    $string_space = preg_replace("/\ /", "\s", preg_quote($string));
	    return $reg_exp.$string_space.'(.*)/iAs';
	}
}
