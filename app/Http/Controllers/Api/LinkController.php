<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LinkResource;
use App\Services\LinkService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LinkController extends Controller
{
    protected LinkService $linkService;
    
    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }
    
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url|max:2048',
            'expires_in' => 'nullable|integer|min:1|max:30', // days
            'custom_code' => 'nullable|string|alpha_num|min:4|max:20'
        ]);
        
        try {
            $link = $this->linkService->createApiLink($validated);
            
            return response()->json([
                'success' => true,
                'data' => new LinkResource($link)
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}