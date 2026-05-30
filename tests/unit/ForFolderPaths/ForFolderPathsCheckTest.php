<?php

namespace Imanghafoori\LaravelMicroscope\Tests\ForFolderPaths;

use Imanghafoori\LaravelMicroscope\ErrorReporters\MessageBuilders\LaravelFoldersReport;
use Imanghafoori\LaravelMicroscope\ErrorReporters\Printer;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
use Imanghafoori\LaravelMicroscope\Foundations\FileReaders\BasePath;
use Imanghafoori\LaravelMicroscope\Foundations\Iterators\CheckSet;
use Imanghafoori\LaravelMicroscope\Foundations\Iterators\ForFolderPaths;
use Imanghafoori\LaravelMicroscope\Foundations\PathFilterDTO;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/SampleCheck.php';
class ForFolderPathsCheckTest extends TestCase
{
    public function test_basic()
    {
        BasePath::$path = __DIR__;
        Console::recoredWrites();
        $pathDTO = PathFilterDTO::make();
        $checkSet = CheckSet::init([SampleCheck::class], $pathDTO);
        $foldersStats = ForFolderPaths::check($checkSet, ['config' => $this->getDirsList()]);
        $lines = LaravelFoldersReport::formatFoldersStats($foldersStats);

        $_SESSION['test_ms'] = [];
        $_SESSION['files'] = [];
        Printer::printAll($lines);

        $ds = DIRECTORY_SEPARATOR;
        $this->assertTrue(array_key_exists(__DIR__.$ds.'ForFolderPathsCheckTest.php', $_SESSION['files']));
        $this->assertTrue(array_key_exists(__DIR__.$ds.'SampleCheck.php', $_SESSION['files']));
    }

    private function getDirsList()
    {
        return [__DIR__];
    }
}
