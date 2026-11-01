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

final class ParameterImplementationInstruction implements Instruction
{
    public function instruct(): string
    {
        return <<<Instruction
ParameterType Implementation Rules

The Laravel ParameterTypeDefinition base class already provides a final cast() method.
This method performs validation using rules() and converts valid values using valueType().

Therefore:
- Never override or reimplement cast().
- Implement complete validation rules in rules().
- Implement the appropriate value conversion in valueType().
- The rules() method MUST return an array of valid Laravel validation rules.
- The valueType() method MUST return an instance of the appropriate ValueType class.
- NEVER return a class name string from valueType().
- Pass the required constructor arguments when creating ValueType instances.
- The selected ValueType MUST be consistent with the values returned by the inherited cast() method.
- Invalid or unsupported input values MUST be rejected through the validation rules.
- Never replace required validation or conversion logic with placeholder comments.
- If implementation requires unavailable project-specific dependencies, request the necessary information.
- If meaningful implementation is impossible, request clarification instead of generating unrelated or incomplete code.
Instruction;
    }
}
