<?php

namespace Spatie\PartialCache;

use Blade;
use Illuminate\Support\ServiceProvider;

class PartialCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources/config/skeleton.php' => config_path('skeleton.php'),
        ], 'config');

        $directive = config('partialcache.directive');

        Blade::directive($directive, function($expression) {
            if (starts_with($expression, '(')) {
                $expression = substr($expression, 1, -1);
            }

            return "<?php echo app()->make('partialcache')
                ->cache(array_except(get_defined_vars(), ['__data', '__path']), {$expression}); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/config.php', 'skeleton');

        $this->app->instance(PartialCache::class, new PartialCache());
        $this->app->alias(PartialCache::class, 'partialcache');
    }
}
