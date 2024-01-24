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
class EventSearchForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
	$builder
	    ->add('dateFrom', null, [
		'label' => 'label.from',
		'translation_domain' => 'messages'
	    ])
	    ->add('dateTo', null, [
		'label' => 'label.to',
		'translation_domain' => 'messages'
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
	    ->add('search', SubmitType::class, [
		'label' => 'button.search',
		'translation_domain' => 'messages'
	    ])
	    ->add('save', ButtonType::class, [
		'label' => 'button.save',
		'translation_domain' => 'messages'
	    ])
	    ->add('reset', ButtonType::class, [
		'label' => 'button.reset',
		'translation_domain' => 'messages'
	    ])
	;
    }

    public function configureOptions(OptionsResolver $resolver) {
	$resolver->setDefaults([
	    'csrf_protection' => true,
	    'data_class' => null,
	]);
    }

}
