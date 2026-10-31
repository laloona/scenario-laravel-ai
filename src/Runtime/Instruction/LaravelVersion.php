<?php declare(strict_types=1);

/*
 * This file is part of Stateforge\Scenario\LaravelAi package.
 *
 * (c) Christina Koenig <christina.koenig@looriva.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stateforge\Scenario\LaravelAi\Runtime\Instruction;

use Stateforge\Scenario\Ai\Runtime\Instruction\Instruction;
use function sprintf;

final class LaravelVersion implements Instruction
{
    public function __construct(
        private readonly string $version,
    ) {
    }

    public function instruct(): string
    {
        return sprintf(
            'Generate code compatible with Laravel %s',
            $this->version,
        );
    }
}
