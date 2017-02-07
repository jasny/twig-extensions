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
            new \Twig_SimpleFilter('localdate', [$this, 'localDate']),
            new \Twig_SimpleFilter('localtime', [$this, 'localTime']),
            new \Twig_SimpleFilter('localdatetime', [$this, 'localDateTime']),
            new \Twig_SimpleFilter('duration', [$this, 'duration']),
            new \Twig_SimpleFilter('age', [$this, 'age']),
        ];
    }

    
    /**
     * Get configured intl date formatter.
     * 
     * @param string|null $dateFormat
     * @param string|null $timeFormat
     * @param string      $calendar
     * @return \IntlDateFormatter
     */
    protected function getDateFormatter($dateFormat, $timeFormat, $calendar)
    {
        $datetype = isset($dateFormat) ? $this->getFormat($dateFormat, $calendar) : null;
        $timetype = isset($timeFormat) ? $this->getFormat($timeFormat, $calendar) : null;
        
        $pattern = null;
        
        if ($datetype === null || $timetype === null) {
            $pattern = $this->getDatePattern(
                isset($datetype) ? $datetype : ($dateFormat ?: \IntlDateFormatter::SHORT),
                isset($timetype) ? $timetype : ($timeFormat ?: \IntlDateFormatter::SHORT)
            );
        }
        
        $calendarConst = $calendar === 'traditional' ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;
        
        return new \IntlDateFormatter(
            \Locale::getDefault(),
            $datetype,
            $timetype,
            null,
            $calendarConst,
            $pattern
        );
    }
    
    /**
     * Format the date/time value as a string based on the current locale
     * 
     * @param string $format    'short', 'medium', 'long', 'full'
     * @return int|null
     */
    protected function getFormat($format)
    {
        switch ($format) {
            case false:    $type = \IntlDateFormatter::NONE; break;
            case 'short':  $type = \IntlDateFormatter::SHORT; break;
            case 'medium': $type = \IntlDateFormatter::MEDIUM; break;
            case 'long':   $type = \IntlDateFormatter::LONG; break;
            case 'full':   $type = \IntlDateFormatter::FULL; break;
            default:       $type = null;
        }
        
        return $type;
    }
    
    /**
     * Default date pattern is short date pattern with 4 digit year
     * 
     * @param int|string $datetype
     * @param int|string $timetype
     * @param int        $calendar
     * @return string
     */
    protected function getDatePattern($datetype, $timetype, $calendar = \IntlDateFormatter::GREGORIAN)
    {
        if (
            (is_int($datetype) && $datetype !== \IntlDateFormatter::NONE) ||
            (is_int($timetype) && $timetype !== \IntlDateFormatter::NONE)
        ){
            $pattern = \IntlDateFormatter::create(
                \Locale::getDefault(),
                is_int($datetype) ? $datetype : \IntlDateFormatter::NONE,
                is_int($timetype) ? $timetype : \IntlDateFormatter::NONE,
                \IntlTimeZone::getGMT(),
                $calendar
            )->getPattern();
        } else {
            $pattern = null;
        }
        
        if (is_string($datetype)) {
            $pattern = trim($datetype . ' ' . $pattern);
        }

        if (is_string($timetype)) {
            $pattern = trim($pattern . ' ' . $timetype);
        }
        
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
        
        $formatter = $this->getDateFormatter($dateFormat, $timeFormat, $calendar);
        
        return $formatter->format($date->getTimestamp());
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
    public function localDateTime($date, $format = null, $calendar = 'gregorian')
    {
        if (is_array($format) || $format instanceof \stdClass || !isset($format)) {
            $formatDate = isset($format['date']) ? $format['date'] : null;
            $formatTime = isset($format['time']) ? $format['time'] : 'short';
        } else {
            $formatDate = $format;
            $formatTime = false;
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
     * One year is seen as exactly 52 weeks.
     * 
     * Use null to skip a unit.
     * 
     * @param int    $value     Time in seconds
     * @param array  $units     Time units (seconds, minutes, hours, days, weeks, years)
     * @param string $seperator
     * @return string
     */
    public function duration($value, $units = ['s', 'm', 'h', 'd', 'w', 'y'], $seperator = ' ')
    {
        if (!isset($value)) {
            return null;
        }
        
        list($seconds, $minutes, $hours, $days, $weeks, $years) =
            $this->splitDuration($value, count($units) - 1) + array_fill(0, 6, null);
        
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
        if (!isset($date)) {
            return null;
        }
        
        if (!$date instanceof \DateTime) {
            $date = is_int($date) ? \DateTime::createFromFormat('U', $date) : new \DateTime((string)$date);
        }
        
        return $date->diff(new \DateTime())->format('%y');
    }
}
