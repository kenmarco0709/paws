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

class SupplierForm extends AbstractType
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
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                 
                  
            })
            ->add('branch', HiddenType::class, array('data' => $options['branchId']));
            $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, true, $options['branchId']));


    }

    public function getName()
    {
        return 'supplier';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\SupplierEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'supplierEntity_intention',
            'action'          => 'n',
            'branchId'        => null
        ));
    }
}