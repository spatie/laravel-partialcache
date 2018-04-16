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
    /** @var \Illuminate\Contracts\View\Factory */
    protected $view;

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var \Illuminate\Contracts\Cache\Factory */
    protected $cacheManager;

    /** @var string */
    protected $cacheKey;

    /** @var bool */
    protected $cacheIsTaggable;

    /** @var bool */
    protected $enabled;

    /** @var int|null */
    protected $duration;

    public function __construct(View $view, Cache $cache, CacheManager $cacheManager, Config $config)
    {
        $this->view = $view;
        $this->cache = $cache;
        $this->cacheManager = $cacheManager;

        $this->cacheKey = $config->get('partialcache.key');
        $this->cacheIsTaggable = is_a($this->cacheManager->driver()->getStore(), TaggableStore::class);
        $this->enabled = $this->determineEnabled($config);
        $this->duration = $config->get('partialcache.duration');
    }

    /**
     * Cache a view. If minutes are null, the view is cached forever.
     *
     * @param array        $data
     * @param string       $view
     * @param array        $mergeData
     * @param int          $minutes
     * @param string       $key
     * @param string|array $tag
     *
     * @return string
     */
    public function cache($data, $view, $mergeData = null, $minutes = null, $key = null, $tag = null)
    {
        if (!$this->enabled) {
            return call_user_func($this->renderView($view, $data, $mergeData));
        }

        $viewKey = $this->getCacheKeyForView($view, $key);

        $mergeData = $mergeData ?: [];

        $tags = $this->getTags($tag);

        $minutes = $this->resolveCacheDuration($minutes);

        if ($this->cacheIsTaggable && $minutes === null) {
            return $this->cache
                ->tags($tags)
                ->rememberForever($viewKey, $this->renderView($view, $data, $mergeData));
        }

        if ($this->cacheIsTaggable) {
            return $this->cache
                ->tags($tags)
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
     * @param string $view
     * @param string $key
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
     * @param string $view
     * @param string $key
     * @param null|string|array $tag
     */
    public function forget($view, $key = null, $tag = null)
    {
        $cacheKey = $this->getCacheKeyForView($view, $key);

        if ($this->cacheIsTaggable) {
            $tags = $this->getTags($tag);
            $this->cache->tags($tags)->forget($cacheKey);
        }

        $this->cache->forget($cacheKey);
    }

    /**
     * Empty all views linked to a tag or the complete partial cache.
     * Note: Only supported by Taggable cache drivers.
     *
     * @param string $tag
     *
     * @throws \Spatie\PartialCache\Exceptions\MethodNotSupportedException
     */
    public function flush($tag = null)
    {
        if (!$this->cacheIsTaggable) {
            throw new MethodNotSupportedException('The cache driver ('.
                get_class($this->cacheManager->driver()).') doesn\'t support the flush method.');
        }

        $tag = $tag ?: $this->cacheKey;
        $this->cache->tags($tag)->flush();
    }

    /**
     * Render a view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return string
     */
    protected function renderView($view, $data, $mergeData)
    {
        $data = $data ?: [];
        $mergeData = $mergeData ?: [];

        return function () use ($view, $data, $mergeData) {
            return $this->view->make($view, $data, $mergeData)->render();
        };
    }

    /**
     * Constructs tag array
     *
     * @param null|string|array $tag
     *
     * @return array
     */
    protected function getTags($tag = null)
    {
        $tags = [$this->cacheKey];

        if ($tag) {
            if (!is_array($tag)) {
                $tag = [$tag];
            }

            $tags = array_merge($tags, $tag);
        }

        return $tags;
    }

    protected function determineEnabled(Config $config)
    {
        $configValue = $config->get('partialcache.enabled');

        /**
         * Previous versions of the package mistakenly used a string for the enabled setting.
         */
        if (is_string($config)) {
            return filter_var($configValue,FILTER_VALIDATE_BOOLEAN);
        }

        return $configValue;
    }

    /**
     * Resolve cache duration, defaults to the config if minutes is null
     *
     * @param int|null $minutes
     * @return int|null
     */
    protected function resolveCacheDuration($minutes = null)
    {
        if (is_null($minutes)) {
            return $this->duration;
        }

        return $minutes;
    }
}
