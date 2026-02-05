<?php

use Imanghafoori\LaravelMicroscope\Foundations\Analyzers\Fixer;
use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\ClassListProvider;
use Imanghafoori\LaravelMicroscope\Foundations\PhpFileDescriptor;

class FixImportTest extends TestCase
{
    public function tearDown(): void
    {
        @unlink(app_path('R.php'));
        @unlink(app_path('Models/R2.php'));
        @unlink(app_path('R3.php'));
        @unlink(app_path('Sample.php'));
        ClassListProvider::$allNamespaces = [];
        parent::tearDown();
    }

    public function test_fix_import()
    {
        ClassListProvider::$allNamespaces = [];
        copy(__DIR__.'/FixImportTest/sample_class.stub', app_path('Sample.php'));
        copy(__DIR__.'/FixImportTest/fix_import.stub', app_path('R.php'));
        copy(__DIR__.'/FixImportTest/fix_inline_ref.stub', app_path('Models/R2.php'));
        copy(__DIR__.'/FixImportTest/fix_inline_ref-2.stub', app_path('R3.php'));

        $result1 = Fixer::fixImport(app_path('R.php'), 'App\Wrong\Sample', 5, false);
        $result2 = Fixer::fixReference(PhpFileDescriptor::make(app_path('Models/R2.php')), '\App\Wrong\Sample', 11);
        $result3 = Fixer::fixReference(PhpFileDescriptor::make(app_path('R3.php')), '\App\Wrong\Sample', 9);

        $this->assertEquals(
            file_get_contents(__DIR__.'/FixImportTest/expected.stub'),
            file_get_contents(app_path('R.php'))
        );

        $this->assertEquals(
            file_get_contents(__DIR__.'/FixImportTest/fix_inline_class_ref_expected.stub'),
            file_get_contents(app_path('Models/R2.php'))
        );

        $this->assertEquals(
            file_get_contents(__DIR__.'/FixImportTest/fix_inline_class_ref-2_expected.stub'),
            file_get_contents(app_path('R3.php'))
        );

        $this->assertEquals([[5], [' Deleted!']], $result1);
        $this->assertEquals([true, ['App\Sample']], $result2);
    }
}