<?php

namespace Spatie\PartialCache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Factory as CacheManager;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\View\Factory as View;

class PartialCache
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;
    
    /**
     * @var \Illuminate\Contracts\Cache\Factory
     */
    protected $cacheManager;

    /**
     * @var string
     */
    protected $cacheKey;
    
    /**
     * @param  \Illuminate\Contracts\View\Factory $view
     * @param  \Illuminate\Contracts\Cache\Repository $cache
     * @param  \Illuminate\Contracts\Cache\Factory $cacheManager
     * @param  \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(View $view, Cache $cache, CacheManager $cacheManager, Config $config)
    {
        $this->view         = $view;
        $this->cache        = $cache;
        $this->cacheManager = $cacheManager;

        $this->cacheKey     = $config->get('partialcache.key');
    }

    /**
     * Cache a view. If minutes are null, the view is cached forever.
     * 
     * @param  array $data
     * @param  string $view
     * @param  array $mergeData
     * @param  string $key
     * @param  int $minutes
     * 
     * @return string
     */
    public function cache($data, $view, $mergeData = [], $key = null, $minutes = null)
    {
        $key = $this->getKey($view, $key);

        $taggable = is_a($this->cacheManager->driver()->getStore(), TaggableStore::class);

        if ($taggable && $minutes === null) {
            return $this->cache
                ->tags($this->cacheKey)
                ->rememberForever($key, $this->renderView($view, $data, $mergeData));
        }

        if ($taggable) {
            return $this->cache
                ->tags($this->cacheKey)
                ->remember($key, $minutes, $this->renderView($view, $data, $mergeData));
        }

        if ($minutes === null) {
            return $this->cache
                ->rememberForever($key, $this->renderView($view, $data, $mergeData));
        }

        return $this->cache
            ->remember($key, $minutes, $this->renderView($view, $data, $mergeData));
    }

    /**
     * Create a key name for the cached view.
     * 
     * @param  string $view
     * @param  string $key
     * 
     * @return string
     */
    protected function getKey($view, $key)
    {
        $parts = [$this->cacheKey, $view];

        if ($key !== null) {
            $parts[] = $key;
        }

        return implode('.', $parts);
    }

    /**
     * Render a view.
     * 
     * @param  string $view
     * @param  array $data
     * @param  array $mergeData
     * 
     * @return string
     */
    protected function renderView($view, $data, $mergeData)
    {
        return function () use ($view, $data, $mergeData) {
            return $this->view->make($view, $data, $mergeData)->render();
        };
    }
}
