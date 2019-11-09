<?php

declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

class Indenter
{
    private const LINE_DELIMITER = "\n";

    public function indentLines(array $lines, int $spaceCount = 4): array
    {
        return array_map(function ($line) use ($spaceCount) {
            if ('' === $line) {
                return '';
            }

            $indentation = str_repeat(' ', $spaceCount);

            return $indentation . $line;
        }, $lines);
    }

    public function indentContent(string $content, int $spaceCount = 4): string
    {
        $lines = $this->indentLines(explode(self::LINE_DELIMITER, $content), $spaceCount);

        return implode(self::LINE_DELIMITER, $lines);
    }
}
