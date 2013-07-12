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
        if (!extension_loaded('pcre')) throw new \Exception("The LocalDate PCRE extension requires PHP extension 'pcre' (see http://www.php.net/pcre).");
    }


    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'preg_match' => new \Twig_Filter_Method($this, 'match'),
            'preg_replace' => new \Twig_Filter_Method($this, 'replace'),
            'preg_split' => new \Twig_Filter_Method($this, 'split'),
        );
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
        return preg_match($pattern, $value);
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
        if (preg_match('/(.).*\1(.+)$/', trim($pattern), $match) && strpos($match[1], 'e') !== false) throw new Exception("Using the eval modifier for regular expressions is not allowed");
        return preg_replace($pattern, $replacement, $value, $limit);
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
        return preg_split($pattern, $value);
    }
    
    /**
     * Return extension name
     * 
     * @return string
     */
    public function getName()
    {
        return 'pcre';
    }
}
