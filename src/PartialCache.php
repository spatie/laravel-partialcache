<?php

namespace Spatie\PartialCache;

use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Factory as CacheManager;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\View\Factory as View;
use Spatie\PartialCache\Exceptions\MethodNotSupportedException;

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
     * @var bool
     */
    protected $cacheIsTaggable;
    
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

        $this->cacheKey        = $config->get('partialcache.key');
        $this->cacheIsTaggable = is_a($this->cacheManager->driver()->getStore(), TaggableStore::class);
    }

    /**
     * Cache a view. If minutes are null, the view is cached forever.
     * 
     * @param  array $data
     * @param  string $view
     * @param  array $mergeData
     * @param  int $minutes
     * @param  string $key
     * 
     * @return string
     */
    public function cache($data, $view, $mergeData = null, $minutes = null, $key = null)
    {
        $viewKey = $this->getCacheKeyForView($view, $key);

        $mergeData = $mergeData ?: [];

        if ($this->cacheIsTaggable && $minutes === null) {
            return $this->cache
                ->tags($this->cacheKey)
                ->rememberForever($viewKey, $this->renderView($view, $data, $mergeData));
        }

        if ($this->cacheIsTaggable) {
            return $this->cache
                ->tags($this->cacheKey)
                ->remember($viewKey, $minutes, $this->renderView($view, $data, $mergeData));
        }

        if ($minutes === null) {
            return $this->cache
                ->rememberForever($viewKey, $this->renderView($view, $data, $mergeData));
        }

        return $this->cache
            ->remember($viewKey, $minutes, $this->renderView($view, $data, $mergeData));
    }

    /**
     * Create a key name for the cached view.
     * 
     * @param  string $view
     * @param  string $key
     * 
     * @return string
     */
    public function getCacheKeyForView($view, $key = null)
    {
        $parts = [$this->cacheKey, $view];

        if ($key !== null) {
            $parts[] = $key;
        }

        return implode('.', $parts);
    }

    /**
     * Forget a rendered view.
     * 
     * @param  string $view
     * @param  string $key
     */
    public function forget($view, $key = null)
    {
        $cacheKey = $this->getCacheKeyForView($view, $key);

        if ($this->cacheIsTaggable) {
            $this->cache->tags($this->cacheKey)->forget($cacheKey);
        }

        $this->cache->forget($cacheKey);
    }

    /**
     * Empty the partial cache completely.
     * Note: Only supported by Taggable cache drivers.
     * 
     * @param  string $tag
     */
    public function flush($tag = null)
    {
        if (!$this->cacheIsTaggable) {
            throw new MethodNotSupportedException('The cache driver (' . 
                get_class($this->cacheManager->driver()) . ') doesn\'t support the flush method.');
        }

        $this->cache->tags($this->cacheKey)->flush();
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
