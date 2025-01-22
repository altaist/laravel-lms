<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseRoutingController;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

abstract class BaseController extends BaseRoutingController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getAuthUser(): User
    {
        return Auth::user();
    }

    public function responseData($data, $status = 200)
    {
        return response($data, status: $status);
    }

    public function inertia(string $componentName, array $data = [])
    {
        return Inertia::render($componentName, $data);
    }
}
