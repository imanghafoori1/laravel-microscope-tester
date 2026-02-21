<?php

use App\Models\User;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\FacadeAlias\FacadeAliasesCheck;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckAliasesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        Console::$instance = new class
        {
            public $writeln = [];

            public function writeln($write)
            {
                $this->writeln[] = $write;
            }
        };

        copy(__DIR__.'/CheckFacadeAliasesStub/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        Color::$color = true;
        File::deleteDirectory(storage_path('framework/cache/microscope/'), true);
        FacadeAliasesCheck::$alias = '-all-';
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
        $this->assertEquals(0, $r);

        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/CheckFacadeAliasesStub/expected.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );
    }

    public function test_no_fix()
    {
        AliasLoader::getInstance()->alias('MyAlias', User::class);
        AliasLoader::getInstance()->alias('MyAlias2', 'App\\Models\\User2');
        $this->artisan('check:aliases --nofix')->run();

        $expected = [
            '   Facade alias: Session for Illuminate\Support\Facades\Session',
            '   at app\Aliases.php:6',
            '   ',
            '   Facade alias: Auth for Illuminate\Support\Facades\Auth',
            '   at app\Aliases.php:7',
            '   ',
            '   Facade alias: Config for Illuminate\Support\Facades\Config',
            '   at app\Aliases.php:7',
            '   ',
            '   Facade alias: Mate for Illuminate\Support\Facades\Request',
            '   at app\Aliases.php:8',
            '   ',
            '   Facade alias: Rate for Illuminate\Support\Facades\Gate',
            '   at app\Aliases.php:9',
            '   ',
            '   Facade alias: Response for Illuminate\Support\Facades\Response',
            '   at app\Aliases.php:10',
            '   ',
            '   Facade alias: MyAlias for App\Models\User',
            '   at app\Aliases.php:11',
            '   ',
            '   Facade alias: MyAlias2 for App\Models\User2',
            '   at app\Aliases.php:12',
            '   ',
        ];

        $this->assertEquals($expected, Console::getInstance()->writeln);

        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/CheckFacadeAliasesStub/init.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );
    }

    public function test_no_fix_alias()
    {
        $this->artisan('check:aliases --nofix --alias=Auth,Config')->run();

        $expected = [
            0 => '   Facade alias: Auth for Illuminate\Support\Facades\Auth',
            1 => '   at app\Aliases.php:7',
            2 => '   ',
            3 => '   Facade alias: Config for Illuminate\Support\Facades\Config',
            4 => '   at app\Aliases.php:7',
            5 => '   ',
        ];
        $this->assertEquals($expected, Console::getInstance()->writeln);

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
