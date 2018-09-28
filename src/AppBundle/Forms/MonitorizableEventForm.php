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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Unit;
use AppBundle\Entity\MonitorizableEvent;


/**
 * Description of MonitorizableEventForm
 *
 * @author ibilbao
 */
class MonitorizableEventForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
	$builder
	    ->add('name', null, [
		'constraints' => [new NotBlank(),],
		'label' => 'label.name',
		'translation_domain' => 'messages'
	    ])
	    ->add('successCondition', null, [
		'constraints' => [new NotBlank(),],
		'label' => 'label.successCondition',
		'translation_domain' => 'messages'
	    ])
	    ->add('failureCondition',null, [
		'required' => false,
		'label' => 'label.failureCondition',
		'translation_domain' => 'messages'
	    ])
	    ->add('frecuency',null, [
		'required' => false,
		'attr' => ['min' => 1, 
			   'max' =>5], 
		'label' => 'label.frecuency',
		'translation_domain' => 'messages'
	    ])
	    ->add('unit', EntityType::class, [
		'required' => true,
		'expanded'=> true,
		'label' => 'label.unit',
		'class' => Unit::class,
		'translation_domain' => 'messages'
	    ])
//
//	    ->add('save', SubmitType::class, [
//		'label' => 'label.save',
//		'attr' => ['class' => 'btn btn-primary'],
//		'translation_domain' => 'messages'
//	    ])
	;
    }

    public function configureOptions(OptionsResolver $resolver) {
	$resolver->setDefaults([
	    'csrf_protection' => true,
	    'data_class' => MonitorizableEvent::class,
	]);
    }

}
