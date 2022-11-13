<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as ShopAssert;

class CreateProductDto
{
    #[Assert\NotBlank]
    #[ShopAssert\UniqueInDatabase]
    private string $title;
    #[Assert\NotBlank]
    private int $price;
    #[Assert\Currency]
    #[Assert\NotBlank]
    private string $currency;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return CreateProductDto
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return CreateProductDto
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     * @return CreateProductDto
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

}