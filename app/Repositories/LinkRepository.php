<?php

namespace App\Repositories;

use App\Models\Link;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LinkRepository
{
    protected Link $model;
    
    public function __construct(Link $model)
    {
        $this->model = $model;
    }
    
    public function create(array $data): Link
    {
        return $this->model->create($data);
    }
    
    public function findByShortCode(string $shortCode): ?Link
    {
        return $this->model->where('short_code', $shortCode)
                          ->where('is_active', true)
                          ->first();
    }
    
    public function existsByShortCode(string $shortCode): bool
    {
        return $this->model->where('short_code', $shortCode)->exists();
    }
    
    public function getMostClicked(int $limit = 10): Collection
    {
        return $this->model->active()
                          ->orderBy('clicks', 'desc')
                          ->limit($limit)
                          ->get();
    }
    
    public function getLinksByUser(int $userId, int $perPage = 15)
    {
        return $this->model->forUser($userId)
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage);
    }
    
    public function incrementClicks(Link $link): void
    {
        $link->increment('clicks');
        Cache::forget("link:{$link->short_code}");
    }
}