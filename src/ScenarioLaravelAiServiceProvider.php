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
use Stateforge\Scenario\Ai\Runtime\AdapterMode;
use Stateforge\Scenario\Ai\Runtime\Ai;
use Stateforge\Scenario\LaravelAi\Command\ScenarioAiConfigCommand;
use Stateforge\Scenario\LaravelAi\Command\ScenarioAiMakeCommand;
use Stateforge\Scenario\LaravelAi\Command\ScenarioAiSkillCommand;
use Throwable;

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

        $commands = [
            ScenarioAiConfigCommand::class,
        ];

        try {
            $config = Ai::config();
            if ($config->adapter->mode !== AdapterMode::Skill) {
                $commands[] = ScenarioAiMakeCommand::class;
            }
            if ($config->adapter->mode !== AdapterMode::Cli) {
                $commands[] = ScenarioAiSkillCommand::class;
            }
        } catch (Throwable $exception) {
        }

        $this->commands($commands);
    }
}
