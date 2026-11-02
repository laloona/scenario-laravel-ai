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

use Stateforge\Scenario\Ai\Console\CliSkill;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Laravel\Console\Command\ScenarioCommand;
use Stateforge\Scenario\Laravel\Console\Output;
use Stateforge\Scenario\LaravelAi\Runtime\Context;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ScenarioAiSkillCommand extends ScenarioCommand
{
    protected $signature = 'scenario:ai:skill';

    protected $description = 'Generate a skill IDE-ready skill for AI (local/develop/testing only)';

    protected function executeCommand(): int
    {
        (new Application())->prepare();

        $style = new Output(new SymfonyStyle($this->input, $this->output));
        return (new CliSkill())->generate($style, new Context()) === true
            ? self::SUCCESS
            : self::FAILURE;
    }
}
