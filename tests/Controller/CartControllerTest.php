<?php

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CartControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->cartRepository = $this->client->getContainer()->get(CartRepository::class);
        $this->cartItemRepository = $this->client->getContainer()->get(CartItemRepository::class);
        $this->productRepository = $this->client->getContainer()->get(ProductRepository::class);
    }

    public function testCreateCart(): void
    {
        $crawler = $this->client->request(
            'POST',
            '/carts',
            [],
            [],
            [],
        );

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $url = $response->headers->get('Location');
        //dump($data);
        $this->assertNotNull($url);
        $this->assertStringStartsWith("/carts/", $url);
    }

    public function testGetAllProducts(): void
    {
        $cart = $this->makeBigCart();
        $this->cartRepository->save($cart, true);
        $crawler = $this->client->request('GET', '/carts/'.$cart->getId().'/cart_items');

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $data = $response->getContent();
        $deserialize = json_decode($data, true);
        $this->assertEquals(1894, $deserialize['total']);
        $this->assertEquals(3, count($deserialize['cartItems']));
        $this->assertStringContainsString("Fallout", $data);
    }

    public function testAddCartItem()
    {
        $cart = new Cart();
        $cart->setUserId(1)
        ->setTotal(0);
        $this->cartRepository->save($cart, true);
        $cartItems[0]['product'] = 3;
        $cartItems[0]['quantity'] = 1;
        $crawler = $this->client->request(
            'POST',
            '/carts/'.$cart->getId().'/cart_items',
            [],
            [],
            [],
            json_encode(['cartItems' => $cartItems])
        );

        $this->assertResponseIsSuccessful();
    }

    public function testAddCartItemTooMany()
    {
        $cart = $this->makeBigCart();
        $cartItems[0]['product'] = 3;
        $cartItems[0]['quantity'] = 1;
        $crawler = $this->client->request(
            'POST',
            '/carts/'.$cart->getId().'/cart_items',
            [],
            [],
            [],
            json_encode(['cartItems' => $cartItems])
        );

        $this->assertResponseStatusCodeSame(500);
    }

    public function testAddCartItemTooMuchQuantity()
    {
        $cart = $this->makeBigCart2();
        $cartItems[0]['product'] = 3;
        $cartItems[0]['quantity'] = 3;
        $crawler = $this->client->request(
            'POST',
            '/carts/'.$cart->getId().'/cart_items',
            [],
            [],
            [],
            json_encode(['cartItems' => $cartItems])
        );

        $this->assertResponseStatusCodeSame(500);
    }

    /**
     * @return Cart
     */
    private function makeCart(): Cart
    {
        $product = $this->productRepository->find(1);
        $cart = new Cart();
        $cart->setUserId(1);
        $cartItem = new CartItem();
        $cartItem->setQuantity(2)
            ->setProduct($product);
        $cart->addCartItem($cartItem);
        $this->cartRepository->save($cart, true);
        return $cart;
    }

    /**
     * @return Cart
     */
    private function makeBigCart(): Cart
    {
        $product = $this->productRepository->find(1);
        $cart = new Cart();
        $cart->setUserId(1);
        $cartItem = new CartItem();
        $cartItem->setQuantity(2)
            ->setProduct($product);
        $cart->addCartItem($cartItem);
        $product = $this->productRepository->find(2);
        $cartItem = new CartItem();
        $cartItem->setQuantity(1)
            ->setProduct($product);
        $cart->addCartItem($cartItem);
        $product = $this->productRepository->find(3);
        $cartItem = new CartItem();
        $cartItem->setQuantity(3)
            ->setProduct($product);
        $cart->addCartItem($cartItem);
        $this->cartRepository->save($cart, true);
        return $cart;
    }

    /**
     * @return Cart
     */
    private function makeBigCart2(): Cart
    {
        $product = $this->productRepository->find(1);
        $cart = new Cart();
        $cart->setUserId(1);
        $cartItem = new CartItem();
        $cartItem->setQuantity(2)
            ->setProduct($product);
        $cart->addCartItem($cartItem);
        $product = $this->productRepository->find(3);
        $cartItem = new CartItem();
        $cartItem->setQuantity(10)
            ->setProduct($product);
        $cart->addCartItem($cartItem);
        $product = $this->productRepository->find(3);
        $this->cartRepository->save($cart, true);
        return $cart;
    }
}
