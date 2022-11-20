<?php

namespace App\Tests\Controller;

use App\DTO\CreateProductDto;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProductControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->productRepository = $this->client->getContainer()->get(ProductRepository::class);
    }

    public function testGetAllProducts(): void
    {
        $crawler = $this->client->request('GET', '/products');

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $data = $response->getContent();
        //dump($data);
        $this->assertStringContainsString("Fallout", $data);
    }

    public function testCreateProduct(): void
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $productDto = new CreateProductDto();
        $productDto->setPrice(122)
            ->setTitle('test')
            ->setCurrency('USD');
        $crawler = $this->client->request(
            'POST',
            '/products',
            [],
            [],
            [],
            $serializer->serialize($productDto, 'json', [])
        );

        $this->assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        $url = $response->headers->get('Location');
        $this->assertNotNull($url);
        $this->assertStringStartsWith("/products/", $url);
    }

    public function testCreateProductSameName(): void
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $productDto = new CreateProductDto();
        $productDto->setPrice(122)
            ->setTitle('Fallout')
            ->setCurrency('USD');
        $crawler = $this->client->request(
            'POST',
            '/products',
            [],
            [],
            [],
            $serializer->serialize($productDto, 'json', [])
        );

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(400);
        $data = $response->getContent();
        $this->assertStringContainsString("is not unique.", $data);
    }

    public function testUpdateNotExistingProduct(): void
    {
        $id = 767;
        $crawler = $this->client->request('PUT', '/products/' . $id);
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(404);
        $data = $response->getContent();
        $this->assertStringContainsString("Product #" . $id . " was not found", $data);
    }
}
