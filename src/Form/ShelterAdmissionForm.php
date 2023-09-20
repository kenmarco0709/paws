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
use App\Form\DataTransformer\DatetimeTransformer;


use App\Entity\ClientEntity;
use App\Entity\BranchEntity;
use App\Entity\PetEntity;
use App\Entity\UserEntity;
use App\Entity\ShelterAdmissionEntity;
use App\Entity\FacilityEntity;

class ShelterAdmissionForm extends AbstractType
{
   
    private $manager;
    private $admissionType;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->admissionType =  $options['admissionType'];
        $builder
            ->add('action', HiddenType::class, array(
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('pet', HiddenType::class)
            ->add('facility', HiddenType::class)
            ->add('branch', HiddenType::class, array('data' => $options['branchId']))
            ->add('admission_type', HiddenType::class, array('data' =>  $this->admissionType))
            ->add('rescuer_name', TextType::class, array(
                'label' => 'Rescuer Name',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' =>  $this->admissionType == 0 ?  true : false
            ))
            ->add('rescue_place', TextType::class, array(
                'label' => 'Rescue Place',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' =>  $this->admissionType == 0 ?  true : false
            ))
    
            ->add('rescue_story', TextareaType::class, array(
                'label' => 'Rescue Story',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' =>  $this->admissionType == 0 ?  true : false
            ))
            ->add('rescuer_contact', TextType::class, array(
                'label' => 'Rescuer Contact #',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' =>  $this->admissionType == 0 ?  true : false
            ))
            ->add('admission_date', TextType::class, array(
                'label' => 'Admission Date',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control datepicker ', 'required' => 'required'],
                'required' =>  $this->admissionType == 0 ?  true : false
            ))
            ->add('rescue_date', TextType::class, array(
                'label' => 'Rescue Date',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control datepicker ', 'required' => 'required'],
                'required' =>  $this->admissionType == 0 ?  true : false
            ))
            ->add('returned_reason', TextType::class, array(
                'label' => 'Reason for Return',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' =>  $this->admissionType == 1 ?  true : false
            ))
            ->add('returned_date', TextType::class, array(
                'label' => 'Returned Date',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control datepicker ', 'required' => 'required'],
                'required' =>  $this->admissionType == 1 ?  true : false
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event, $options) {
               
                $form = $event->getForm();
                $data = $event->getData();

                $pet = $data->getPet();
                $facility = $data->getFacility();


                $form
                    ->add('pet_desc', TextType::class, array(
                        'label' => 'Pet',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control' ,
                            'readonly' => $this->admissionType == 0 ?  true : false

                        ),
                        'mapped' => false,
                        'data' => $pet ? $pet->getName() : ''
                    ))
                    ->add('facility_desc', TextType::class, array(
                        'label' => 'Facility',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control' 

                        ),
                        'mapped' => false,
                        'data' => $facility ? $facility->getDescription() : ''
                    ));
                 
            });

           $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, false, $options['branchId']));
           $builder->get('pet')->addModelTransformer(new DataTransformer($this->manager, PetEntity::class, false));
           $builder->get('facility')->addModelTransformer(new DataTransformer($this->manager, FacilityEntity::class, false));
           $builder->get('admission_date')->addModelTransformer(new DatetimeTransformer());
           $builder->get('returned_date')->addModelTransformer(new DatetimeTransformer());
           $builder->get('rescue_date')->addModelTransformer(new DatetimeTransformer());




    }

    public function getName()
    {
        return 'shelterAdmission';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\ShelterAdmissionEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'admissionEntity_intention',
            'action'          => 'n',
            'branchId'    => null,
            'admissionType' => null 
        ));
    }
}