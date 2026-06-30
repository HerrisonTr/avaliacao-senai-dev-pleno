<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ServicesController extends Controller
{
    public function index(): JsonResponse
    {
        $services = Service::query()
            ->active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $services,
        ], Response::HTTP_OK);
    }
}
