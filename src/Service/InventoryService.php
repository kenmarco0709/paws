<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\InventoryItemEntity;

Class InventoryService {

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container) {

        $this->em = $em;
        $this->container = $container;
    }

    public function processInventory($class){

        $inventoryItem = $this->em->getRepository(InventoryItemEntity::class)->find($class->getInventoryItem()->getId());
        
        $quantity = $inventoryItem->getQuantity();

        if(in_array(get_class($class), ['App\Entity\InvoiceVoidInventoryItemEntity', 'App\Entity\InventoryItemCompletedOrderEntity'])){
            
            $quantity += $class->getQuantity(); 

            if(method_exists($class, 'getBuyingPrice')){
                $inventoryItem->setBuyingPrice($class->getBuyingPrice());
            }  

        } else {
           
            if(method_exists($class, 'getAdjustmentType')){
             
                if($class->getSellingPrice()){
                    $inventoryItem->setSellingPrice($class->getSellingPrice());
                }

                if($class->getBuyingPrice()){
                    $inventoryItem->setBuyingPrice($class->getBuyingPrice());
                }

                if($class->getLowQuantity()){
                    $inventoryItem->setLowQuantity($class->getLowQuantity());
                }


                if($class->getQuantity()){
                    
                    if($class->getAdjustmentType() == 'Add'){
                    
                        $quantity += $class->getQuantity(); 
                    } else {
        
                        $quantity -= $class->getQuantity(); 
                    }
                }
   
           } else {
   
               $quantity -= $class->getQuantity(); 

           }
        }
        
        $inventoryItem->setQuantity($quantity);
        $this->em->flush();

    }




}