<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 27/06/2016
 * Time: 11:26
 */

trait DatabaseMigrationsForSqlite
{
    /**
     * Migrate and seed the database if using sqlite in memory
     * @param bool $shouldSeed
     */
    public function runMigrations($shouldSeed = true)
    {
        $connection = config('database.default');

        if (config("database.connections.{$connection}.driver") === 'sqlite' &&
            config("database.connections.{$connection}.database") === ':memory:'
        ) {
            $this->artisan('migrate');

            if ($shouldSeed) {
                $this->artisan('db:seed');
            }
        }
    }
}