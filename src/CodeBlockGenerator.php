<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\LineListInterface;

class CodeBlockGenerator
{
    private $useStatementFactory;
    private $lineListGenerator;

    public function __construct(
        UseStatementFactory $useStatementFactory,
        LineListGenerator $lineListGenerator
    ) {
        $this->useStatementFactory = $useStatementFactory;
        $this->lineListGenerator = $lineListGenerator;
    }

    public static function create(): CodeBlockGenerator
    {
        return new CodeBlockGenerator(
            new UseStatementFactory(),
            LineListGenerator::create()
        );
    }

    /**
     * @param LineListInterface $lineList
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createFromLineList(LineListInterface $lineList, array $variableIdentifiers = []): string
    {
        $lines = $this->lineListGenerator->createFromLineList($lineList, $variableIdentifiers);

        return implode("\n", $lines);
    }

    /**
     * @param LineListInterface $lineList
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createWithUseStatementsFromLineList(
        LineListInterface $lineList,
        array $variableIdentifiers = []
    ): string {
        $code = $this->createFromLineList($lineList->getMetadata()->getClassDependencies());

        if ('' !== $code) {
            $code .= "\n\n";
        }

        return $code . $this->createFromLineList($lineList, $variableIdentifiers);
    }
}
