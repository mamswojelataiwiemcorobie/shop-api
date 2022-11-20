<?php

namespace App\EventListener;

use App\Entity\CartItem;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CartItemsListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this listener only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof CartItem) {
            return;
        }

        $entityManager = $args->getObjectManager();

        $total = $entity->getCart()->calculateTotal();
        // ... do something with the Product entity
    }
}