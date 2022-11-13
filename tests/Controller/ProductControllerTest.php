<?php

namespace App\Tests\Controller;

use App\DTO\CreateProductDto;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProductControllerTest extends WebTestCase
{
    public function testGetAllProducts(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/products');

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();
        $data = $response->getContent();
        //dump($data);
        $this->assertStringContainsString("Fallout", $data);
    }

    public function testCreateProduct(): void
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $client = static::createClient();
        $productDto = new CreateProductDto();
        $productDto->setPrice(122)
            ->setTitle('test test')
            ->setCurrency('USD');
        $crawler = $client->request(
            'POST',
            '/products',
            [],
            [],
            [],
            $serializer->serialize($productDto, 'json', [])
        );

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();
        $url = $response->headers->get('Location');
        //dump($data);
        $this->assertNotNull($url);
        $this->assertStringStartsWith("/products/", $url);
    }

    public function testCreateProductSameName(): void
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $client = static::createClient();
        $productDto = new CreateProductDto();
        $productDto->setPrice(122)
            ->setTitle('test test')
            ->setCurrency('USD');
        $crawler = $client->request(
            'POST',
            '/products',
            [],
            [],
            [],
            $serializer->serialize($productDto, 'json', [])
        );

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(400);
        $data = $response->getContent();
        $this->assertStringContainsString("is not unique.", $data);
    }

    public function testUpdateNotExistingProduct(): void
    {
        $client = static::createClient();
        $id = 767;
        $crawler = $client->request('PUT', '/products/' . $id);

        //
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(404);
        $data = $response->getContent();
        $this->assertStringContainsString("Product #" . $id . " was not found", $data);
    }
}
