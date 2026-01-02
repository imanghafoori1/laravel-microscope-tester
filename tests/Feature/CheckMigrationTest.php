<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;
use Imanghafoori\LaravelMicroscope\LaravelPaths\LaravelPaths;

class CheckMigrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/CheckMigrationStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        LaravelPaths::$migrationDirs[] = __DIR__.'/absent';
        LaravelPaths::$migrationDirs[] = base_path('vendor/imanghafoori');

        $r = $this->artisan('check:migrations')
            ->expectsQuestion('Do you want to replace 0001_01_01_000002_create_posts_table.php with new version of it?', 'yes')
            ->run();

        $this->assertIsInt($r);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckMigrationStubs/expected.stub'),
            file_get_contents($this->mainPath())
            );
    }

    private function mainPath()
    {
        return database_path('migrations/0001_01_01_000002_create_posts_table.php');
    }
}