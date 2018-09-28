<?php

    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use JMS\Serializer\Annotation as Serializer;
    use JMS\Serializer\Annotation\ExclusionPolicy;
    use JMS\Serializer\Annotation\Expose;
    use JMS\Serializer\Annotation\MaxDepth;

    /**
     * Monitorizable Event Tabble
     * @ORM\Table(name="monitorizableEvent")
     * @ORM\Entity(repositoryClass="AppBundle\Repository\MonitorizableEventRepository")
     * @ExclusionPolicy("all")
     */
    class MonitorizableEvent
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
	* @ORM\Column(name="name", type="string", nullable=false)
	* @Expose
	*/
        private $name;

        /**
         * @var string
         * @Expose
         * @ORM\Column(name="successCondition", type="string", nullable=false)
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
         * @ORM\Column(name="frecuency", type="integer", nullable=true)
         */
        private $frecuency;

	/**
	/**
         * @var string
         * @Expose
         * @ORM\ManyToOne(targetEntity="Unit")
         */
        private $unit;
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

	public function setFrecuency($frecuency) {
	    $this->frecuency = $frecuency;
	}

	public function setUnit($unit) {
	    $this->unit = $unit;
	}

	public function __toString() {
	    return $this->tipoVia.' '.trim($this->descripcion);
	}
}
