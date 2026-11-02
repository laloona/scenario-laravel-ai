<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\LaravelAi package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\LaravelAi\Tests\Unit\Command;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use ReflectionClass;
use Stateforge\Scenario\Ai\Contract\AiAdapter;
use Stateforge\Scenario\Ai\Contract\AiSkill;
use Stateforge\Scenario\Ai\Contract\Instruction;
use Stateforge\Scenario\Ai\Runtime\Adapter\AdapterRegistry;
use Stateforge\Scenario\Ai\Runtime\AdapterConfiguration;
use Stateforge\Scenario\Ai\Runtime\AdapterMode;
use Stateforge\Scenario\Ai\Runtime\Ai;
use Stateforge\Scenario\Ai\Runtime\Configuration;
use Stateforge\Scenario\Ai\Runtime\SourceCodeRegistry;
use Stateforge\Scenario\Core\Runtime\Application;
use Stateforge\Scenario\Core\Runtime\Application\Configuration\Configuration as CoreConfiguration;
use Stateforge\Scenario\LaravelAi\Command\ScenarioAiSkillCommand;
use Stateforge\Scenario\LaravelAi\Runtime\Context;
use Stateforge\Scenario\LaravelAi\Runtime\Instruction\LaravelVersion;
use Stateforge\Scenario\LaravelAi\Runtime\Instruction\ParameterImplementationInstruction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use function file_put_contents;
use function unlink;
use const DIRECTORY_SEPARATOR;

#[CoversClass(ScenarioAiSkillCommand::class)]
#[UsesClass(Context::class)]
#[UsesClass(LaravelVersion::class)]
#[UsesClass(ParameterImplementationInstruction::class)]
#[Group('command')]
#[Small]
final class ScenarioAiSkillCommandTest extends CommandTestCase
{
    private AdapterConfiguration $adapterConfiguration;

    protected function setUp(): void
    {
        parent::setUp();
        $path = $this->projectDirectory . '/vendor/stateforge/scenario-laravel';
        (new Filesystem())->makeDirectory($path . '/docs', 0777, true);
        (new Filesystem())->makeDirectory($path . '/blueprint', 0777, true);
        file_put_contents($path . '/docs/framework.md', 'Laravel skill documentation marker');
        file_put_contents($path . '/blueprint/scenario.blueprint', 'Laravel scenario blueprint marker');
        file_put_contents($path . '/blueprint/parameter.blueprint', 'Laravel parameter blueprint marker');
        $configuration = self::createStub(CoreConfiguration::class);
        $configuration->method('getSuites')->willReturn([]);
        $configuration->method('getParameterDirectory')->willReturn('scenario/parameter');
        $core = new ReflectionClass(Application::class);
        $core->getProperty('rootDir')->setValue(null, $this->projectDirectory);
        $core->getProperty('configuration')->setValue(null, $configuration);
        $this->adapterConfiguration = new AdapterConfiguration(AdapterMode::Skill, 'test-skill', null, null, []);
        $ai = new ReflectionClass(Ai::class);
        $ai->getProperty('config')->setValue(null, new Configuration(null, false, 3, $this->adapterConfiguration));
        $ai->getProperty('registry')->setValue(null, new AdapterRegistry());
        (new ReflectionClass(SourceCodeRegistry::class))->getProperty('classes')->setValue(null, []);
    }

    protected function tearDown(): void
    {
        $core = new ReflectionClass(Application::class);
        $core->getProperty('rootDir')->setValue(null, null);
        $core->getProperty('configuration')->setValue(null, null);
        $ai = new ReflectionClass(Ai::class);
        $ai->getProperty('config')->setValue(null, null);
        $ai->getProperty('registry')->setValue(null, null);
        (new ReflectionClass(SourceCodeRegistry::class))->getProperty('classes')->setValue(null, []);
        parent::tearDown();
    }

    public function testCommandIsConfigured(): void
    {
        $command = $this->command();

        self::assertSame('scenario:ai:skill', $command->getName());
        self::assertSame('Generate a skill IDE-ready skill for AI (local/develop/testing only)', $command->getDescription());
    }

    #[DataProvider('generationResults')]
    public function testGeneratesSkillWithLaravelInstructionsWithoutApiCall(?string $path, int $expectedStatus, string $expectedMessage): void
    {
        $skill = $this->createMock(AiSkill::class);
        $skill->expects(self::once())->method('generate')->willReturn($path);
        $adapter = $this->createMock(AiAdapter::class);
        $adapter->expects(self::never())->method('client');
        $adapter->expects(self::once())->method('skill')
            ->with(self::identicalTo($this->adapterConfiguration), self::isInstanceOf(Instruction::class))
            ->willReturnCallback(function (AdapterConfiguration $configuration, Instruction $instruction) use ($skill): AiSkill {
                $text = $instruction->instruct($configuration->mode);
                foreach ([
                    'Introduction',
                    'Framework Attribute Rules',
                    'Available attributes:',
                    'Documentation',
                    'Base class definitions:',
                    'Blueprint Rules',
                    'Scenario Rules',
                    'Scenario Blueprint:',
                    'ParameterType Rules',
                    'Parameter Type Blueprint:',
                    'ParameterType Implementation Rules',
                    'Final Validation',
                ] as $heading) {
                    self::assertStringContainsString($heading, $text);
                }
                self::assertStringNotContainsString('Conversation rules:', $text);
                return $skill;
            });
        $registry = new AdapterRegistry();
        $registry->register('test-skill', $adapter);
        (new ReflectionClass(Ai::class))->getProperty('registry')->setValue(null, $registry);
        $tester = new CommandTester($this->command());

        self::assertSame($expectedStatus, $tester->execute([]));
        self::assertStringContainsString($expectedMessage, $tester->getDisplay());
    }

    /** @return iterable<string, array{?string, int, string}> */
    public static function generationResults(): iterable
    {
        yield 'success' => ['SKILL.md', Command::SUCCESS, 'Skill was generated successfully: SKILL.md'];
        yield 'failure' => [null, Command::FAILURE, 'Skill generation failed.'];
    }

    private function command(): ScenarioAiSkillCommand
    {
        $command = new ScenarioAiSkillCommand();
        $command->setLaravel($this->app);
        return $command;
    }

    #[DataProvider('allowedEnvironments')]
    public function testAllowsConfiguredDevelopmentEnvironments(string $environment): void
    {
        $this->app->instance('env', $environment);
        $skill = $this->createMock(AiSkill::class);
        $skill->expects(self::once())->method('generate')->willReturn('SKILL.md');
        $adapter = $this->createMock(AiAdapter::class);
        $adapter->expects(self::once())->method('skill')->willReturn($skill);
        $adapter->expects(self::never())->method('client');
        $registry = new AdapterRegistry();
        $registry->register('test-skill', $adapter);
        (new ReflectionClass(Ai::class))->getProperty('registry')->setValue(null, $registry);

        self::assertSame(Command::SUCCESS, (new CommandTester($this->command()))->execute([]));
    }

    /** @return iterable<string, array{string}> */
    public static function allowedEnvironments(): iterable
    {
        yield 'local' => ['local'];
        yield 'develop' => ['develop'];
        yield 'testing' => ['testing'];
    }

    #[DataProvider('blockedConfigurations')]
    public function testRejectsInvalidConfigurationBeforeGeneratingSkill(string $environment, bool $installed, bool $validAllowedEnvironments, string $message): void
    {
        $this->app->instance('env', $environment);
        if (!$installed) {
            unlink($this->projectDirectory . DIRECTORY_SEPARATOR . 'scenario.dist.xml');
        }
        if (!$validAllowedEnvironments) {
            Config::set('scenario.allowed_envs', 'testing');
        }
        $adapter = $this->createMock(AiAdapter::class);
        $adapter->expects(self::never())->method('skill');
        $adapter->expects(self::never())->method('client');
        $registry = new AdapterRegistry();
        $registry->register('test-skill', $adapter);
        (new ReflectionClass(Ai::class))->getProperty('registry')->setValue(null, $registry);
        $tester = new CommandTester($this->command());

        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertStringContainsString($message, $tester->getDisplay());
    }

    /** @return iterable<string, array{string, bool, bool, string}> */
    public static function blockedConfigurations(): iterable
    {
        yield 'production' => ['production', true, true, 'Scenarios are not allowed in "production" environment.'];
        yield 'not installed' => ['testing', false, true, 'Scenario is not installed.'];
        yield 'invalid environment configuration' => ['testing', true, false, 'Configuration key "scenario.allowed_envs" must be an array.'];
    }

    #[DataProvider('installations')]
    public function testVisibilityRequiresScenarioInstallation(?string $filename): void
    {
        unlink($this->projectDirectory . DIRECTORY_SEPARATOR . 'scenario.dist.xml');
        if ($filename !== null) {
            file_put_contents($this->projectDirectory . DIRECTORY_SEPARATOR . $filename, 'Core configuration');
        }
        self::assertSame($filename === null, $this->command()->isHidden());
    }

    /** @return iterable<string, array{?string}> */
    public static function installations(): iterable
    {
        yield 'local config' => ['scenario.xml'];
        yield 'distributed config' => ['scenario.dist.xml'];
        yield 'not installed' => [null];
    }
}
