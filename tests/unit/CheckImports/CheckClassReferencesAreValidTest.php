<?php

namespace Imanghafoori\LaravelMicroscope\Tests\CheckImports;

use Imanghafoori\LaravelMicroscope\Features\CheckImports\Checks\CheckClassReferencesAreValid;
use Imanghafoori\LaravelMicroscope\Foundations\PhpFileDescriptor;
use Imanghafoori\TokenAnalyzer\ImportsAnalyzer;
use Imanghafoori\TokenAnalyzer\ParseUseStatement;
use PHPUnit\Framework\TestCase;

class CheckClassReferencesAreValidTest extends TestCase
{
    public function test_check()
    {
        $absPath = __DIR__.'/wrongImport.stub';
        $file = PhpFileDescriptor::make($absPath);
        CheckClassReferencesAreValid::$extraWrongImportsHandler = MockerUnusedWrongImportsHandler::class;
        CheckClassReferencesAreValid::$wrongClassRefsHandler = MockWrongClassRefsHandler::class;

        ImportsAnalyzer::$existenceChecker = new class {
            public static function check($class, $absFilePath): bool
            {
                return true;
            }
        };

        CheckClassReferencesAreValid::$importsProvider = function (PhpFileDescriptor $file) {
            $imports = ParseUseStatement::parseUseStatements($file->getTokens());

            return $imports[0] ?: [$imports[1]];
        };
        CheckClassReferencesAreValid::check($file);

        $unusedWrongImportsHandler = MockerUnusedWrongImportsHandler::$calls;
        $wrongClassRefsHandler = MockWrongClassRefsHandler::$calls;

        $this->assertEquals([], $unusedWrongImportsHandler[0][0]);
        $this->assertEquals(__DIR__.'/wrongImport.stub', $unusedWrongImportsHandler[0][1]->getAbsolutePath());

        $this->assertEquals([], $wrongClassRefsHandler);
    }
}

class MockerUnusedWrongImportsHandler
{
    public static $calls = [];

    public static function handle($unusedCorrectImports, $absFilePath)
    {
        self::$calls[] = [$unusedCorrectImports, $absFilePath];
    }

    public static function reset()
    {
        self::$calls = [];
    }
}

class MockWrongClassRefsHandler
{
    public static $calls = [];

    public static function handle(array $wrongClassRefs, $absFilePath)
    {
        self::$calls[] = [$wrongClassRefs, $absFilePath];
    }

    public static function reset()
    {
        self::$calls = [];
    }
}
