<?php

namespace App\Http\Controllers;

use App\Actions\Product\ProductAction;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\JsonFileService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductController extends Controller
{

    public function __construct(protected ProductRepositoryInterface $productRepository){}

    public function index(ProductAction $getProductsAction): View
    {
        $data = $getProductsAction->get();

        return view('products.index', $data);
    }

    public function store(StoreProductRequest $request, ProductAction $createProductAction): JsonResponse
    {
        $product = $createProductAction->store($request->validated());

        $data = (new ProductAction($this->productRepository, app(JsonFileService::class)))->get();

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'product' => $product,
            'products' => $data['products'],
            'totalSum' => $data['totalSum']
        ]);
    }

    public function update(UpdateProductRequest $request, ProductAction $ProductAction, Product $product,): JsonResponse
    {
        $updatedProduct = $ProductAction->update($product, $request->validated());

        $data = (new ProductAction($this->productRepository, app(JsonFileService::class)))->get();

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $updatedProduct,
            'products' => $data['products'],
            'totalSum' => $data['totalSum']
        ]);
    }

    public function destroy(Product $product, ProductAction $deleteProductAction): JsonResponse
    {
        $deleteProductAction->delete($product);

        $data = (new ProductAction($this->productRepository, app(JsonFileService::class)))->get();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
            'products' => $data['products'],
            'totalSum' => $data['totalSum']
        ]);
    }
}
