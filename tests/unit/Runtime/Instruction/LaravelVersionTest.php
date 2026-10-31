<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\LaravelAi package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\LaravelAi\Tests\Unit\Runtime\Instruction;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\LaravelAi\Runtime\Instruction\LaravelVersion;

#[CoversClass(LaravelVersion::class)]
#[Group('runtime')]
#[Small]
final class LaravelVersionTest extends TestCase
{
    #[DataProvider('versions')]
    public function testInstructIncludesTheProvidedLaravelVersion(string $version, string $expected): void
    {
        self::assertSame($expected, (new LaravelVersion($version))->instruct());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function versions(): iterable
    {
        yield 'major and minor' => ['12.0', 'Generate code compatible with Laravel 12.0'];
        yield 'patch version' => ['12.1.2', 'Generate code compatible with Laravel 12.1.2'];
        yield 'prerelease' => ['13.0.0-RC1', 'Generate code compatible with Laravel 13.0.0-RC1'];
    }
}
