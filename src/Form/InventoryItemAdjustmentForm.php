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
use App\Entity\InventoryItemEntity;



class InventoryItemAdjustmentForm extends AbstractType
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

            ->add('adjustment_type',ChoiceType::class,
                array('choices' => array(
                        'Others' => 'Others',
                        'Add' => 'Add',
                        'Deduct' => 'Deduct'
                    ),
                'multiple'=> false,
                'expanded'=> true,
                'data' => 'Others'
            ))
            ->add('quantity', TextType::class, array(
                'label' => 'Quantity',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt'],
                'required' => false,
            ))
            ->add('low_quantity', TextType::class, array(
                'label' => 'Low Quantity',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt'],
                'required' => false,
            ))
            ->add('selling_price', TextType::class, array(
                'label' => 'Selling Price',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt'],
                'required' => false,
            ))
            ->add('buying_price', TextType::class, array(
                'label' => 'Buying Price',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control amt'],
                'required' => false,
            ))
            ->add('remarks', TextAreaType::class, array(
                'label' => 'Remarks',
                'label_attr' => array(
                    'class' => 'middle required'
                ),
                'attr' => [ 'class' => 'form-control'],
                'required' => false,
            ))
            ->add('inventory_item', HiddenType::class, array('data' => $options['inventoryItemId']));
            $builder->get('inventory_item')->addModelTransformer(new DataTransformer($this->manager, InventoryItemEntity::class, true,$options['inventoryItemId']));


    }

    public function getName()
    {
        return 'inventoryItemAdjustment';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 	  => 'App\Entity\InventoryItemAdjustmentEntity',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'petEntity_intention',
            'action'          => 'n',
            'inventoryItemId'    => null 
        ));
    }
}