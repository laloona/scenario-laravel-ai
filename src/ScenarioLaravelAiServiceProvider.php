<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\LaravelAi package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\LaravelAi;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Stateforge\Scenario\LaravelAi\Command\ScenarioAiConfigCommand;
use Stateforge\Scenario\LaravelAi\Command\ScenarioAiMakeCommand;

final class ScenarioLaravelAiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole() === false) {
            return;
        }

        /** @var array<string>|string $allowedEnvs */
        $allowedEnvs = Config::get('scenario.allowed_envs', ['local', 'develop', 'testing']);
        if ($this->app->environment($allowedEnvs) === false) {
            return;
        }

        $this->commands([
            ScenarioAiConfigCommand::class,
            ScenarioAiMakeCommand::class,
        ]);
    }
}
