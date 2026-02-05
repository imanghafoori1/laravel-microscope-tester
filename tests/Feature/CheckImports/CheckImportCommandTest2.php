<?php

namespace CheckImports;

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\ClassListProvider;
use Imanghafoori\TokenAnalyzer\ImportsAnalyzer;
use PHPUnit\Framework\Attributes\Test;

class CheckImportCommandTest2 extends TestCase
{
    protected $testDirectory;

    protected $stubPath;

    protected static $composerJson;

    protected function setUp(): void
    {
        parent::setUp();
        ImportsAnalyzer::$checkedRefCount = 0;
        ClassListProvider::$allNamespaces = [];
        copy(__DIR__.'/CheckImportsStubs/one_extra_import.stub', app_path('A.php'));
    }

    protected function tearDown(): void
    {
        ImportsAnalyzer::$checkedRefCount = 0;
        ClassListProvider::$allNamespaces = [];
        @unlink(app_path('A.php'));
        parent::tearDown();
    }

    #[Test]
    public function test_extra_import()
    {
        $ds = DIRECTORY_SEPARATOR;
        // Create test files with incorrect/missing namespaces
        // Run the artisan command on our test directory
        $status = $this->artisan('check:imports')
            ->expectsOutputToContain(' 🔸 1 wrong import found.')
            ->expectsOutputToContain(' 🔸 1 wrong class reference found.')
            ->expectsOutputToContain(' 🔸 2 extra imports found.')
            //
            ->expectsOutputToContain("   1 Inline class Ref 'T' does not exist:")
            ->expectsOutputToContain('   6| class A extends T {}')
            ->expectsOutputToContain("at app{$ds}A.php:6")
            //
            ->expectsOutputToContain("   2 Extra Import: ExraWrng")
            ->expectsOutputToContain('   4| use ExraWrng;')
            ->expectsOutputToContain("at app{$ds}A.php:4")
            //
            ->expectsOutputToContain("   3 Extra Import: App")
            ->expectsOutputToContain("   3| use App;")
            ->expectsOutputToContain("at app{$ds}A.php:3")
            //
            ->run();

        $this->assertEquals(1, $status);
    }
}
