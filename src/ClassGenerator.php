<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;

class ClassGenerator
{
    const CLASS_SIGNATURE_TEMPLATE = 'class %s %s';

    private $classDependencyHandler;
    private $methodGenerator;
    private $lineListGenerator;
    private $indenter;

    public function __construct(
        UseStatementFactory $classDependencyHandler,
        MethodGenerator $methodGenerator,
        LineListGenerator $lineListGenerator,
        Indenter $indenter
    ) {
        $this->classDependencyHandler = $classDependencyHandler;
        $this->methodGenerator = $methodGenerator;
        $this->lineListGenerator = $lineListGenerator;
        $this->indenter = $indenter;
    }

    public static function create(): ClassGenerator
    {
        return new ClassGenerator(
            new UseStatementFactory(),
            MethodGenerator::create(),
            LineListGenerator::create(),
            new Indenter()
        );
    }

    /**
     * @param ClassDefinitionInterface $classDefinition
     * @param string|null $baseClass
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createForClassDefinition(
        ClassDefinitionInterface $classDefinition,
        string $baseClass = null,
        array $variableIdentifiers = []
    ) {
        $useStatementCode = $this->createUseStatementCode($classDefinition->getMetadata()->getClassDependencies());
        $signature = $this->createClassSignatureLine($classDefinition->getName(), $baseClass);
        $body = $this->createClassBody($classDefinition->getMethods(), $variableIdentifiers);



        $classTemplate = <<<'EOD'
%s

%s
{
%s
}
EOD;

        return trim(sprintf(
            $classTemplate,
            $useStatementCode,
            $signature,
            $body
        ));
    }

    /**
     * @param ClassDependencyCollection $classDependencies
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    private function createUseStatementCode(ClassDependencyCollection $classDependencies): string
    {
        $useStatements = new LineList();

        foreach ($classDependencies as $classDependency) {
            $useStatements->addLinesFromSource($this->classDependencyHandler->createSource($classDependency));
        }

        return implode("\n", $this->lineListGenerator->createFromLineList($useStatements, []));
    }

    private function createClassSignatureLine(string $className, ?string $baseClass)
    {
        $extendsSegment = null === $baseClass
             ? ''
            : 'extends ' . $baseClass;

        return trim(sprintf(self::CLASS_SIGNATURE_TEMPLATE, $className, $extendsSegment));
    }

    /**
     * @param array $methods
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    private function createClassBody(array $methods, array $variableIdentifiers): string
    {
        $methodCode = [];

        foreach ($methods as $methodDefinition) {
            $method = $this->methodGenerator->createFromMethodDefinition($methodDefinition, $variableIdentifiers);
            $method = $this->indenter->indentContent($method);

            $methodCode[] = $method;
        }

        return implode("\n\n", $methodCode);
    }
}
