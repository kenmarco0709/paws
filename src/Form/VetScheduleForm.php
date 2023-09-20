<?php

namespace App\Form;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\Form\DataTransformer\DataTransformer;
use App\Entity\UserEntity;
use App\Entity\BranchEntity;




class VetScheduleForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $scheduleTypes = array();

        foreach($options['scheduleTypes'] as $row) {
                $scheduleTypes[$row] = $row;
        }

        $builder
            ->add('action', HiddenType::class, array(
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('branch', HiddenType::class, array('data' => $options['branchId']))
            ->add('vet', HiddenType::class)
            ->add('schedule_type', ChoiceType::class, array(
                'label' => 'Type',
                'label_attr' => array(
                    'class' => 'required middle'
                ),
                'required' => true,
                'choices' => $scheduleTypes,
                'attr' => [ 'class' => 'form-control', 'required' => 'required']

            ))
            
            ->add('schedule_time_from', TextType::class, array(
                'label' => 'Schedule Time From',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control timepicker-from'],
                'required' => false,
                'mapped' => true
            ))
            ->add('schedule_time_to', TextType::class, array(
                'label' => 'Schedule Time To',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control timepicker-to'],
                'required' => false,
                'mapped' => true
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $vet = $data->getVet();

                $form
                    ->add('schedule_date_from', TextType::class, array(
                        'label' => 'Schedule Date From',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'attr' => [ 'class' => 'form-control datepicker', 'required' => 'required'],
                        'required' => true,
                        'mapped' => false,
                        'data' => $data->getScheduleDateFrom() ? date_format($data->getScheduleDateFrom(),"m/d/Y") : ''
                    ))
                    ->add('schedule_date_to', TextType::class, array(
                        'label' => 'Schedule Date To',
                        'label_attr' => array(
                            'class' => 'middle'
                        ),
                        'attr' => [ 'class' => 'form-control datepicker'],
                        'required' => false,
                        'mapped' => false,
                        'data' => $data->getScheduleDateTo() ? date_format($data->getScheduleDateTo(),"m/d/Y") : ''
                    ))
                    ->add('vet_desc', TextType::class, array(
                        'label' => 'Vet.',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $vet ? $vet->getFullName() : ''
                    ));
        
            });

           $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, false, $options['branchId']));
           $builder->get('vet')->addModelTransformer(new DataTransformer($this->manager, UserEntity::class, false));

    }

    public function getName()
    {
        return 'vetSchedule';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\VetScheduleEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'vetScheduleEntity_intention',
            'action'          => 'n',
            'branchId'    => null,
            'scheduleTypes' => [] 
        ));
    }
}