<?php declare(strict_types=1);

namespace webignition\BasilCodeGenerator;

class UnresolvedPlaceholderException extends \Exception
{
    private $placeholder;
    private $content;

    public function __construct(string $placeholder, string $content)
    {
        $message = sprintf(
            'Unresolved placeholder "%s" in content "%s"',
            $placeholder,
            $content
        );

        parent::__construct($message);

        $this->placeholder = $placeholder;
        $this->content = $content;
    }

    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
