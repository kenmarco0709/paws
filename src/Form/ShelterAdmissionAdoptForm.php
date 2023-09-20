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

class ShelterAdmissionAdoptForm extends AbstractType
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
            ->add('adopter_name', TextType::class, array(
                'label' => 'Adopter Name',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' =>   true 
            ))
            ->add('adopter_contact', TextType::class, array(
                'label' => 'Adopter Contact Number',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' =>   false
            ))
            ->add('adopter_address', TextType::class, array(
                'label' => 'Adopter Address',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' =>   false
            ))
            ->add('adopter_email_address', TextType::class, array(
                'label' => 'Adopter Email Address',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' =>   false
            ))
            ->add('adoption_date', TextType::class, array(
                'label' => 'Adoption Date',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control datepicker ', 'required' => 'required'],
                'required' =>  true
            ))
            ->add('remarks', TextareaType::class, array(
                'label' => 'Remarks',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' =>   false
            ));

           $builder->get('adoption_date')->addModelTransformer(new DatetimeTransformer());



    }

    public function getName()
    {
        return 'shelterAdmissionAdopt';
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
        ));
    }
}