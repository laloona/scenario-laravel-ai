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

use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use function file_put_contents;
use function sys_get_temp_dir;
use function uniqid;
use const DIRECTORY_SEPARATOR;

abstract class CommandTestCase extends TestCase
{
    protected string $projectDirectory;
    protected Application $app;

    protected function setUp(): void
    {
        $this->projectDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'scenario-laravel-ai-' . uniqid('', true);
        (new Filesystem())->makeDirectory($this->projectDirectory, 0777, true);
        file_put_contents($this->projectDirectory . DIRECTORY_SEPARATOR . 'scenario.dist.xml', 'Core configuration');
        $this->app = new Application($this->projectDirectory);
        $this->app->instance('env', 'testing');
        $this->app->instance('config', new Repository([]));
        $this->app->instance('files', new Filesystem());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Application::setInstance(null);
        (new Filesystem())->deleteDirectory($this->projectDirectory);
    }
}
