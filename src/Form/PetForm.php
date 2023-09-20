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
use App\Entity\ClientEntity;
use App\Entity\BreedEntity;

class PetForm extends AbstractType
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
        
            ->add('birth_date', TextType::class, array(
                'label' => 'Birth Date',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control datepicker', 'required' => 'required'],
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
            ->add('color_markings', TextType::class, array(
                'label' => 'Color Markings',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => array(
                    'class' => 'form-control '
                ),
                'required' => false
            ))
            ->add('profile_pic', FileType::class, array(
                'label' => 'Upload Profile Picture',
                'label_attr' => array(
                    'class' => 'middle'
                ),
                'attr' => array(
                    'class' => 'form-control ',
                    'accept' => "image/png, image/gif, image/jpeg"
                ),
                'required' => false,
                'mapped' => false
            ))
            ->add('client_id', HiddenType::class, array(
                'data' => $options['clientId'],
                'mapped' => false
            ))
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $client = $data->getClient();
                $breed = $data->getBreed();

                $form
                    ->add('birth_date', TextType::class, array(
                        'label' => 'Birthday',
                        'label_attr' => array(
                            'class' => 'middle'
                        ),
                        'attr' => array('class' => 'form-control datepicker', 'data-toggle' => 'datetimepicker'),
                        'required' => false,
                        'mapped' => false,
                        'data' => $data->getBirthDate() ? date_format($data->getBirthDate(),"m/d/Y") : ''
                    ))
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
                    ));
            })

            ->add('breed', HiddenType::class);
            $builder->get('breed')->addModelTransformer(new DataTransformer($this->manager, BreedEntity::class, false));
            // if($options['clientId'] != null){
            //     $builder->add('client', HiddenType::class, array('data' => $options['clientId'] ));
            //     $builder->get('client')->addModelTransformer(new DataTransformer($this->manager, ClientEntity::class, true, $options['clientId']));

            // } else {
            //     $builder->add('client', HiddenType::class);
            //     $builder->get('client')->addModelTransformer(new DataTransformer($this->manager, ClientEntity::class, false));
            // }xsx
    }

    public function getName()
    {
        return 'pet';
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
            'clientId'    => null 
        ));
    }
}