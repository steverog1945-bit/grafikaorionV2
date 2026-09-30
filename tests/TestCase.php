<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Penjaga: tes memakai RefreshDatabase (mengosongkan tabel). Bila konfigurasi ter-cache
     * (php artisan config:cache), nilai phpunit.xml diabaikan dan tes akan berjalan di database
     * asli. Berhenti sebelum satu query pun dijalankan.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $db = (string) $app['config']->get('database.connections.' . $app['config']->get('database.default') . '.database');
        if (!str_contains($db, 'testing') && $db !== ':memory:') {
            fwrite(STDERR, "\nDIBATALKAN: tes mengarah ke database '{$db}', bukan database uji. Jalankan: php artisan config:clear\n");
            exit(1);
        }

        return $app;
    }
}
