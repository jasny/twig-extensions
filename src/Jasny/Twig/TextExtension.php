<?php

namespace Jasny\Twig;

/**
 * Text functions for Twig
 * 
 * @author Arnold Daniels <arnold@jasny.net>
 */
class TextExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'paragraph' => new \Twig_Filter_Method($this, 'paragraph', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'line' => new \Twig_Filter_Method($this, 'line'),
            'more' => new \Twig_Filter_Method($this, 'more', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'truncate' => new \Twig_Filter_Method($this, 'truncate', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'linkify' => new \Twig_Filter_Method($this, 'linkify', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'split' => new \Twig_Filter_Method($this, 'split'),
            'join' => new \Twig_Filter_Method($this, 'join'),
        );
    }

    /**
     * Add paragraph and line breaks to text.
     * 
     * @param string $value
     * @return string
     */
    public function paragraph($value)
    {
        return '<p>' . preg_replace(array('~\n(\s*)\n\s*~', '~(?<!</p>)\n\s*~'), array("</p>\n\$1<p>", "<br>\n"), trim($value)) . '</p>';
    }

    /**
     * Get a single line
     * 
     * @param string $value 
     * @param int    $line   Line number (starts at 1)
     * @return string
     */
    public function line($value, $line=1)
    {
        $lines = explode("\n", $value);
        return isset($lines[$line-1]) ? $lines[$line-1] : null;
    }
    
    /**
     * Cut of text on a pagebreak.
     * 
     * @param string $value
     * @param string $replace
     * @param string $break
     * @return string
     */
    public function more($value, $replace = '...', $break = '<!-- pagebreak -->')
    {
        $pos = stripos($value, $break);
        return $pos === false ? $value : substr($value, 0, $pos) . $replace;
    }

    /**
     * Cut of text if it's to long.
     * 
     * @param string $value
     * @param int    $length
     * @param string $replace
     * @return string
     */
    public function truncate($value, $length, $replace = '...')
    {
        return strlen($value) <= $length ? $value : substr($value, 0, $length - strip_tags($replace)) . $replace;
    }
    
    /**
     * Turn all URLs in clickable links.
     * 
     * @param string $value
     * @param array  $protocols  http/https, ftp, mail, twitter
     * @param array  $attributes
     * @param string $mode       normal or all
     * @return string
     */
    public function linkify($value, $protocols = array('http', 'mail'), array $attributes = array(), $mode = 'normal')
    {
        // Link attributes
        $attr = '';
        foreach ($attributes as $key => $val) {
            $attr .= ' ' . $key . '="' . htmlentities($val) . '"';
        }
        
        $links = array();
        
        // Extract existing links and tags
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);
        
        // Extract text links for each protocol
        foreach ((array)$protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https':   $value = preg_replace_callback($mode != 'all' ? '~(?:(https?)://([^\s<>]+)|(www\.[^\s<>]+?\.[^\s<>]+))(?<![\.,:;\?!\'"\|])~i' : '~(?:(https?)://([^\s<>]+)|([^\s<>]+?\.[^\s<>]+)(?<![\.,:]))~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, '<a' . $attr . ' href="' . $protocol . '://' . $link  . '">' . rtrim($link, '/') . '</a>') . '>'; }, $value); break;
                case 'mail':    $value = preg_replace_callback('~([^\s<>]+?@[^\s<>]+?\.[^\s<>]+)(?<![\.,:;\?!\'"\|])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, '<a' . $attr . ' href="mailto:' . $match[1]  . '">' . $match[1] . '</a>') . '>'; }, $value); break;
                case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, '<a' . $attr . ' href="https://twitter.com/' . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . '">' . $match[0] . '</a>') . '>'; }, $value); break;
                
                default:
                    if (strpos($protocol, ':') === false) $protocol .= in_array($protocol, array('ftp', 'tftp', 'ssh', 'scp'))  ? '://' : ':';
                    $value = preg_replace_callback($mode != 'all' ? '~' . preg_quote($protocol, '~') . '([^\s<>]+?)(?<![\.,:;\?!\'"\|])~i' : '~([^\s<>]+)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, '<a' . $attr . ' href="' . $protocol . $match[1]  . '">' . $match[1] . '</a>') . '>'; }, $value); break;
            }
        }
        
        // Insert all link
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
    }

    /**
     * Split text into an array (explode).
     * 
     * @param string $value
     * @param string $delimiter
     * @return array
     */
    public function split($value, $delimiter="\n")
    {
        return explode($delimiter, $value);
    }
    
    /**
     * Join array elements with a string (implode).
     * 
     * @param array  $value
     * @param string $glue
     * @param boolean $filter
     * @return string
     */
    public function join($value, $glue="\n", $filter=true)
    {
        if ($filter) $value = array_filter($value);
        return join($glue, $value);
    }
    
    /**
     * Return extension name
     * 
     * @return string
     */
    public function getName()
    {
        return 'text';
    }
}
