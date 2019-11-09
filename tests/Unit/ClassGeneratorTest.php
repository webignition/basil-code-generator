<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use webignition\BasilCodeGenerator\ClassGenerator;
use webignition\BasilCompilationSource\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MethodDefinition;
use webignition\BasilCompilationSource\Statement;

class ClassGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classGenerator = ClassGenerator::create();
    }

    /**
     * @dataProvider createForClassDefinitionDataProvider
     */
    public function testCreateForClassDefinition(
        ClassDefinitionInterface $classDefinition,
        ?string $baseClass,
        array $variableIdentifiers,
        string $expectedCode
    ) {
        $code = $this->classGenerator->createForClassDefinition($classDefinition, $baseClass, $variableIdentifiers);

        $this->assertTrue(true);
        $this->assertEquals($expectedCode, $code);
    }

    public function createForClassDefinitionDataProvider(): array
    {
        return [
            'no methods, no base class' => [
                'classDefinition' => new ClassDefinition('NameOfClass', []),
                'baseClass' => null,
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'class NameOfClass' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'no methods, has base class' => [
                'classDefinition' => new ClassDefinition('NameOfClass', []),
                'baseClass' => 'BaseClass',
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'class NameOfClass extends BaseClass' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'single method' => [
                'classDefinition' => new ClassDefinition('NameOfClass', [
                    new MethodDefinition('methodName', new LineList())
                ]),
                'baseClass' => null,
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'class NameOfClass' . "\n" .
                    '{' . "\n" .
                    '    public function methodName()' . "\n" .
                    '    {' . "\n\n" .
                    '    }' . "\n" .
                    '}'
            ],
            'many methods' => [
                'classDefinition' => new ClassDefinition('NameOfClass', [
                    new MethodDefinition('init', new LineList([
                        new Comment('initialize'),
                        new Statement(
                            '$this->widget = new Widget()',
                            (new Metadata())
                                ->withClassDependencies(new ClassDependencyCollection([
                                    new ClassDependency('Acme\Widget'),
                                ]))
                        ),
                    ])),
                    new MethodDefinition(
                        'run',
                        new LineList([
                            new Statement('$this->widget->go($x)')
                        ]),
                        ['x']
                    ),
                ]),
                'baseClass' => null,
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'use Acme\Widget;' . "\n\n" .
                    'class NameOfClass' . "\n" .
                    '{' . "\n" .
                    '    public function init()' . "\n" .
                    '    {' . "\n" .
                    '        // initialize' . "\n" .
                    '        $this->widget = new Widget();' . "\n" .
                    '    }' . "\n\n" .
                    '    public function run($x)' . "\n" .
                    '    {' . "\n" .
                    '        $this->widget->go($x);' . "\n" .
                    '    }' . "\n" .
                    '}'
            ],
        ];
    }
}
