<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BasilCodeGenerator\ClassGenerator;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\DocBlock;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;

class ClassGeneratorTest extends TestCase
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
        string $fullyQualifiedBaseClass,
        array $variableIdentifiers,
        string $expectedCode
    ) {
        $code = $this->classGenerator->createForClassDefinition(
            $classDefinition,
            $fullyQualifiedBaseClass,
            $variableIdentifiers
        );

        $this->assertTrue(true);
        $this->assertEquals($expectedCode, $code);
    }

    public function createForClassDefinitionDataProvider(): array
    {
        return [
            'no methods, base class in root namespace' => [
                'classDefinition' => new ClassDefinition('NameOfClass', []),
                'fullyQualifiedBaseClass' => 'TestCase',
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'class NameOfClass extends TestCase' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'no methods, base class in non-root namespace' => [
                'classDefinition' => new ClassDefinition('NameOfClass', []),
                'fullyQualifiedBaseClass' => TestCase::class,
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'use PHPUnit\Framework\TestCase;' . "\n" .
                    '' . "\n" .
                    'class NameOfClass extends TestCase' . "\n" .
                    '{' . "\n\n" .
                    '}'
            ],
            'single method' => [
                'classDefinition' => new ClassDefinition('NameOfClass', [
                    new MethodDefinition('methodName', new CodeBlock())
                ]),
                'fullyQualifiedBaseClass' => 'TestCase',
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'class NameOfClass extends TestCase' . "\n" .
                    '{' . "\n" .
                    '    public function methodName()' . "\n" .
                    '    {' . "\n\n" .
                    '    }' . "\n" .
                    '}'
            ],
            'many methods' => [
                'classDefinition' => new ClassDefinition('NameOfClass', [
                    new MethodDefinition('init', new CodeBlock([
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
                        new CodeBlock([
                            new Statement('$this->widget->go($x)')
                        ]),
                        ['x']
                    ),
                ]),
                'fullyQualifiedBaseClass' => 'TestCase',
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'use Acme\Widget;' . "\n\n" .
                    'class NameOfClass extends TestCase' . "\n" .
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
            'many methods, with docblock' => [
                'classDefinition' => new ClassDefinition('NameOfClass', [
                    $this->createMethodDefinitionWithDocBlock(
                        new MethodDefinition('init', new CodeBlock([
                            new Comment('initialize'),
                            new Statement(
                                '$this->widget = new Widget()',
                                (new Metadata())
                                    ->withClassDependencies(new ClassDependencyCollection([
                                        new ClassDependency('Acme\Widget'),
                                    ]))
                            ),
                        ])),
                        new DocBlock([
                            new Comment('initialisation')
                        ])
                    ),
                    $this->createMethodDefinitionWithDocBlock(
                        new MethodDefinition(
                            'run',
                            new CodeBlock([
                                new Statement('$this->widget->go($x)')
                            ]),
                            ['x']
                        ),
                        new DocBlock([
                            new Comment('execution')
                        ])
                    ),
                ]),
                'fullyQualifiedBaseClass' => 'TestCase',
                'variableIdentifiers' => [],
                'expectedCode' =>
                    'use Acme\Widget;' . "\n\n" .
                    'class NameOfClass extends TestCase' . "\n" .
                    '{' . "\n" .
                    '    /**' . "\n" .
                    '     * initialisation' . "\n" .
                    '     */' . "\n" .
                    '    public function init()' . "\n" .
                    '    {' . "\n" .
                    '        // initialize' . "\n" .
                    '        $this->widget = new Widget();' . "\n" .
                    '    }' . "\n\n" .
                    '    /**' . "\n" .
                    '     * execution' . "\n" .
                    '     */' . "\n" .
                    '    public function run($x)' . "\n" .
                    '    {' . "\n" .
                    '        $this->widget->go($x);' . "\n" .
                    '    }' . "\n" .
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
