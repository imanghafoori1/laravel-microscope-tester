<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
use Imanghafoori\LaravelMicroscope\LaravelPaths\LaravelPaths;

class CheckMigrationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;
        @mkdir(database_path('migrations2'), 0777, true);
        copy(__DIR__.'/CheckMigrationStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        LaravelPaths::$migrationDirs = [];
        @rmdir(database_path('migrations2'));
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        LaravelPaths::$migrationDirs[] = __DIR__.'/absent';
        LaravelPaths::$migrationDirs[] = base_path('vendor/imanghafoori');
        LaravelPaths::$migrationDirs[] = database_path('migrations2');

        Console::enforceTrue();
        $this->artisan('check:migrations')
            ->assertFailed()
            ->run();

        $this->assertEquals([
            'Do you want to replace 0001_01_01_000002_create_posts_table.php with new version of it?'
        ], Console::$askedConfirmations);
        $this->assertFileEquals(
            __DIR__.'/CheckMigrationStubs/expected.stub',
            $this->tmpFileUnderTest()
        );
    }

    private function tmpFileUnderTest()
    {
        return database_path('migrations/0001_01_01_000002_create_posts_table.php');
    }
}
