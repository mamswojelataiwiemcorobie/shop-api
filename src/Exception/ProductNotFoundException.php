<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductNotFoundException extends NotFoundHttpException
{
    public function __construct(int $id)
    {
        parent::__construct("Product #" . $id . " was not found");
    }
}