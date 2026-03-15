<?php

namespace Imanghafoori\LaravelMicroscope\Tests\Iterators;

use Imanghafoori\LaravelMicroscope\Foundations\Analyzers\ComposerJson;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
use Imanghafoori\LaravelMicroscope\Foundations\FileReaders\BasePath;
use Imanghafoori\LaravelMicroscope\Foundations\Iterator;
use Imanghafoori\LaravelMicroscope\Foundations\Iterators\CheckSet;
use Imanghafoori\LaravelMicroscope\Foundations\PhpFileDescriptor;
use Imanghafoori\LaravelMicroscope\SpyClasses\RoutePaths;
use PHPUnit\Framework\TestCase;

class IteratorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        RoutePaths::$paths = [__DIR__.'/web.php'];
        BasePath::$path = __DIR__;

        $_SESSION['msg'] = [];
    }

    public function tearDown(): void
    {
        unset($_SESSION['msg']);
        parent::tearDown();
    }

    public function test_basic()
    {
        ComposerJson::$composer = function () {
            return new class
            {
                public function readAutoload()
                {
                    return [
                        '/' => ['App\\' => 'app'],
                    ];
                }

                public function readAutoloadClassMap()
                {
                    return [
                        '/' => ['class_map'],
                    ];
                }

                public function autoloadedFilesList()
                {
                    return [
                        '/' => ['class_map/MyClass.php'],
                    ];
                }
            };
        };
        $iterator = new Iterator(CheckSet::initParams([new class
        {
            public static function check(PhpFileDescriptor $file)
            {
                $file->getFileName();
            }
        }], new class
        {
            public function option()
            {
                return '';
            }
        }));

        Console::$instance = new class
        {
            public function write($msg)
            {
                $_SESSION['msg'][] = $msg;
            }
        };

        $iterator->formatPrintPsr4();
        $this->assertIsArray($_SESSION['msg']);
        $this->assertStringContainsString('<fg=blue> ./composer.json</>', $_SESSION['msg'][1]);
        $this->assertStringContainsString('PSR-4', $_SESSION['msg'][3]);
        $this->assertStringContainsString('App\\', $_SESSION['msg'][6]);
        $this->assertStringContainsString('./app', $_SESSION['msg'][7]);

        $iterator->formatPrintPsr4Classmap();
        $iterator->forRoutes();
        $iterator->formatPrintForComposerLoadedFiles();
    }
}
