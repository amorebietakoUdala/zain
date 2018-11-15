<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use \Symfony\Component\Form\Extension\Core\Type\ButtonType;
use AppBundle\Entity\MonitorizableEvent;
use AppBundle\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

/**
 * Description of MonitorizableEventForm
 *
 * @author ibilbao
 */
class EventForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
	$builder
	    ->add('date', DateTimeType::class, [
		'widget' => 'single_text',
		'html5' => 'false',
		'format' => 'yyyy-MM-dd HH:mm',
	    ])
	    ->add('origin',null, [
		'label' => 'label.origin',
		'translation_domain' => 'messages'
	    ])
	    ->add('fromAddress',null, [
		'label' => 'label.fromAddress',
		'translation_domain' => 'messages'
	    ])
	    ->add('subject',null, [
		'label' => 'label.subject',
		'translation_domain' => 'messages'
	    ])
	    ->add('details',null, [
		'label' => 'label.details',
		'translation_domain' => 'messages'
	    ])
	    ->add('mailId',null, [
		'label' => 'label.mailId',
		'translation_domain' => 'messages'
	    ])
	    ->add('monitorizableEvent', EntityType::class, [
		'class' => 'AppBundle:MonitorizableEvent',
		'label' => 'label.monitorizableEvent',
		'translation_domain' => 'messages',
	    ])
	    ->add('save', ButtonType::class, [
		'label' => 'button.save',
		'translation_domain' => 'messages'
	    ])
	;
    }

    public function configureOptions(OptionsResolver $resolver) {
	$resolver->setDefaults([
	    'csrf_protection' => true,
	    'data_class' => Event::class,
	]);
    }

}
