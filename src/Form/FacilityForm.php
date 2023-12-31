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

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use App\Form\DataTransformer\DataTransformer;
use App\Entity\BranchEntity;
use App\Entity\SpeciesEntity;


class FacilityForm extends AbstractType
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
            ->add('code', TextType::class, array(
                'label' => 'Code',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))
            ->add('description', TextType::class, array(
                'label' => 'Description',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => true
            ))
            ->add('capacity', TextType::class, array(
                'label' => 'Capacity',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control number'
                ),
                'required' => true
            ))
            ->add('species', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                $species = $data->getSpecies();

                $form
                 
                    ->add('species_desc', TextType::class, array(
                        'label' => 'Species',
                        'label_attr' => array(
                            'class' => 'middle required  col-form-label col-3'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $species ? $species->getDescription() : ''
                    ));
                 
                  
            })
            ->add('branch', HiddenType::class, array('data' => $options['branchId']));
            $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, true, $options['branchId']));
            $builder->get('species')->addModelTransformer(new DataTransformer($this->manager, SpeciesEntity::class, false));



    }

    public function getName()
    {
        return 'facility';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\FacilityEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'facilityEntity_intention',
            'action'          => 'n',
            'branchId'        => null
        ));
    }
}