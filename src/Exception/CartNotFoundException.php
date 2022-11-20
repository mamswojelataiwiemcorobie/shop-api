<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartNotFoundException extends NotFoundHttpException
{
    public function __construct(int $id)
    {
        parent::__construct("Cart #" . $id . " was not found");
    }
}