<?php namespace Bayes;

use Illuminate\Support\ServiceProvider;

class BayesServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->bind('bayes', 'Bayes\Bayes');
    }

} 