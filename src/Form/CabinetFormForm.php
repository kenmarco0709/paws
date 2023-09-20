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
use App\Entity\PetEntity;
use App\Entity\CabinetFormEntity;

class CabinetFormForm extends AbstractType
{
   
    private $manager;

    public function __construct(ObjectManager $manager) {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $formTypes = array();

        foreach($options['formTypes'] as $row) {
          
                $formTypes[$row] = $row;
        }

        $builder
            ->add('action', HiddenType::class, array(
                'mapped' => false,
                'attr' => array(
                    'class' => 'form-action'
                )
            ))
            ->add('id', HiddenType::class)
            ->add('form_type', ChoiceType::class, array(
                'label' => 'Type',
                'label_attr' => array(
                    'class' => 'required middle'
                ),
                'required' => true,
                'choices' => $formTypes,
                'attr' => [ 'class' => 'form-control', 'required' => 'required']

            ))
            ->add('cabinet_file', FileType::class, array(
                'label' => 'Cabinet File',
                'label_attr' => array(
                    'class' => 'required middle'
                ),
                'required' => true,
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'mapped' => false

            ));
            $builder->add('pet', HiddenType::class, array('data' => $options['petId'] ));
            $builder->get('pet')->addModelTransformer(new DataTransformer($this->manager, PetEntity::class, true, $options['petId']));
    }

    public function getName()
    {
        return 'cabinetFormForm';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\CabinetFormEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'cabinetFormEntity_intention',
            'action'          => 'n',
            'petId'    => null,
            'formTypes' => [] 
        ));
    }
}