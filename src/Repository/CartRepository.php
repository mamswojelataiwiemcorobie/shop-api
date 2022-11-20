<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\CartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validation;

/**
 * @extends ServiceEntityRepository<Cart>
 *
 * @method Cart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cart[]    findAll()
 * @method Cart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function save(Cart $entity, bool $flush = false): void
    {
        foreach ($entity->getCartItems() as $cartItem) {
            $cartItem->setCart($entity);
            $this->getEntityManager()->persist($cartItem);
        }
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllWithProducts($userId)
    {
        $list = $this->createQueryBuilder('c')
            ->leftJoin('c.cartItems', 'cart_item')
            ->andWhere('c.userId = :val')
            ->setParameter('val', $userId)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function addCartItem(Cart $cart, CartItem $cartItemNew)
    {
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProduct() === $cartItemNew->getProduct()) {
                $quantity = $cartItem->getQuantity() + $cartItemNew->getQuantity();
                $cartItem->setQuantity($quantity);

                $validator = Validation::createValidatorBuilder()
                    ->enableAnnotationMapping()
                    ->getValidator();
                $violations = $validator->validate($cartItem);
                if ($violations->count() > 0) {
                    $messages = [];
                    foreach ($violations as $violation) {
                        $messages[$violation->getPropertyPath()][] = $violation->getMessage();
                    }
                    return ['errors' => true, 'messages' => $messages];
                }

                $this->getEntityManager()->persist($cartItem);
                $this->getEntityManager()->flush();
                return $cartItem;
            }
        }
        $cart->addCartItem($cartItemNew);
        $this->save($cart, true);
    }

    public function removeCartItem(Cart $cart, CartItem $cartItemNew)
    {
        foreach ($cart->getCartItems() as $cartItem) {
            if ($cartItem->getProduct() === $cartItemNew->getProduct()) {
                $quantity = $cartItem->getQuantity() - $cartItemNew->getQuantity();
                if ($quantity <= 0) {
                    break;
                }
                $cartItem->setQuantity($quantity);
                $this->getEntityManager()->persist($cartItem);
                $this->getEntityManager()->flush();
                return true;
            }
        }
        $cart->removeCartItem($cartItemNew);
        $this->save($cart, true);
    }
}
