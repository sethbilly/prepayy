<?php

namespace CloudLoan\Libraries\Xds;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Client::class, function ($app) {
            $wsdl = config('services.xds.api_url');
            $username = config('services.xds.username');
            $password = config('services.xds.password');

            $client = new \SoapClient($wsdl, ['soap_version' => SOAP_1_2]);

            return new Client($client, $username, $password);
        });
    }

    public function provides()
    {
        return [Client::class];
    }
}
