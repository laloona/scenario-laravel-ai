<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\LaravelAi package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\LaravelAi\Command;

use Stateforge\Scenario\Ai\Console\CliConversation;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Laravel\Console\Command\ScenarioCommand;
use Stateforge\Scenario\Laravel\Console\Output;
use Stateforge\Scenario\LaravelAi\Runtime\Context;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ScenarioAiMakeCommand extends ScenarioCommand
{
    protected $signature = 'scenario:ai:make';

    protected $description = 'Make a scenario or parameter type with AI (local/develop/testing only)';

    protected function executeCommand(): int
    {
        (new Application())->prepare();

        $style = new Output(new SymfonyStyle($this->input, $this->output));
        return (new CliConversation('Scenario Laravel AI', 'Scenario Laravel framework'))->talk($style, new Context()) === true
            ? self::SUCCESS
            : self::FAILURE;
    }
}
