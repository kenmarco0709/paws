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
use App\Entity\ItemEntity;



class InventoryItemForm extends AbstractType
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
            ->add('beginning_quantity', TextType::class, array(
                'label' => 'Beginning Quantity',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt', 'required' => 'required'],
                'required' => true,
            ))
            ->add('low_quantity', TextType::class, array(
                'label' => 'Low Quantity',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt', 'required' => 'required'],
                'required' => true,
            ))
            ->add('selling_price', TextType::class, array(
                'label' => 'Selling Price',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt', 'required' => 'required'],
                'required' => true,
            ))
            ->add('buying_price', TextType::class, array(
                'label' => 'Buying Price',
                'label_attr' => array(
                    'class' => 'middle'
                ),
                'attr' => [ 'class' => 'form-control amt'],
                'required' => false,
            ))
            ->add('item', HiddenType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();
                $item = $data->getItem();
                $form
                    ->add('item_desc', TextType::class, array(
                        'label' => 'Item',
                        'label_attr' => array(
                            'class' => 'middle required'
                        ),
                        'required' => true,
                        'attr' => array(                            
                            'class' => 'form-control',
                        ),
                        'mapped' => false,
                        'data' => $item ? $item->getDescription() : ''
                    ));

            })

            
            ->add('branch', HiddenType::class, array('data' => $options['branchId']));
            $builder->get('branch')->addModelTransformer(new DataTransformer($this->manager, BranchEntity::class, true, $options['branchId']));
            $builder->get('item')->addModelTransformer(new DataTransformer($this->manager, ItemEntity::class, false));
    }

    public function getName()
    {
        return 'inventoryItem';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\InventoryItemEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'petEntity_intention',
            'action'          => 'n',
            'branchId'    => null 
        ));
    }
}