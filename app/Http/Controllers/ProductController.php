<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        return response()->json($this->productService->getAll());
    }

    public function show($id)
    {
        return response()->json($this->productService->getById($id));
    }

    public function store(ProductRequest $request)
    {
        return response()->json($this->productService->create($request->validated()));
    }

    public function update(ProductRequest $request, $id)
    {
        return response()->json($this->productService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json($this->productService->delete($id));
    }

    public function report()
    {
        return response()->json($this->productService->getProductsReport());
    }
} 