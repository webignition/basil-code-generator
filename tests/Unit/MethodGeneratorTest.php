<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\MethodGenerator;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\DocBlock;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;

class MethodGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MethodGenerator
     */
    private $methodGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->methodGenerator = MethodGenerator::create();
    }

    /**
     * @dataProvider createForClassDefinitionDataProvider
     *
     * @param MethodDefinitionInterface $methodDefinition
     * @param array<string, string> $variableIdentifiers
     * @param string $expectedCode
     */
    public function testCreateFromMethodDefinition(
        MethodDefinitionInterface $methodDefinition,
        array $variableIdentifiers,
        string $expectedCode
    ) {
        $code = $this->methodGenerator->createFromMethodDefinition($methodDefinition, $variableIdentifiers);

        $this->assertTrue(true);
        $this->assertEquals($expectedCode, $code);
    }

    public function createForClassDefinitionDataProvider(): array
    {
        $emptyProtectedMethod = new MethodDefinition('emptyProtectedMethod', new CodeBlock());
        $emptyProtectedMethod->setProtected();

        $emptyPrivateMethod = new MethodDefinition('emptyPrivateMethod', new CodeBlock());
        $emptyPrivateMethod->setPrivate();

        $emptyMethodWithReturnType = new MethodDefinition('emptyPublicMethodWithReturnType', new CodeBlock());
        $emptyMethodWithReturnType->setReturnType('string');

        $emptyPublicStaticMethod = new MethodDefinition('emptyPublicStaticMethod', new CodeBlock());
        $emptyPublicStaticMethod->setStatic();

        return [
            'public, no arguments, no return type, no lines, no variable identifiers' => [
                'methodDefinition' => new MethodDefinition('emptyPublicMethod', new CodeBlock()),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'public function emptyPublicMethod()' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'protected, no arguments, no return type, no lines, no variable identifiers' => [
                'methodDefinition' => $emptyProtectedMethod,
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'protected function emptyProtectedMethod()' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'private, no arguments, no return type, no lines, no variable identifiers' => [
                'methodDefinition' => $emptyPrivateMethod,
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'private function emptyPrivateMethod()' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'public, has arguments, no return type, no lines, no variable identifiers' => [
                'methodDefinition' => new MethodDefinition('emptyPublicMethod', new CodeBlock(), [
                    'arg1',
                    'arg2',
                    'arg3',
                ]),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'public function emptyPublicMethod($arg1, $arg2, $arg3)' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'public, no arguments, has return type, no lines, no variable identifiers' => [
                'methodDefinition' => $emptyMethodWithReturnType,
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'public function emptyPublicMethodWithReturnType(): string' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'public, has arguments, no return type, has lines, no variable identifiers' => [
                'methodDefinition' => new MethodDefinition(
                    'nameOfMethod',
                    new CodeBlock([
                        new Comment('Add x and y and then return'),
                        new Statement('$z = $x + $y'),
                        new EmptyLine(),
                        new Statement('return $z'),
                    ]),
                    ['x', 'y']
                ),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'public function nameOfMethod($x, $y)' . "\n" .
                    '{' . "\n" .
                    '    // Add x and y and then return' . "\n" .
                    '    $z = $x + $y;' . "\n" .
                    '' . "\n" .
                    '    return $z;' . "\n" .
                    '}'
            ],
            'public, has arguments, no return type, has lines with trailing newline, no variable identifiers' => [
                'methodDefinition' => new MethodDefinition(
                    'nameOfMethod',
                    new CodeBlock([
                        new Comment('comment'),
                        new EmptyLine(),
                    ]),
                    ['x', 'y']
                ),
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'public function nameOfMethod($x, $y)' . "\n" .
                    '{' . "\n" .
                    '    // comment' . "\n" .
                    '}'
            ],
            'public static, no arguments, no return type, no lines, no variable identifiers' => [
                'methodDefinition' => $emptyPublicStaticMethod,
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'public static function emptyPublicStaticMethod()' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'public, has arguments, no return type, has lines, has variable identifiers' => [
                'methodDefinition' => new MethodDefinition(
                    'nameOfMethod',
                    new CodeBlock([
                        new Comment('Add {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }} and then return'),
                        new Statement('$z = {{ PLACEHOLDER1 }} + {{ PLACEHOLDER2 }}'),
                        new EmptyLine(),
                        new Statement('return $z'),
                    ]),
                    ['x', 'y']
                ),
                'variableIdentifiers' => [
                    'PLACEHOLDER1' => '$x',
                    'PLACEHOLDER2' => '$y',
                ],
                'expectedCode' =>
                    'public function nameOfMethod($x, $y)' . "\n" .
                    '{' . "\n" .
                    '    // Add {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }} and then return' . "\n" .
                    '    $z = $x + $y;' . "\n" .
                    '' . "\n" .
                    '    return $z;' . "\n" .
                    '}'
            ],
            'public, has arguments, no return type, has lines, has variable identifiers, with docblock' => [
                'methodDefinition' => $this->createMethodDefinitionWithDocBlock(
                    new MethodDefinition(
                        'nameOfMethod',
                        new CodeBlock([
                            new Comment('Add {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }} and then return'),
                            new Statement('$z = {{ PLACEHOLDER1 }} + {{ PLACEHOLDER2 }}'),
                            new EmptyLine(),
                            new Statement('return $z'),
                        ]),
                        ['x', 'y']
                    ),
                    new DocBlock([
                        new Comment('@dataProvider nameOfMethodDataProvider'),
                    ])
                ),
                'variableIdentifiers' => [
                    'PLACEHOLDER1' => '$x',
                    'PLACEHOLDER2' => '$y',
                ],
                'expectedCode' =>
                    '/**' . "\n" .
                    ' * @dataProvider nameOfMethodDataProvider' . "\n" .
                    ' */' . "\n" .
                    'public function nameOfMethod($x, $y)' . "\n" .
                    '{' . "\n" .
                    '    // Add {{ PLACEHOLDER1 }} and {{ PLACEHOLDER2 }} and then return' . "\n" .
                    '    $z = $x + $y;' . "\n" .
                    '' . "\n" .
                    '    return $z;' . "\n" .
                    '}'
            ],
        ];
    }

    private function createMethodDefinitionWithDocBlock(
        MethodDefinitionInterface $methodDefinition,
        DocBlock $docBlock
    ): MethodDefinitionInterface {
        $methodDefinition->setDocBlock($docBlock);

        return $methodDefinition;
    }
}
