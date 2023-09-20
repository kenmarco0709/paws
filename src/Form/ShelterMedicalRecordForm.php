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
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\Form\DataTransformer\DataTransformer;
use App\Form\DataTransformer\DatetimeTransformer;

use App\Entity\ShelterAdmissionEntity;
use App\Entity\AdmissionTypeEntity;
use App\Entity\UserEntity;


class ShelterMedicalRecordForm extends AbstractType
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
            ->add('attending_vet', HiddenType::class)
            ->add('weight', TextType::class, array(
                'label' => 'Weight',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('temperature', TextType::class, array(
                'label' => 'Temperature',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('vaccine_lot_no', TextType::class, array(
                'label' => 'Lot #',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' =>  false
            ))
            ->add('vaccine_batch_no', TextType::class, array(
                'label' => 'Batch #',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label col-3'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' =>  false
            ))
            ->add('primary_complain', TextareaType::class, array(
                'label' => 'Primary Complain / History',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('medical_interpretation', TextareaType::class, array(
                'label' => 'Medical Interpretations',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('diagnosis', TextareaType::class, array(
                'label' => 'Diagnosis',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('vaccine_due_date', TextType::class, array(
                'label' => 'Due Date',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control datepicker '],
                'required' =>   false
            ))
            ->add('vaccine_expiration_date', TextType::class, array(
                'label' => 'Expiration Date',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control datepicker '],
                'required' =>   false
            ))
            ->add('remarks', TextareaType::class, array(
                'label' => 'Remarks',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('shelter_admission', HiddenType::class, array('data' => $options['admissionId']))
            ->add('admission_type', HiddenType::class, array('data' => $options['admissionType']))
  
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $attendingVet = $data->getAttendingVet();

                $form
                    ->add('attending_vet_desc', TextType::class, array(
                        'label' => 'Attending Vet.',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $attendingVet ? $attendingVet->getFullName() : ''
                    ));
            });

    
            $builder->get('shelter_admission')->addModelTransformer(new DataTransformer($this->manager, ShelterAdmissionEntity::class, true, $options['admissionId']));
            $builder->get('admission_type')->addModelTransformer(new DataTransformer($this->manager, AdmissionTypeEntity::class, true, $options['admissionType']));
            $builder->get('attending_vet')->addModelTransformer(new DataTransformer($this->manager, UserEntity::class, false));
            $builder->get('vaccine_due_date')->addModelTransformer(new DatetimeTransformer());
            $builder->get('vaccine_expiration_date')->addModelTransformer(new DatetimeTransformer());

    }

    public function getName()
    {
        return 'shelterMedicalRecord';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_extra_fields ' => true,
            'data_class' 	  => 'App\Entity\MedicalRecordEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'petEntity_intention',
            'action'          => 'n',
            'admissionId'    => null,
            'admissionType' => null 
        ));
    }
}