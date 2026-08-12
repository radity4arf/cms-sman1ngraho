<?php

/**
 * TestCase — Base class untuk seluruh test project
 *
 * Setup: enable FK constraint di SQLite (default OFF).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-MOD] : TestCase — enable FK pragma untuk SQLite

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // SQLite tidak meng-enforce foreign key secara default — harus diaktifkan manual
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
}
