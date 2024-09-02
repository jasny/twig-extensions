<?php

namespace Jasny\Twig;

use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Expose the PCRE functions to Twig.
 *
 * @see http://php.net/manual/en/book.pcre.php
 */
class PcreExtension extends AbstractExtension
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        if (!extension_loaded('pcre')) {
            throw new \Exception("The Twig PCRE extension requires the PCRE extension."); // @codeCoverageIgnore
        }
    }

    /**
     * Return extension name
     */
    public function getName(): string
    {
        return 'jasny/pcre';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('preg_quote', [$this, 'quote']),
            new TwigFilter('preg_match', [$this, 'match']),
            new TwigFilter('preg_get', [$this, 'get']),
            new TwigFilter('preg_get_all', [$this, 'getAll']),
            new TwigFilter('preg_grep', [$this, 'grep']),
            new TwigFilter('preg_replace', [$this, 'replace']),
            new TwigFilter('preg_filter', [$this, 'filter']),
            new TwigFilter('preg_split', [$this, 'split']),
        ];
    }


    /**
     * Check that the regex doesn't use the eval modifier
     *
     * @throws RuntimeError
     */
    protected function assertNoEval(string ...$pattern): void
    {
        $patterns = $pattern;

        foreach ($patterns as $pattern) {
            $pos       = strrpos($pattern, $pattern[0]);
            $modifiers = substr($pattern, $pos + 1);

            if (strpos($modifiers, 'e') !== false) {
                throw new RuntimeError("Using the eval modifier for regular expressions is not allowed");
            }
        }
    }

    /**
     * Quote regular expression characters.
     */
    public function quote(?string $value, string $delimiter = '/'): ?string
    {
        if (!isset($value)) {
            return null;
        }

        return preg_quote($value, $delimiter);
    }

    /**
     * Wrapper for preg_match that throws an exception on error.
     *
     * @throws RuntimeError
     */
    private function pregMatch(string $pattern, string $value, &$matches = []): int
    {
        $ret = preg_match($pattern, $value, $matches);

        if ($ret === false) {
            throw new RuntimeError("Error in regular expression: $pattern");
        }

        return $ret;
    }

    /**
     * Perform a regular expression match.
     */
    public function match(?string $value, string $pattern): bool
    {
        if (!isset($value)) {
            return false;
        }

        return $this->pregMatch($pattern, $value) > 0;
    }

    /**
     * Perform a regular expression match and return a matched group.
     */
    public function get(?string $value, string $pattern, int $group = 0): ?string
    {
        if (!isset($value)) {
            return null;
        }

        return $this->pregMatch($pattern, $value, $matches) > 0 && isset($matches[$group]) ? $matches[$group] : null;
    }

    /**
     * Perform a regular expression match and return the group for all matches.
     */
    public function getAll(?string $value, string $pattern, int $group = 0): ?array
    {
        if (!isset($value)) {
            return null;
        }

        $ret = preg_match_all($pattern, $value, $matches, PREG_PATTERN_ORDER);

        if ($ret === false) {
            throw new RuntimeError("Error in regular expression: $pattern");
        }

        return $ret > 0 && isset($matches[$group]) ? $matches[$group] : [];
    }

    /**
     * Perform a regular expression match and return an array of entries that match the pattern
     *
     * @param array|null $values
     * @param string $pattern
     * @param string $flags    Optional 'invert' to return entries that do not match the given pattern.
     * @return array
     */
    public function grep(?array $values, string $pattern, string $flags = ''): ?array
    {
        if (!isset($values)) {
            return null;
        }

        if (is_string($flags)) {
            $flags = $flags === 'invert' ? PREG_GREP_INVERT : 0;
        }

        $ret = preg_grep($pattern, $values, $flags);

        if ($ret === false) {
            throw new RuntimeError("Error in regular expression: $pattern");
        }

        return $ret;
    }

    /**
     * Perform a regular expression search and replace.
     *
     * @param string|array|null $value
     * @param string|array $pattern
     * @param string|array $replacement
     * @param int $limit
     * @return string|array|null
     * @throws RuntimeError
     */
    public function replace($value, $pattern, $replacement = '', int $limit = -1)
    {
        $this->assertNoEval(...(array)$pattern);

        if (!isset($value)) {
            return null;
        }

        $ret = preg_replace($pattern, $replacement, $value, $limit);

        if ($ret === null) {
            throw new RuntimeError("Error in regular expression: $pattern");
        }

        return $ret;
    }

    /**
     * Perform a regular expression search and replace, returning only matched subjects.
     *
     * @param string|array|null $value
     * @param string|array $pattern
     * @param string|array $replacement
     * @param int $limit
     * @return string|array|null
     * @throws RuntimeError
     */
    public function filter($value, $pattern, $replacement = '', int $limit = -1)
    {
        $this->assertNoEval(...(array)$pattern);

        if (!isset($value)) {
            return null;
        }

        $ret = preg_filter($pattern, $replacement, $value, $limit);

        if ($ret === null) {
            throw new RuntimeError("Error in regular expression: $pattern");
        }

        return $ret;
    }

    /**
     * Split text into an array using a regular expression.
     */
    public function split(?string $value, string $pattern): array
    {
        if (!isset($value)) {
            return [];
        }

        $ret = preg_split($pattern, $value);

        if ($ret === false) {
            throw new RuntimeError("Error in regular expression: $pattern");
        }

        return $ret;
    }
}
