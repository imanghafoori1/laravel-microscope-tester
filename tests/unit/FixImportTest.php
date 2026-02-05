<?php

use Imanghafoori\LaravelMicroscope\ClassListProvider;
use Imanghafoori\LaravelMicroscope\Foundations\Analyzers\Fixer;
use Illuminate\Foundation\Testing\TestCase;

class FixImportTest extends TestCase
{
    public function tearDown(): void
    {
        @unlink(app_path('R.php'));
        @unlink(app_path('Sample.php'));
        ClassListProvider::$allNamespaces = [];
        parent::tearDown();
    }

    public function test_fix_import()
    {
        ClassListProvider::$allNamespaces = [];
        copy(__DIR__.'/FixImportTest/sample_class.stub', app_path('Sample.php'));
        copy(__DIR__.'/FixImportTest/fix_import.stub', app_path('R.php'));
        $absPath = app_path('R.php');

        $result = Fixer::fixImport($absPath, 'App\Wrong\Sample', 5, false);

        $this->assertEquals(
            file_get_contents(__DIR__.'/FixImportTest/expected.stub'),
            file_get_contents(app_path('R.php'))
        );

        $this->assertEquals([[5], [' Deleted!']], $result);
    }
}