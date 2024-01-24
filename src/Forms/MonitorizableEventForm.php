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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\MonitorizableEvent;

/**
 * Description of MonitorizableEventForm.
 *
 * @author ibilbao
 */
class MonitorizableEventForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $readonly = $options['readonly'];
        $builder
        ->add('name', null, [
            'constraints' => [new NotBlank()],
            'label' => 'label.name',
            'translation_domain' => 'messages',
            'disabled' => $readonly,
        ])
        ->add('successCondition', null, [
            'constraints' => [],
            'label' => 'label.successCondition',
            'translation_domain' => 'messages',
            'disabled' => $readonly,
        ])
        ->add('failureCondition', null, [
            'required' => false,
            'label' => 'label.failureCondition',
            'translation_domain' => 'messages',
            'disabled' => $readonly,
        ])
        ->add('filterCondition', null, [
            'required' => false,
            'label' => 'label.filterCondition',
            'translation_domain' => 'messages',
            'disabled' => $readonly,
        ])
        ->add('frecuency', null, [
            'required' => false,
            'attr' => ['min' => 1,
                'max' => 5, ],
            'label' => 'label.frecuency',
            'translation_domain' => 'messages',
            'disabled' => $readonly,
        ])
        ->add('unit', ChoiceType::class, [
            'required' => true,
            'expanded' => false,
            'label' => 'label.unit',
            'choices' => [
                'label.hours' => 1,
                'label.days' => 2,
                'label.weeks' => 3,
                'label.months' => 4,
                'label.years' => 5,
            ],
            'translation_domain' => 'messages',
            'disabled' => $readonly,
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'data_class' => MonitorizableEvent::class,
            'readonly' => false,
    ]);
    }
}
