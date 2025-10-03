<?php

namespace Asyntai\Statamic\Chatbot;

use Illuminate\Support\Facades\Route;
use Statamic\Providers\AddonServiceProvider;
use Asyntai\Statamic\Chatbot\Http\Middleware\InjectWidget;
use Asyntai\Statamic\Chatbot\Tags\AsyntaiTag;
use Statamic\Facades\CP\Nav as CpNav;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        AsyntaiTag::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    public function boot()
    {
        parent::boot();
        
        // Register CP navigation - use a simple global bound key to prevent duplicates
        if (!$this->app->bound('asyntai.cp.nav.registered')) {
            $this->app->instance('asyntai.cp.nav.registered', true);
            
            CpNav::extend(function ($nav) {
                $nav->create('Asyntai AI Chatbot')
                    ->section('Tools')
                    ->route('asyntai.index');
            });
        }
    }

    public function bootAddon()
    {
        // Register views namespace for addon templates
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'asyntai');

        // Frontend middleware injection
        $this->app['router']->pushMiddlewareToGroup('web', InjectWidget::class);

        // Frontend: inject widget script if connected (site_id present)
        $this->app['events']->listen('statamic.view.rendering', function ($view, $data) {
            $settings = Settings::instance();
            $siteId = trim((string) $settings->get('site_id', ''));
            if ($siteId === '') {
                return;
            }
            $scriptUrl = trim((string) $settings->get('script_url', 'https://asyntai.com/static/js/chat-widget.js'));
            $tag = '<script async defer src="'.e($scriptUrl).'" data-asyntai-id="'.e($siteId).'"></script>';
            view()->composer('*', function ($view) use ($tag) {
                $view->with('asyntai_widget_tag', $tag);
            });
        });
    }
}


