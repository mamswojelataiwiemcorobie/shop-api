<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Exception\CartNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Form\Type\CartItemType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: "/carts", name: "carts_")]
class CartController extends AbstractController
{
    public function __construct(
        private CartRepository $cartRepository,
        private CartRepository $cartItemRepository
    ) {}

    #[Route(path: "", name: "create", methods: ["POST"])]
    public function create(Request $request): Response
    {
        $cart = new Cart();
        $cart->setUserId(1);
        $this->cartRepository->save($cart, true);

        return $this->json([], Response::HTTP_CREATED, ["Location" => "/carts/" . $cart->getId()]);
    }

    #[Route(path: "/{id}/cart_items", name: "getCartItems", methods: ["GET"])]
    function all($id, Request $request): Response
    {
        $cart = $this->cartRepository->find($id);
        if (!$cart) {
            throw new CartNotFoundException($id);
        }
        $cartItemShow = [];
        $cartShow['id'] = $id;
        $cartShow['total'] = $cart->getTotal();
        $data = $cart->getCartItems();
        foreach ($data as $cartItem) {
            $cartItemShow[] = $cartItem->toArray();
        }
        $cartShow['cartItems'] = $cartItemShow;
        return $this->json($cartShow);
    }

    #[Route(path: "/{id}/cart_items", name: "addCartItem", methods: ["POST"])]
    public function addCartItem(Cart $cart, Request $request, ProductRepository $productRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        foreach ($data['cartItems'] as $cartItem) {
            $product = $productRepository->find($cartItem['product']);
            if (!$product) {
                throw new ProductNotFoundException($cartItem['product']);
            }
            break;
        }

        $form = $this->createForm(CartItemType::class, new CartItem());
        $form->submit($cartItem);
        if ($form->isSubmitted() && $form->isValid()) {
            $cartItem = $form->getData();
            if ($cart->getCartItems()->count() < 3) {
                $result = $this->cartRepository->addCartItem($cart, $cartItem);
                if (isset($result['errors'])) {
                    return $this->json(['errors' => $result['messages']], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return $this->json(['errors' => ['To many products in cart']], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return $this->json(['error' => $form->getErrors(false, true)], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([], Response::HTTP_CREATED, ["Location" => "/carts/" . $cart->getId()]);
    }

    #[Route(path:"/{id}/cart_items/{cart_id}", name:"cart_delete", methods:["DELETE"])]
    public function delete($id, int $cart_id, Request $request): JsonResponse
    {
        $cart = $this->cartRepository->find($id);
        if (!$cart) {
            throw new CartNotFoundException($id);
        }
        $cartItem = $this->cartItemRepository->find($cart_id);
        $this->cartRepository->removeCartItem($cart, $cartItem);
        return new JsonResponse('Product deleted', Response::HTTP_NO_CONTENT);
    }
}