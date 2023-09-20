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
use Symfony\Component\Form\Extension\Core\Type\FileType;



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

class PetPhotoForm extends AbstractType
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
            ->add('pet', HiddenType::class, array('data' => $options['petId']))
            ->add('before_photo', FileType::class, array(
                'label' => 'Upload Before Photo',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label '
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' => true,
                'mapped' => false 
            ))
            ->add('after_photo', FileType::class, array(
                'label' => 'Upload After Photo',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' => true,
                'mapped' => false 
            ))
            ->add('remarks', TextareaType::class, array(
                'label' => 'Remarks',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ));

           $builder->get('pet')->addModelTransformer(new DataTransformer($this->manager, PetEntity::class, true, $options['petId']));



    }

    public function getName()
    {
        return 'PetPhoto';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\PetPhotoEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'PetPhotoEntity_intention',
            'action'          => 'n',
            'petId'    => null
        ));
    }
}