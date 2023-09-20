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

use App\Form\AdmissionPetForm;



class AdmissionForm extends AbstractType
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
            ->add('attending_vet', HiddenType::class)
            ->add('branch', HiddenType::class, array('data' => $options['branchId']))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event, $options) {

                $form = $event->getForm();
                $data = $event->getData();
                $client = $data->getClient();
                $admisstionType = $data->getAdmissionType();
                $attendingVet = $data->getAttendingVet();

                $form
                    ->add('attending_vet_desc', TextType::class, array(
                        'label' => '',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $attendingVet ? $attendingVet->getFullName() : ''
                    ))
                    ->add('client_desc', TextType::class, array(
                        'label' => 'Client Name',
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
                    ->add('admission_type_desc', TextType::class, array(
                        'label' => 'Client Type',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $admisstionType ? $admisstionType->getDescription() : ''
                    ));

                    if(is_null($client)){
                        $form->add('admission_pets', CollectionType::class, [
                            'entry_type' => AdmissionPetForm::class,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'by_reference' => false, //this is so much importance to tag all child entity to this parent entity
                            'entry_options' => array(
                                'admissionId' => $data->getId() ? $data->getId() : null,
                            )
                        ]);
                    }
                 
            });

           $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, false, $options['branchId']));
           $builder->get('client')->addModelTransformer(new DataTransformer($this->manager, ClientEntity::class, false));
           $builder->get('admission_type')->addModelTransformer(new DataTransformer($this->manager, AdmissionTypeEntity::class, false));
           $builder->get('attending_vet')->addModelTransformer(new DataTransformer($this->manager, UserEntity::class, false));

    }

    public function getName()
    {
        return 'admission';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\AdmissionEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'admissionEntity_intention',
            'action'          => 'n',
            'branchId'    => null 
        ));
    }
}