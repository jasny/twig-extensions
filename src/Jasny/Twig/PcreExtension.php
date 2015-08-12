<?php

namespace Jasny\Twig;

/**
 * Expose the pcre functions to Twig
 * 
 * @author Arnold Daniels <arnold@jasny.net>
 */
class PcreExtension extends \Twig_Extension
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        if (!extension_loaded('pcre')) throw new \Exception("The Twig PCRE extension requires PHP extension 'pcre' (see http://www.php.net/pcre).");
    }


    /**
     * Callback for Twig
     * @ignore
     */
    public function getFilters()
    {
        return array(
            'preg_quote' => new \Twig_Filter_Method($this, 'quote'),
            'preg_match' => new \Twig_Filter_Method($this, 'match'),
            'preg_get' => new \Twig_Filter_Method($this, 'get'),
            'preg_get_all' => new \Twig_Filter_Method($this, 'getAll'),
            'preg_grep' => new \Twig_Filter_Method($this, 'grep'),
            'preg_replace' => new \Twig_Filter_Method($this, 'replace'),
            'preg_filter' => new \Twig_Filter_Method($this, 'filter'),
            'preg_split' => new \Twig_Filter_Method($this, 'split'),
        );
    }

    /**
     * Check that the regex doesn't use the eval modifier
     * 
     * @param string $pattern
     */
    protected function assertNoEval($pattern)
    {
        if (preg_match('/(.).*\1(.+)$/', trim($pattern), $match) && strpos($match[1], 'e') !== false) throw new \Exception("Using the eval modifier for regular expressions is not allowed");
    }
    

    /**
     * Quote regular expression characters.
     * 
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    public function quote($value, $delimiter = '/')
    {
        if (!isset($value)) return null;
        return preg_quote($value, $delimiter);
    }

    /**
     * Perform a regular expression match.
     * 
     * @param string $value
     * @param string $pattern
     * @return boolean
     */
    public function match($value, $pattern)
    {
        $this->assertNoEval($pattern);
        
        if (!isset($value)) return null;
        return preg_match($pattern, $value);
    }

    /**
     * Perform a regular expression match and return a matched group.
     * 
     * @param string $value
     * @param string $pattern
     * @return string
     */
    public function get($value, $pattern, $group=0)
    {
        $this->assertNoEval($pattern);
        
        if (!isset($value)) return null;
        if (!preg_match($pattern, $value, $matches)) return null;
        return isset($matches[$group]) ? $matches[$group] : null;
    }

    /**
     * Perform a regular expression match and return the group for all matches.
     * 
     * @param string $value
     * @param string $pattern
     * @return array
     */
    public function getAll($value, $pattern, $group=0)
    {
        $this->assertNoEval($pattern);
        
        if (!isset($value)) return null;
        if (!preg_match_all($pattern, $value, $matches, PREG_PATTERN_ORDER)) return array();
        return isset($matches[$group]) ? $matches[$group] : array();
    }

    /**
     * Perform a regular expression match and return an array of entries that match the pattern
     * 
     * @param array  $values
     * @param string $pattern
     * @param strign $flags    Optional 'invert' to return entries that do not match the given pattern.
     * @return array
     */
    public function grep($values, $pattern, $flags='')
    {
        $this->assertNoEval($pattern);
        
        if (!isset($values)) return null;
        
        if (is_string($flags)) $flags = $flags == 'invert' ? PREG_GREP_INVERT : 0;
        return preg_grep($pattern, $values, $flags);
    }

    /**
     * Perform a regular expression search and replace.
     * 
     * @param string $value
     * @param string $pattern
     * @param string $replacement
     * @param int    $limit
     * @return string
     */
    public function replace($value, $pattern, $replacement='', $limit=-1)
    {
        $this->assertNoEval($pattern);
        
        if (!isset($value)) return null;
        return preg_replace($pattern, $replacement, $value, $limit);
    }

    /**
     * Perform a regular expression search and replace, returning only matched subjects.
     * 
     * @param string $value
     * @param string $pattern
     * @param string $replacement
     * @param int    $limit
     * @return string
     */
    public function filter($value, $pattern, $replacement='', $limit=-1)
    {
        $this->assertNoEval($pattern);
        
        if (!isset($value)) return null;
        return preg_filter($pattern, $replacement, $value, $limit);
    }

    /**
     * Split text into an array using a regular expression.
     * 
     * @param string $value
     * @param string $pattern
     * @return array
     */
    public function split($value, $pattern)
    {
        $this->assertNoEval($pattern);
        
        if (!isset($value)) return null;
        return preg_split($pattern, $value);
    }
    
    /**
     * Return extension name
     * 
     * @return string
     */
    public function getName()
    {
        return 'jasny/pcre';
    }
}
