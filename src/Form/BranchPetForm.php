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

use App\Entity\BranchEntity;
use App\Entity\BreedEntity;
use App\Entity\SpeciesEntity;
use App\Entity\StageEntity;


class BranchPetForm extends AbstractType
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
            ->add('name', TextType::class, array(
                'label' => 'Name',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control', 'required' => 'required'],
                'required' => true,
            ))
            ->add('approximate_age', TextType::class, array(
                'label' => 'Approximate Age',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('birth_date', TextType::class, array(
                'label' => 'Birth Date',
                'label_attr' => array(
                    'class' => 'middle'
                ),
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => false,
            ))
            ->add('fixed_date', TextType::class, array(
                'label' => 'Fixed Date',
                'label_attr' => array(
                    'class' => 'middle'
                ),
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => false,
            ))
            ->add('death_date', TextType::class, array(
                'label' => 'Death Date',
                'label_attr' => array(
                    'class' => 'middle'
                ),
                'attr' => [ 'class' => 'form-control datepicker'],
                'required' => false,
            ))
            ->add('gender', ChoiceType::class, [
                'label' => 'Sex',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'choices' => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                ],
                'attr' => [ 'class' => 'form-control', 'required' => 'required']           
             ])
            ->add('color_markings', TextareaType::class, array(
                'label' => 'Color/Markings',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))
            ->add('is_fixed', CheckboxType::class, [
                'label'    => 'Fixed',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('is_deceased', CheckboxType::class, [
                'label'    => 'Deceased',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('cause_of_death', TextType::class, [
                'label'    => 'Cause of death',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ])
            ->add('has_cruel_file', CheckboxType::class, [
                'label'    => 'Has Cruelty Case',
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('beforeFile', FileType::class, array(
                'label' => 'Upload Before Photo',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
                'mapped' => false 
            ))
            ->add('afterFile', FileType::class, array(
                'label' => 'Upload After Photo',
                'label_attr' => array(
                    'class' => 'middle required  col-form-label'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
                'mapped' => false 
            ))
            ->add('branch', HiddenType::class, array('data' => $options['branchId']))
            ->add('species', HiddenType::class)
            ->add('breed', HiddenType::class)
            ->add('stage', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $breed = $data->getBreed();
                $species = $data->getSpecies();
                $stage = $data->getStage();


                $form
                    ->add('breed_desc', TextType::class, array(
                        'label' => 'Breed',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'required' => false,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $breed ? $breed->getDescription() : ''
                    ))
                    ->add('species_desc', TextType::class, array(
                        'label' => 'Species',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'required' => false,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $species ? $species->getDescription() : ''
                    ))
                    ->add('stage_desc', TextType::class, array(
                        'label' => 'Status',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'required' => false,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $stage ? $stage->getDescription() : ''
                    ));
            });

            $builder->get('breed')->addModelTransformer(new DataTransformer($this->manager, BreedEntity::class, false));
            $builder->get('species')->addModelTransformer(new DataTransformer($this->manager, SpeciesEntity::class, false));
            $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, true, $options['branchId']));
            $builder->get('birth_date')->addModelTransformer(new DatetimeTransformer());
            $builder->get('fixed_date')->addModelTransformer(new DatetimeTransformer());
            $builder->get('death_date')->addModelTransformer(new DatetimeTransformer());
            $builder->get('stage')->addModelTransformer(new DataTransformer($this->manager, StageEntity::class, false));



    }

    public function getName()
    {
        return 'branchPet';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\PetEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'petEntity_intention',
            'action'          => 'n',
            'branchId'    => null 
        ));
    }
}