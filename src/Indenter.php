<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

class Indenter
{
    private const LINE_DELIMITER = "\n";

    public function indentLinesInString(string $content, int $spaceCount = 4): string
    {
        $lines = explode(self::LINE_DELIMITER, $content);

        return implode(self::LINE_DELIMITER, $this->indentLinesInArray($lines, $spaceCount));
    }

    /**
     * @param string[] $lines
     * @param int $spaceCount
     *
     * @return string[]
     */
    public function indentLinesInArray(array $lines, int $spaceCount = 4): array
    {
        return array_map(
            function ($line) use ($spaceCount) {
                if ('' === $line) {
                    return '';
                }

                $indentation = str_repeat(' ', $spaceCount);

                return $indentation . $line;
            },
            $lines
        );
    }
}
