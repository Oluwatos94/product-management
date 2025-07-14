<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\JsonFileService;

class ProductAction
{

    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected JsonFileService $jsonFileService
    ) {}

    public function store(array $data): Product
    {
        $product = $this->productRepository->create($data);

        $this->jsonFileService->saveProduct($product);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $updatedProduct = $this->productRepository->update($product, $data);

        $this->jsonFileService->updateJsonFile();

        return $updatedProduct;
    }

    public function get(): array
    {
        $products = $this->productRepository->getAllOrdered();
        $totalSum = $this->productRepository->getTotalSum();

        return [
            'products' => $products,
            'totalSum' => $totalSum
        ];
    }

    public function delete(Product $product): bool
    {
        $result = $this->productRepository->delete($product);

        if ($result) {
            $this->jsonFileService->updateJsonFile();
        }

        return $result;
    }
}
