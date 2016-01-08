<?php

namespace Jasny\Twig;

/**
 * Format a date based on the current locale in Twig
 * 
 * @author Arnold Daniels <arnold@jasny.net>
 */
class DateExtension extends \Twig_Extension
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        if (!extension_loaded('intl')) throw new \Exception("Jasny's Date Twig extension requires PHP extension 'intl' (see http://www.php.net/intl).");
    }

    
    /**
     * Return extension name
     * 
     * @return string
     */
    public function getName()
    {
        return 'jasny/date';
    }

    /**
     * Callback for Twig
     * @ignore
     */
    public function getFilters()
    {
        return array(
            'localdate' => new \Twig_SimpleFilter('localdate', array($this, 'localDate')),
            'localtime' => new \Twig_SimpleFilter('localtime', array($this, 'localTime')),
            'localdatetime' => new \Twig_SimpleFilter('localdatetime', array($this, 'localDateTime')),
            'duration' => new \Twig_SimpleFilter('duration', array($this, 'duration')),
            'age' => new \Twig_SimpleFilter('age', array($this, 'age')),
        );
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
     * Format the date value as a string based on the current locale
     * 
     * @param DateTime|int|string $date
     * @param string              $format    null, 'short', 'medium', 'long', 'full' or pattern
     * @param string              $calendar  'gregorian' or 'traditional'
     * @return string
     */
    public function localDate($date, $format=null, $calendar='gregorian')
    {
        if (!isset($date)) return null;
        
        if ($date instanceof \DateTime);
         elseif (is_int($date)) $date = \DateTime::createFromFormat('U', $date);
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
    public function localTime($date, $format='short', $calendar='gregorian')
    {
        if (!isset($date)) return null;
        
        if ($date instanceof \DateTime);
         elseif (is_int($date)) $date = \DateTime::createFromFormat('U', $date);
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
    public function localDateTime($date, $format=null, $calendar='gregorian')
    {
        if (!isset($date)) return null;
        
        if ($date instanceof \DateTime);
         elseif (is_int($date)) $date = \DateTime::createFromFormat('U', $date);
         else $date = new \DateTime((string)$date);

        $calendar = $calendar == 'traditional' ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;
         
        if (is_array($format) || !isset($format)) {
            $format_date = null;
            $format_time = 'short';
            
            extract((array)$format, EXTR_PREFIX_ALL, 'format');
            return $this->localDate($date, $format_date, $calendar) . ' ' . $this->localTime($date, $format_time, $calendar);
        }
        
        list($format, $pattern) = $this->getFormat($format, $calendar);
        
        $df = new \IntlDateFormatter(\Locale::getDefault(), $format, \IntlDateFormatter::SHORT, null, $calendar, $pattern);
        return $df->format($date->getTimestamp());
    }
    

    /**
     * Split duration into seconds, minutes, hours, days, weeks and years.
     * 
     * @param int $seconds
     * @return array
     */
    protected function splitDuration($seconds, $max)
    {
        if ($max < 1 || $seconds < 60) return array($seconds);
        
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        if ($max < 2 || $minutes < 60) return array($seconds, $minutes);
        
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        if ($max < 3 || $hours < 24) return array($seconds, $minutes, $hours);
        
        $days = floor($hours / 24);
        $hours = $hours % 24;
        if ($max < 4 || $days < 7) return array($seconds, $minutes, $hours, $days); 
        
        $weeks = floor($days / 7);
        $days = $days % 7;
        if ($max < 5 || $weeks < 52) return array($seconds, $minutes, $hours, $days, $weeks);
        
        $years = floor($weeks / 52);
        $weeks = $weeks % 52;
        return array($seconds, $minutes, $hours, $days, $weeks, $years); 
    }
    
    /**
     * Calculate duration from seconds.
     * 1 year is seen as exactly 52 weeks.
     * 
     * Use null to skip a unit.
     * 
     * @param int    $seconds    Time in seconds
     * @param array  $units      Time units (seconds, minutes, hours, days, weeks, years)
     * @param string $separator
     * @return string
     */
    public function duration($seconds, $units=array('s', 'm', 'h', 'd', 'w', 'y'), $seperator=' ')
    {
        list($seconds, $minutes, $hours, $days, $weeks, $years) =
            $this->splitDuration($seconds, count($units)-1) + array_fill(0, 6, null);
        
        $duration = '';
        if (isset($years) && isset($units[5]))   $duration .= $seperator . $years . $units[5];
        if (isset($weeks) && isset($units[4]))   $duration .= $seperator . $weeks . $units[4];
        if (isset($days) && isset($units[3]))    $duration .= $seperator . $days . $units[3];
        if (isset($hours) && isset($units[2]))   $duration .= $seperator . $hours . $units[2];
        if (isset($minutes) && isset($units[1])) $duration .= $seperator . $minutes . $units[1];
        if (isset($seconds) && isset($units[0])) $duration .= $seperator . $seconds . $units[0];
        
        return trim($duration, $seperator);
    }

    /**
     * Get the age (in years) based on a date.
     * 
     * @param DateTime|string $date
     * @return int
     */
    public function age($date)
    {
        if (!$date instanceof \DateTime) $date = new \DateTime($date);
        return $date->diff(new \DateTime())->format('%y');
    }
}
