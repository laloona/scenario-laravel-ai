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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Stateforge\Scenario\LaravelAi\Runtime\Instruction\ParameterImplementationInstruction;

#[CoversClass(ParameterImplementationInstruction::class)]
#[Group('runtime')]
#[Small]
final class ParameterImplementationInstructionTest extends TestCase
{
    public function testUsesInheritedCastAndLaravelValidationRules(): void
    {
        $text = (new ParameterImplementationInstruction())->instruct();

        self::assertStringContainsString('The Laravel ParameterTypeDefinition base class already provides a final cast() method.', $text);
        self::assertStringContainsString('Never override or reimplement cast().', $text);
        self::assertStringContainsString('Implement complete validation rules in rules().', $text);
        self::assertStringContainsString('The rules() method MUST return an array of valid Laravel validation rules.', $text);
        self::assertStringContainsString('Invalid or unsupported input values MUST be rejected through the validation rules.', $text);
    }

    public function testRequiresCompatibleValueTypeInstances(): void
    {
        $text = (new ParameterImplementationInstruction())->instruct();

        self::assertStringContainsString('The valueType() method MUST return an instance of the appropriate ValueType class.', $text);
        self::assertStringContainsString('NEVER return a class name string from valueType().', $text);
        self::assertStringContainsString('Pass the required constructor arguments when creating ValueType instances.', $text);
        self::assertStringContainsString('The selected ValueType MUST be consistent with the values returned by the inherited cast() method.', $text);
    }

    public function testRequestsMissingInformationInsteadOfGeneratingPlaceholderCode(): void
    {
        $text = (new ParameterImplementationInstruction())->instruct();

        self::assertStringContainsString('Never replace required validation or conversion logic with placeholder comments.', $text);
        self::assertStringContainsString('If implementation requires unavailable project-specific dependencies, request the necessary information.', $text);
        self::assertStringContainsString('If meaningful implementation is impossible, request clarification instead of generating unrelated or incomplete code.', $text);
    }
}
