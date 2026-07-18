<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Compliance\Services\SesService;
use App\Domains\Compliance\Services\SesPayloadBuilder;
use App\Domains\Compliance\Services\SesPayloadValidator;
use App\Domains\Compliance\Services\SesXmlBuilder;

class SesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SesXmlBuilder::class, function ($app) {
            return new SesXmlBuilder();
        });

        $this->app->singleton(SesPayloadBuilder::class, function ($app) {
            return new SesPayloadBuilder(
                $app->make(SesXmlBuilder::class),
            );
        });

        $this->app->singleton(SesPayloadValidator::class, function ($app) {
            return new SesPayloadValidator();
        });

        $this->app->singleton(SesService::class, function ($app) {
            return new SesService(
                $app->make(SesPayloadBuilder::class),
                $app->make(SesPayloadValidator::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
