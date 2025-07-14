<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class JsonFileService
{

    public function __construct(protected ProductRepositoryInterface $productRepository){}

    public function saveProduct(Product $product): void
    {
        $jsonData = $this->getExistingData();

        $jsonData[] = $this->formatProductData($product);

        $this->saveToFile($jsonData);
    }

    public function updateJsonFile(): void
    {
        $products = $this->productRepository->getAllOrdered();
        $jsonData = [];

        foreach ($products as $product) {
            $jsonData[] = $this->formatProductData($product);
        }

        $this->saveToFile($jsonData);
    }

    private function getExistingData(): array
    {
        if (Storage::exists('products.json')) {
            return json_decode(Storage::get('products.json'), true) ?? [];
        }

        return [];
    }

    private function formatProductData(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'quantity' => $product->quantity,
            'price' => $product->price,
            'total_value' => $product->total_value,
            'created_at' => $product->created_at->toISOString(),
            'updated_at' => $product->updated_at->toISOString()
        ];
    }

    private function saveToFile(array $data): void
    {
        Storage::put('products.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}
