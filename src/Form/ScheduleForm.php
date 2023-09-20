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

use App\Entity\ClientEntity;
use App\Entity\BranchEntity;
use App\Entity\AdmissionTypeEntity;
use App\Entity\UserEntity;

use App\Form\SchedulePetForm;



class ScheduleForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('action', HiddenType::class, array(
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('admission_type', HiddenType::class)
            ->add('client', HiddenType::class)
            ->add('branch', HiddenType::class, array('data' => $options['branchId']))
            ->add('attending_vet', HiddenType::class)
            ->add('remarks', TextareaType::class, array(
                'label' => 'Remarks',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))
            ->add('status', ChoiceType::class, [
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'choices'  => $options['statuses'],
                'required' => false
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $client = $data->getClient();
                $admisstionType = $data->getAdmissionType();
                $attendingVet = $data->getAttendingVet();

                $form
                    ->add('client_desc', TextType::class, array(
                        'label' => 'Client',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $client ? $client->getFullName() : ''
                    ))
                    ->add('schedule_date', TextType::class, array(
                        'label' => 'Schedule Date',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'attr' => [ 'class' => 'form-control datepicker', 'required' => 'required'],
                        'required' => true,
                    ))
                    ->add('admission_type_desc', TextType::class, array(
                        'label' => 'Schedule Type',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $admisstionType ? $admisstionType->getDescription() : ''
                    ))
                    ->add('schedule_pets', CollectionType::class, [
                        'entry_type' => SchedulePetForm::class,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false, //this is so much importance to tag all child entity to this parent entity
                        'entry_options' => array(
                            'scheduleId' => $data->getId() ? $data->getId() : null  ,
                        )
                    ])
                    ->add('schedule_date', TextType::class, array(
                        'label' => 'Schedule Date',
                        'label_attr' => array(
                            'class' => 'middle required col-form-label col-3'
                        ),
                        'attr' => array('class' => 'form-control datepicker', 'data-toggle' => 'datetimepicker'),
                        'required' => true,
                        'mapped' => false,
                        'data' => $data->getScheduleDate() ? date_format($data->getScheduleDate(),"m/d/Y") : ''
                    ))
                    ->add('attending_vet_desc', TextType::class, array(
                        'label' => 'Attending Vet.',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $attendingVet ? $attendingVet->getFullName() : ''
                    ));
        
            });

           $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, false, $options['branchId']));
           $builder->get('client')->addModelTransformer(new DataTransformer($this->manager, ClientEntity::class, false));
           $builder->get('admission_type')->addModelTransformer(new DataTransformer($this->manager, AdmissionTypeEntity::class, false));
           $builder->get('attending_vet')->addModelTransformer(new DataTransformer($this->manager, UserEntity::class, false));





    }

    public function getName()
    {
        return 'schedule';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ScheduleEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'scheduleEntity_intention',
            'action'          => 'n',
            'branchId'    => null,
            'statuses' => [] 
        ));
    }
}