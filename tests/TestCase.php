<?php

namespace Noorfarooqy\Salaamch\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Noorfarooqy\Salaamch\SalaamchServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    // use CreatesApplication, DatabaseTransactions;
    protected function setUp(): void
    {
        parent::setUp();
    }
    protected function getPackageProviders($app)
    {
        return [
            SalaamchServiceProvider::class,
        ];
    }
}
