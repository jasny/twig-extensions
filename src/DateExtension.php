<?php

namespace Jasny\Twig;

/**
 * Format a date based on the current locale in Twig
 */
class DateExtension extends \Twig_Extension
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        if (!extension_loaded('intl')) {
            throw new \Exception("The Date Twig extension requires the 'intl' PHP extension."); // @codeCoverageIgnore
        }
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
     * Callback for Twig to get all the filters.
     * 
     * @return \Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            'localdate' => new \Twig_SimpleFilter('localdate', [$this, 'localDate']),
            'localtime' => new \Twig_SimpleFilter('localtime', [$this, 'localTime']),
            'localdatetime' => new \Twig_SimpleFilter('localdatetime', [$this, 'localDateTime']),
            'duration' => new \Twig_SimpleFilter('duration', [$this, 'duration']),
            'age' => new \Twig_SimpleFilter('age', [$this, 'age']),
        ];
    }

    
    /**
     * Format the date/time value as a string based on the current locale
     * 
     * @param string $format    null, 'short', 'medium', 'long', 'full' or pattern
     * @param int    $calendar
     * @return array [format, pattern)
     */
    protected function getFormat($format, $calendar = \IntlDateFormatter::GREGORIAN)
    {
        if ($format === false) {
            return [\IntlDateFormatter::NONE, null];
        }
        
        $pattern = null;
        
        switch ($format) {
            case null:     $pattern = $this->getDefaultDatePattern($calendar); 
                           $format = \IntlDateFormatter::SHORT; break;
            case 'short':  $format = \IntlDateFormatter::SHORT;  break;
            case 'medium': $format = \IntlDateFormatter::MEDIUM; break;
            case 'long':   $format = \IntlDateFormatter::LONG;   break;
            case 'full':   $format = \IntlDateFormatter::FULL;   break;
            default:       $pattern = $format;
                           $format = \IntlDateFormatter::SHORT; break;
        }
        
        return [$format, $pattern];
    }
    
    /**
     * Default date pattern is short date pattern with 4 digit year
     * 
     * @param int $calendar
     * @return string
     */
    protected function getDefaultDatePattern($calendar=\IntlDateFormatter::GREGORIAN)
    {
        $pattern = \IntlDateFormatter::create(
            \Locale::getDefault(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            \IntlTimeZone::getGMT(),
            $calendar
        )->getPattern();
        
        return preg_replace('/\byy?\b/', 'yyyy', $pattern);
    }

    /**
     * Format the date and/or time value as a string based on the current locale
     * 
     * @param DateTime|int|string $date
     * @param string              $dateFormat  null, 'short', 'medium', 'long', 'full' or pattern
     * @param string              $timeFormat  null, 'short', 'medium', 'long', 'full' or pattern
     * @param string              $calendar    'gregorian' or 'traditional'
     * @return string
     */
    protected function formatLocal($date, $dateFormat, $timeFormat, $calendar = 'gregorian')
    {
        if (!isset($date)) {
            return null;
        }
        
        if (!$date instanceof \DateTime) {
            $date = is_int($date) ? \DateTime::createFromFormat('U', $date) : new \DateTime((string)$date);
        }
        
        $calendarConst = $calendar === 'traditional' ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;
        
        list($datetype, $pattern1) = $this->getFormat($dateFormat, $calendarConst);
        list($timetype, $pattern2) = $this->getFormat($timeFormat, $calendarConst);
        
        $df = new \IntlDateFormatter(
            \Locale::getDefault(),
            $datetype,
            $timetype,
            null,
            $calendarConst,
            $pattern1 ?: $pattern2
        );
        
        return $df->format($date->getTimestamp());
    }

    /**
     * Format the date value as a string based on the current locale
     * 
     * @param DateTime|int|string $date
     * @param string              $format    null, 'short', 'medium', 'long', 'full' or pattern
     * @param string              $calendar  'gregorian' or 'traditional'
     * @return string
     */
    public function localDate($date, $format = null, $calendar = 'gregorian')
    {
        return $this->formatLocal($date, $format, false, $calendar);
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
        return $this->formatLocal($date, false, $format, $calendar);
    }

    /**
     * Format the date/time value as a string based on the current locale
     * 
     * @param DateTime|int|string $date
     * @param string              $format    date format, pattern or ['date'=>format, 'time'=>format)
     * @param string              $calendar  'gregorian' or 'traditional'
     * @return string
     */
    public function localDateTime($date, $format=null, $calendar='gregorian')
    {
        if (is_array($format) || !isset($format)) {
            $formatDate = null;
            $formatTime = 'short';
            
            extract((array)$format, EXTR_PREFIX_ALL, 'format');
        } else {
            $formatDate = $format;
            $formatTime = 'short';
        }
        
        return $this->formatLocal($date, $formatDate, $formatTime, $calendar);
    }
    

    /**
     * Split duration into seconds, minutes, hours, days, weeks and years.
     * 
     * @param int $seconds
     * @return array
     */
    protected function splitDuration($seconds, $max)
    {
        if ($max < 1 || $seconds < 60) {
            return [$seconds];
        }
        
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        if ($max < 2 || $minutes < 60) {
            return [$seconds, $minutes];
        }
        
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        if ($max < 3 || $hours < 24) {
            return [$seconds, $minutes, $hours];
        }
        
        $days = floor($hours / 24);
        $hours = $hours % 24;
        if ($max < 4 || $days < 7) {
            return [$seconds, $minutes, $hours, $days]; 
        }
        
        $weeks = floor($days / 7);
        $days = $days % 7;
        if ($max < 5 || $weeks < 52) {
            return [$seconds, $minutes, $hours, $days, $weeks];
        }
        
        $years = floor($weeks / 52);
        $weeks = $weeks % 52;
        return [$seconds, $minutes, $hours, $days, $weeks, $years];
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
    public function duration($seconds, $units = ['s', 'm', 'h', 'd', 'w', 'y'], $seperator = ' ')
    {
        list($seconds, $minutes, $hours, $days, $weeks, $years) =
            $this->splitDuration($seconds, count($units)-1) + array_fill(0, 6, null);
        
        $duration = '';
        if (isset($years) && isset($units[5])) {
            $duration .= $seperator . $years . $units[5];
        }
        
        if (isset($weeks) && isset($units[4])) {
            $duration .= $seperator . $weeks . $units[4];
        }
        
        if (isset($days) && isset($units[3])) {
            $duration .= $seperator . $days . $units[3];
        }
        
        if (isset($hours) && isset($units[2])) {
            $duration .= $seperator . $hours . $units[2];
        }
        
        if (isset($minutes) && isset($units[1])) {
            $duration .= $seperator . $minutes . $units[1];
        }
        
        if (isset($seconds) && isset($units[0])) {
            $duration .= $seperator . $seconds . $units[0];
        }
        
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
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }
        
        return $date->diff(new \DateTime())->format('%y');
    }
}
