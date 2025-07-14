<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getAllOrdered(): Collection
    {
        return $this->model->orderedByDate()->get();
    }

    public function findById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function create(array $data): Product
    {
        $data['total_value'] = $data['quantity'] * $data['price'];
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $data['total_value'] = $data['quantity'] * $data['price'];
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function getTotalSum(): float
    {
        return $this->model->sum('total_value');
    }
}
