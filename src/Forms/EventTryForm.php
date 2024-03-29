<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use \Symfony\Component\Form\Extension\Core\Type\ButtonType;
use App\Entity\MonitorizableEvent;


/**
 * Description of MonitorizableEventForm
 *
 * @author ibilbao
 */

class EventTryForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void {
	$builder
	    ->add('successCondition', null, [
		'label' => 'label.successCondition',
		'translation_domain' => 'messages'
	    ])
	    ->add('failureCondition', null, [
		'label' => 'label.failureCondition',
		'translation_domain' => 'messages'
	    ])
	    ->add('save', ButtonType::class, [
		'label' => 'button.save',
		'translation_domain' => 'messages'
	    ])
	    ->add('test', ButtonType::class, [
		'label' => 'button.test',
		'translation_domain' => 'messages'
	    ])
	;
    }

    public function configureOptions(OptionsResolver $resolver): void {
	$resolver->setDefaults([
	    'csrf_protection' => true,
	    'data_class' => MonitorizableEvent::class,
	]);
    }

}
