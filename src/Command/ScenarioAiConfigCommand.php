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

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Stateforge\Scenario\Laravel\Console\Command\ScenarioCommand;
use const DIRECTORY_SEPARATOR;

final class ScenarioAiConfigCommand extends ScenarioCommand
{
    protected $signature = 'scenario:ai:config';

    protected $description = 'Add the AI config for Scenario AI (local/develop/testing only)';

    protected function executeCommand(): int
    {
        $file = App::basePath(
            'vendor' . DIRECTORY_SEPARATOR .
            'stateforge' . DIRECTORY_SEPARATOR .
            'scenario-ai' . DIRECTORY_SEPARATOR .
            'blueprint' . DIRECTORY_SEPARATOR .
            'config.blueprint',
        );

        if (File::exists($file) === false) {
            $this->error('Config file generation failed.');
            return self::FAILURE;
        }

        $configFile = App::basePath('scenario-ai.dist.xml');
        if (File::exists($configFile) === true) {
            $this->error('Config file already exists.');
            return self::FAILURE;
        }

        File::copy($file, $configFile);

        if (File::exists($configFile) === false) {
            $this->error('Config file generation failed.');
            return self::FAILURE;
        }

        $this->info('Config file generated, please modify to your needs.');
        return self::SUCCESS;
    }
}
