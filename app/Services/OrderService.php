<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function getAll()
    {
        return Order::with('orderItems')->get();
    }

    public function getById($id)
    {
        return Order::with('orderItems')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'order_number' => $data['order_number'],
                'total_amount' => $data['total_amount'],
                'status' => $data['status']
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price
                ]);
            }

            return $order;
        });
    }

    public function update($id, array $data)
    {
        $order = $this->getById($id);
        $order->update($data);
        return $order;
    }

    public function delete($id)
    {
        return Order::destroy($id);
    }

    public function getOrdersReport()
    {
        return Order::with(['orderItems.product'])
            ->withCount('orderItems')
            ->get();
    }
} 