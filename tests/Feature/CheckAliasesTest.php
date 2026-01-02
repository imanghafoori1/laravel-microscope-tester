<?php

use App\Models\User;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\FacadeAlias\FacadeAliasesCheck;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckAliasesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        copy(__DIR__.'/CheckFacadeAliasesStub/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
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

        $r = $this->artisan('check:aliases')
            ->expectsOutputToContain('🔍 Looking Facade Aliases...')
            ->expectsConfirmation('Do you want to replace Session with Illuminate\Support\Facades\Session', 'yes')
            ->expectsConfirmation('Do you want to replace Auth with Illuminate\Support\Facades\Auth', 'yes')
            ->expectsConfirmation('Do you want to replace Config with Illuminate\Support\Facades\Config', 'yes')
            ->expectsConfirmation('Do you want to replace Mate with Illuminate\Support\Facades\Request', 'yes')
            ->expectsConfirmation('Do you want to replace Rate with Illuminate\Support\Facades\Gate', 'yes')
            ->expectsConfirmation('Do you want to replace Response with Illuminate\Support\Facades\Response', 'yes')
            ->expectsConfirmation('Do you want to replace MyAlias with App\Models\User', 'yes')
            ->expectsConfirmation('Do you want to replace MyAlias2 with App\Models\User2', 'yes')
            ->run();

        $this->assertEquals(0, $r);

        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/CheckFacadeAliasesStub/expected.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );
    }

    public function test_no_fix()
    {
        $this->artisan('check:aliases --nofix')
            ->expectsOutputToContain('🔍 Looking Facade Aliases...')
            ->expectsOutputToContain('Facade alias: Auth for Illuminate\Support\Facades\Auth')
            ->expectsOutputToContain('Facade alias: Config for Illuminate\Support\Facades\Config')
            ->expectsOutputToContain('Facade alias: Mate for Illuminate\Support\Facades\Request')
            ->expectsOutputToContain('Facade alias: Rate for Illuminate\Support\Facades\Gate')
            ->expectsOutputToContain('Facade alias: Response for Illuminate\Support\Facades\Response')
            ->run();

        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/CheckFacadeAliasesStub/init.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->tmpFileUnderTest()))
        );
    }

    public function test_no_fix_alias()
    {
        $this->artisan('check:aliases --nofix --alias=Auth,Config')
            ->expectsOutputToContain('🔍 Looking Facade Aliases...')
            ->expectsOutputToContain('Facade alias: Auth for Illuminate\Support\Facades\Auth')
            ->expectsOutputToContain('Facade alias: Config for Illuminate\Support\Facades\Config')
            ->doesntExpectOutputToContain('Facade alias: Mate for Illuminate\Support\Facades\Request')
            ->doesntExpectOutputToContain('Facade alias: Rate for Illuminate\Support\Facades\Gate')
            ->doesntExpectOutputToContain('Facade alias: Response for Illuminate\Support\Facades\Response')
            ->run();

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
