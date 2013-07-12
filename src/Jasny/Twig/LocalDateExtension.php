<?php

namespace Jasny\Twig;

/**
 * Format a date based on the current locale in Twig
 * 
 * @author Arnold Daniels <arnold@jasny.net>
 */
class LocalDateExtension extends \Twig_Extension
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        if (!extension_loaded('intl')) throw new \Exception("The LocalDate Twig extension requires PHP extension 'intl' (see http://www.php.net/intl).");
    }


    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'localdate' => new \Twig_Filter_Method($this, 'formatDate'),
            'localtime' => new \Twig_Filter_Method($this, 'formatTime'),
            'localdatetime' => new \Twig_Filter_Method($this, 'formatDateTime'),
        );
    }

    /**
     * Format the date value as a string based on the current locale
     * 
     * @param DateTime|int|string $date
     * @param string              $format    null, 'short', 'medium', 'long', 'full' or pattern
     * @param string              $calendar  'gregorian' or 'traditional'
     * @return string
     */
    public function formatDate($date, $format=null, $calendar='gregorian')
    {
        if ($date instanceof \DateTime);
         elseif (is_int($date)) $date = \DateTime::createFromFormat('U', $time);
         else $date = new \DateTime((string)$date);
        
        $calendar = $calendar == 'traditional' ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;
        list($format, $pattern) = $this->getFormat($format, $calendar);
        
        $df = new \IntlDateFormatter(\Locale::getDefault(), $format, \IntlDateFormatter::NONE, null, $calendar, $pattern);
        return $df->format($date->getTimestamp());
    }

    /**
     * Format the time value as a string based on the current locale
     * 
     * @param DateTime|int|string $date
     * @param string              $format    'short', 'medium', 'long', 'full' or pattern
     * @param string              $calendar  'gregorian' or 'traditional'
     * @return string
     */
    public function formatTime($date, $format='short', $calendar='gregorian')
    {
        if ($date instanceof \DateTime);
         elseif (is_int($date)) $date = \DateTime::createFromFormat('U', $time);
         else $date = new \DateTime((string)$date);
        
        $calendar = $calendar == 'traditional' ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;
        list($format, $pattern) = $this->getFormat($format, $calendar);
        
        $df = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, $format, null, $calendar, $pattern);
        return $df->format($date->getTimestamp());
    }

    /**
     * Format the date/time value as a string based on the current locale
     * 
     * @param DateTime|int|string $date
     * @param string              $format    date format, pattern or array('date'=>format, 'time'=>format)
     * @param string              $calendar  'gregorian' or 'traditional'
     * @return string
     */
    public function formatDateTime($date, $format=null, $calendar='gregorian')
    {
        if ($date instanceof \DateTime);
         elseif (is_int($date)) $date = \DateTime::createFromFormat('U', $time);
         else $date = new \DateTime((string)$date);

        $calendar = $calendar == 'traditional' ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;
         
        if (is_array($format) || !isset($format)) {
            $format_date = null;
            $format_time = 'short';
            
            extract((array)$format, EXTR_PREFIX_ALL, 'format');
            return $this->formatDate($date, $format_date, $calendar) . ' ' . $this->formatTime($date, $format_time, $calendar);
        }
        
        list($format, $pattern) = $this->getFormat($format, $calendar);
        
        $df = new \IntlDateFormatter(\Locale::getDefault(), $format, \IntlDateFormatter::SHORT, null, $calendar, $pattern);
        return $df->format($date->getTimestamp());
    }

    /**
     * Format the date/time value as a string based on the current locale
     * 
     * @param string $format    null, 'short', 'medium', 'long', 'full' or pattern
     * @param int    $calendar
     * @return array(format, pattern)
     */
    protected function getFormat($format, $calendar=\IntlDateFormatter::GREGORIAN)
    {
        $pattern = null;
        
        switch ($format) {
            case null:     $format = \IntlDateFormatter::SHORT; $pattern = $this->getDefaultDatePattern($calendar); break;
            case 'short':  $format = \IntlDateFormatter::SHORT;  break;
            case 'medium': $format = \IntlDateFormatter::MEDIUM; break;
            case 'long':   $format = \IntlDateFormatter::LONG;   break;
            case 'full':   $format = \IntlDateFormatter::FULL;   break;
            default:       $pattern = $format; $format = \IntlDateFormatter::SHORT; break;
        }
        
        return array($format, $pattern);
    }
    
    /**
     * Default date pattern is short date pattern with 4 digit year
     * 
     * @param int $calendar
     * @return string
     */
    protected function getDefaultDatePattern($calendar=\IntlDateFormatter::GREGORIAN)
    {
        return preg_replace('/\byy?\b/', 'yyyy', \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, null, $calendar)->getPattern());
    }
    
    /**
     * Return extension name
     * 
     * @return string
     */
    public function getName()
    {
        return 'localdate';
    }
}
