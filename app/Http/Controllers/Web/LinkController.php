<?php
// app/Http/Controllers/Web/LinkController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLinkRequest;
use App\Models\Link;
use App\Models\Visit;
use App\Services\LinkService;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LinkController extends Controller
{
    protected LinkService $linkService;
    protected AnalyticsService $analyticsService;
    
    public function __construct(
        LinkService $linkService,
        AnalyticsService $analyticsService
    ) {
        $this->linkService = $linkService;
        $this->analyticsService = $analyticsService;
    }
    
    public function index(Request $request)
    {
        $links = $this->linkService->getUserLinks($request->user()->id);
        return view('links.index', compact('links'));
    }
    
    public function create()
    {
        return view('links.create');
    }
    
    public function store(StoreLinkRequest $request)
    {
        try {
            $link = $this->linkService->createLink(
                $request->validated(),
                $request->user()?->id
            );
            
            return redirect()
                ->route('links.show', $link->short_code)
                ->with('success', '✨ Link created successfully! Your short URL is: ' . url('/' . $link->short_code));
                
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Link creation failed: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again.');
        }
    }
    
    public function show(string $shortCode)
    {
        $link = Link::where('short_code', $shortCode)->firstOrFail();
        
        try {
            $analytics = $this->analyticsService->getLinkAnalytics($link);
        } catch (\Exception $e) {
            $analytics = [
                'total_clicks' => $link->clicks ?? 0,
                'unique_visitors' => 0,
                'last_24_hours' => 0,
                'last_7_days' => 0,
                'top_referers' => collect([]),
                'daily_clicks' => collect([]),
            ];
        }
        
        return view('links.show', compact('link', 'analytics'));
    }
    
    /**
     * Redirect to the original URL.
     * This is the catch-all route handler.
     */
    public function redirect(string $shortCode)
    {
        // Log for debugging
        Log::info('Redirect attempt for: ' . $shortCode);
        
        // Check if it's a reserved word
        $reserved = ['register', 'login', 'logout', 'links', 'admin', 'api', 'dashboard'];
        if (in_array(strtolower($shortCode), $reserved)) {
            Log::warning('Reserved word attempted: ' . $shortCode);
            abort(404);
        }
        
        // Find the link
        $link = Link::where('short_code', $shortCode)
                    ->where('is_active', true)
                    ->first();
        
        // Log the result
        if ($link) {
            Log::info('Link found: ' . $shortCode . ' -> ' . $link->original_url);
        } else {
            Log::warning('Link not found: ' . $shortCode);
            abort(404, 'Link not found.');
        }
        
        // Check if expired
        if ($link->expires_at && $link->expires_at->isPast()) {
            Log::warning('Link expired: ' . $shortCode);
            abort(410, 'This link has expired.');
        }
        
        // Increment clicks
        $link->increment('clicks');
        
        // Track the visit
        try {
            Visit::create([
                'link_id' => $link->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referer' => request()->header('referer'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track visit: ' . $e->getMessage());
            // Continue even if tracking fails
        }
        
        // Redirect to original URL
        return redirect()->away($link->original_url);
    }
}