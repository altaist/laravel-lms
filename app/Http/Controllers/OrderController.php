<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Http\Requests\OrderRequest;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        return response()->json($this->orderService->getAll());
    }

    public function show($id)
    {
        return response()->json($this->orderService->getById($id));
    }

    public function store(OrderRequest $request)
    {
        return response()->json($this->orderService->create($request->validated()));
    }

    public function update(OrderRequest $request, $id)
    {
        return response()->json($this->orderService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json($this->orderService->delete($id));
    }

    public function report()
    {
        return response()->json($this->orderService->getOrdersReport());
    }
} 