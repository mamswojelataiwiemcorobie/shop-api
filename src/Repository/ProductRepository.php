<?php

namespace App\Repository;

use App\Entity\Product;
use App\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function saveProduct($title, $price, $currency): Product
    {
        $product = new Product();

        $product
            ->setTitle($title)
            ->setPrice($price)
            ->setCurrency($currency);

        $this->save($product, true);

        return $product;
    }

    public function findAllSerialized()
    {
        $productsList = [];
        $products = $this->findAll();
        foreach ($products as $product) {
            $productsList[] = $product->asArray();
        }
        return $productsList;
    }

    public function findPaginated(int $page = 1): array
    {
        $pageSize = 3;
        $productList = [];
        $query = $this->createQueryBuilder("p")
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        $paginator = new Paginator($query);
        $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($page - 1)) // set the offset
            ->setMaxResults($pageSize); // set the limit
        foreach ($paginator as $product) {
            $productList[] = $product->asArray();
        }
        return $productList;
    }
}
