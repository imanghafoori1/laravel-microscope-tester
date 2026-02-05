<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckView\Check\CheckView;
use Imanghafoori\LaravelMicroscope\Features\CheckView\Check\CheckViewStats;
use Imanghafoori\LaravelMicroscope\Foundations\BaseCommand;
use Imanghafoori\LaravelMicroscope\Foundations\FileReaders\BasePath;
use Imanghafoori\LaravelMicroscope\Foundations\FileReaders\FilePath;
use Imanghafoori\LaravelMicroscope\Foundations\FileReaders\Paths;
use Imanghafoori\LaravelMicroscope\Foundations\Iterators\BladeFiles\CheckBladePaths;
use Imanghafoori\LaravelMicroscope\Foundations\Iterators\CheckSet;
use Imanghafoori\LaravelMicroscope\Foundations\PathFilterDTO;
use Imanghafoori\LaravelMicroscope\Foundations\PhpFileDescriptor;

class FoundationsTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_get_line()
    {
        $file = PhpFileDescriptor::make(__FILE__);
        $this->assertNull($file->getLine(4444444));

        $path = $file->path()->getWithWindowsDirectorySeprator();
        $this->assertStringContainsString('\FoundationsTest.php', $path);
    }

    public function test_git_confirm()
    {
        $obj = new class extends BaseCommand
        {
            public $checks = [];
            public $gitConfirm = true;

            public function gitConfirm()
            {
                return false;
            }
        };

        $this->assertNull($obj->handle());
    }

    public function test_checkset()
    {
        $obj = CheckSet::init([
            new class
            {
                public function check()
                {
                    throw new Exception('');
                }
            }
        ]);
        $obj->applyChecksInPath('App\\', 'app/');
        $this->assertNull($obj->pathDTO);
    }

    public function test_checl_blade_paths_scanned()
    {
        CheckBladePaths::$scanned = [app_path()];
        $r = CheckBladePaths::checkPaths(
            [app_path(), BasePath::$path.DIRECTORY_SEPARATOR.'vendor/laravel'],
            CheckSet::init([CheckView::class], PathFilterDTO::make())
        );
        foreach ($r as $path) {
        }
        $this->assertEquals(0, CheckViewStats::$checkedCallsCount);
    }

    public function test_Paths_class()
    {
        $result = Paths::getAbsFilePaths([], PathFilterDTO::make());

        $this->assertEquals([], $result);

        $result = FilePath::contains(__DIR__.'hello.php', PathFilterDTO::make('hello.php'));
        $this->assertEquals(true, $result);

        $result = FilePath::contains(__DIR__.'hello.php', PathFilterDTO::make('hello2.php'));
        $this->assertEquals(false, $result);

        $result = FilePath::contains(__DIR__.'hello.php', PathFilterDTO::make('', '', '', 'hello.php'));
        $this->assertEquals(false, $result);

        $result = FilePath::contains(__DIR__.'hello.php', PathFilterDTO::make('', '', '', 'hello2.php'));
        $this->assertEquals(true, $result);
    }
}