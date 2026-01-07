<?php

use App\Models\User;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckAliasesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        copy(__DIR__.'/CheckFacadeAliasesStub/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        AliasLoader::getInstance()->alias('MyAlias', User::class);
        AliasLoader::getInstance()->alias('MyAlias2', 'App\\Models\\User2');

        $r = $this->artisan('check:aliases')
            ->expectsOutputToContain('🔍 Looking Facade Aliases...')
            ->expectsConfirmation('Do you want to replace <fg=yellow>Session</> with <fg=yellow>Illuminate\Support\Facades\Session</>', 'yes')
            ->expectsConfirmation('Do you want to replace <fg=yellow>Auth</> with <fg=yellow>Illuminate\Support\Facades\Auth</>', 'yes')
            ->expectsConfirmation('Do you want to replace <fg=yellow>Config</> with <fg=yellow>Illuminate\Support\Facades\Config</>', 'yes')
            ->expectsConfirmation('Do you want to replace <fg=yellow>Mate</> with <fg=yellow>Illuminate\Support\Facades\Request</>', 'yes')
            ->expectsConfirmation('Do you want to replace <fg=yellow>Rate</> with <fg=yellow>Illuminate\Support\Facades\Gate</>', 'yes')
            ->expectsConfirmation('Do you want to replace <fg=yellow>Response</> with <fg=yellow>Illuminate\Support\Facades\Response</>', 'yes')
            ->expectsConfirmation('Do you want to replace <fg=yellow>MyAlias</> with <fg=yellow>App\Models\User</>', 'yes')
            ->expectsConfirmation('Do you want to replace <fg=yellow>MyAlias2</> with <fg=yellow>App\Models\User2</>', 'yes')
            ->run();

        $this->assertEquals(0, $r);

        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents(__DIR__.'/CheckFacadeAliasesStub/expected.stub')),
            str_replace("\r\n", "\n", file_get_contents($this->mainPath()))
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
    }

    private function mainPath()
    {
        return app_path('Aliases.php');
    }
}
