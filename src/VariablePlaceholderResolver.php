<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

use webignition\BasilCompilationSource\VariablePlaceholder;

class VariablePlaceholderResolver
{
    /**
     * @param string $content
     * @param array $variableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function resolve(string $content, array $variableIdentifiers): string
    {
        $search = [];
        $replace = [];

        foreach ($variableIdentifiers as $identifier => $name) {
            $search[] = sprintf(VariablePlaceholder::TEMPLATE, $identifier);
            $replace[] = $name;
        }

        $resolvedContent = (string) str_replace($search, $replace, $content);

        $placeholderMatches = [];
        if (preg_match('/{{ [^{]+ }}/', $resolvedContent, $placeholderMatches)) {
            $unresolvedPlaceholder = trim($placeholderMatches[0], '{} ');

            throw new UnresolvedPlaceholderException($unresolvedPlaceholder, $content);
        }

        return $resolvedContent;
    }
}
