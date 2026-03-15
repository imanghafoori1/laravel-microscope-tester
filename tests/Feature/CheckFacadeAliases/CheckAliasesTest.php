<?php

use App\Models\User;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\EnforceImports\EnforceImportsCheck;
use Imanghafoori\LaravelMicroscope\Features\EnforceImports\EnforceImportsHandler;
use Imanghafoori\LaravelMicroscope\Features\FacadeAlias\FacadeAliasesCheck;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckAliasesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;

        copy(__DIR__.'/CheckFacadeAliasesStub/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        File::deleteDirectory(storage_path('framework/cache/microscope/'), true);
        FacadeAliasesCheck::$alias = '-all-';
        EnforceImportsCheck::$onError = EnforceImportsHandler::class;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        AliasLoader::getInstance()->alias('MyAlias', User::class);
        AliasLoader::getInstance()->alias('MyAlias2', 'App\\Models\\User2');

        Console::enforceTrue();

        $r = $this->artisan('check:aliases')
            ->expectsOutputToContain('🔍 Looking Facade Aliases...')
            ->run();

        $this->assertEquals([
            'Do you want to replace Session with Illuminate\Support\Facades\Session',
            'Do you want to replace Auth with Illuminate\Support\Facades\Auth',
            'Do you want to replace Config with Illuminate\Support\Facades\Config',
            'Do you want to replace Mate with Illuminate\Support\Facades\Request',
            'Do you want to replace Rate with Illuminate\Support\Facades\Gate',
            'Do you want to replace Response with Illuminate\Support\Facades\Response',
            'Do you want to replace MyAlias with App\Models\User',
            'Do you want to replace MyAlias2 with App\Models\User2',
        ], Console::$askedConfirmations);

        $this->assertEquals(1, $r);

        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/CheckFacadeAliasesStub/expected.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );

        $this->assertFileExists(storage_path('framework/cache/microscope/check_facade_alias_command.php'));
        $this->assertFileExists(storage_path('framework/cache/microscope/EnforceImports.php'));
        $data = require storage_path('framework/cache/microscope/check_facade_alias_command.php');

        $this->assertTrue(in_array('web.php', $data));
        $this->assertTrue(in_array('a.php', $data));
        $this->assertTrue(in_array('DatabaseSeeder.php', $data));
        $this->assertFalse(in_array('Aliases.php', $data));

        $r = $this->artisan('check:aliases')->run();
        $this->assertEquals(0, $r);

        $data = require storage_path('framework/cache/microscope/check_facade_alias_command.php');
        $this->assertTrue(in_array('Aliases.php', $data));
    }

    public function test_no_fix()
    {
        $ds = DIRECTORY_SEPARATOR;

        AliasLoader::getInstance()->alias('MyAlias', User::class);
        AliasLoader::getInstance()->alias('MyAlias2', 'App\\Models\\User2');
        $this->artisan('check:aliases --nofix')->assertFailed()->run();

        $write = Console::getInstance()->writeln;
        array_pop($write);

        $this->assertEquals([
            '   1 Alias found:',
            '   Session for Illuminate\Support\Facades\Session',
            'at app'.$ds.'Aliases.php:6',
            '_______',
            '   2 Alias found:',
            '   Auth for Illuminate\Support\Facades\Auth',
            'at app'.$ds.'Aliases.php:7',
            '_______',
            '   3 Alias found:',
            '   Config for Illuminate\Support\Facades\Config',
            'at app'.$ds.'Aliases.php:7',
            '_______',
            '   4 Alias found:',
            '   Mate for Illuminate\Support\Facades\Request',
            'at app'.$ds.'Aliases.php:8',
            '_______',
            '   5 Alias found:',
            '   Rate for Illuminate\Support\Facades\Gate',
            'at app'.$ds.'Aliases.php:9',
            '_______',
            '   6 Alias found:',
            '   Response for Illuminate\Support\Facades\Response',
            'at app'.$ds.'Aliases.php:10',
            '_______',
            '   7 Alias found:',
            '   MyAlias for App\Models\User',
            'at app'.$ds.'Aliases.php:11',
            '_______',
            '   8 Alias found:',
            '   MyAlias2 for App\Models\User2',
            'at app'.$ds.'Aliases.php:12',
            '_______',
        ], $write);

        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/CheckFacadeAliasesStub/init.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );
    }

    public function test_no_fix_alias()
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('check:aliases --nofix --alias=Auth,Config')->run();

        $expected = [
            '   1 Alias found:',
            '   Auth for Illuminate\Support\Facades\Auth',
            "at app{$ds}Aliases.php:7",
            '_______',
            '   2 Alias found:',
            '   Config for Illuminate\Support\Facades\Config',
            "at app{$ds}Aliases.php:7",
            '_______',
        ];

        $write = Console::getInstance()->writeln;
        array_pop($write);
        $this->assertEquals($expected, $write);

        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/CheckFacadeAliasesStub/init.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );
    }

    private function tmpFileUnderTest()
    {
        return app_path('Aliases.php');
    }
}
