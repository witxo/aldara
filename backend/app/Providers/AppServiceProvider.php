<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use App\Rules\RecaptchaV3;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Tinker\TinkerServiceProvider::class);
        }
    }

    public function boot(): void
    {
        Model::shouldBeStrict(!$this->app->isProduction());
        Paginator::useBootstrapFive();

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Validator::extend('recaptcha_v3', function ($attribute, $value, $parameters, $validator) {
            $action = $parameters[0] ?? 'contact';
            $threshold = isset($parameters[1]) ? (float) $parameters[1] : null;

            $rule = new RecaptchaV3($action, $threshold);
            try {
                $rule->validate($attribute, $value, function ($message) use ($validator) {
                    $validator->addFailure($attribute, $message);
                });
                return true;
            } catch (\Throwable) {
                return false;
            }
        });
    }
}
