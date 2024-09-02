<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Jasny\Twig;

use DateTime;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Format a date based on the current locale in Twig
 */
class DateExtension extends AbstractExtension
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
     */
    public function getName(): string
    {
        return 'jasny/date';
    }

    /**
     * Callback for Twig to get all the filters.
     *
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('localdate', [$this, 'localDate']),
            new TwigFilter('localtime', [$this, 'localTime']),
            new TwigFilter('localdatetime', [$this, 'localDateTime']),
            new TwigFilter('duration', [$this, 'duration']),
            new TwigFilter('age', [$this, 'age']),
        ];
    }

    /**
     * Turn a value into a DateTime object
     *
     * @param string|int|DateTime $input
     * @return DateTime
     *
     * @throws \DateMalformedStringException
     * @throws RuntimeError
     */
    protected function valueToDateTime($input): DateTime
    {
        if ($input instanceof DateTime) {
            return $input;
        }

        $date = is_int($input) ? DateTime::createFromFormat('U', (string)$input) : new DateTime($input);

        if ($date === false) {
            throw new RuntimeError("Invalid date '$input'");
        }

        return $date;
    }

    /**
     * Get configured intl date formatter.
     */
    protected function getDateFormatter(?string $dateFormat, ?string $timeFormat, string $calendar): \IntlDateFormatter
    {
        $dateType = isset($dateFormat) ? $this->getFormat($dateFormat) : null;
        $timeType = isset($timeFormat) ? $this->getFormat($timeFormat) : null;

        $calendarConst = $calendar === 'traditional' ? \IntlDateFormatter::TRADITIONAL : \IntlDateFormatter::GREGORIAN;

        $pattern = $this->getDateTimePattern(
            $dateType ?? $dateFormat,
            $timeType ?? $timeFormat,
            $calendarConst
        );

        return new \IntlDateFormatter(\Locale::getDefault(), $dateType, $timeType, null, $calendarConst, $pattern);
    }

    /**
     * Format the date/time value as a string based on the current locale
     *
     * @param string $format  'short', 'medium', 'long', 'full', 'none' or false
     * @return int|null
     */
    protected function getFormat(string $format): ?int
    {
        $types = [
            'none' => \IntlDateFormatter::NONE,
            'short' => \IntlDateFormatter::SHORT,
            'medium' => \IntlDateFormatter::MEDIUM,
            'long' => \IntlDateFormatter::LONG,
            'full' => \IntlDateFormatter::FULL
        ];

        return $types[$format] ?? null;
    }

    /**
     * Get the date/time pattern.
     *
     * @param int|string|null $dateType
     * @param int|string|null $timeType
     * @param int $calendar
     * @return string
     */
    protected function getDateTimePattern($dateType, $timeType, int $calendar = \IntlDateFormatter::GREGORIAN): ?string
    {
        if (is_int($dateType) && is_int($timeType)) {
            return null;
        }

        return $this->getDatePattern(
            $dateType ?? \IntlDateFormatter::SHORT,
            $timeType ?? \IntlDateFormatter::SHORT,
            $calendar
        );
    }

    /**
     * Get the formatter to create a date and/or time pattern
     *
     * @param int|string $dateType
     * @param int|string $timeType
     * @param int        $calendar
     * @return \IntlDateFormatter
     */
    protected function getDatePatternFormatter(
        $dateType,
        $timeType,
        int $calendar = \IntlDateFormatter::GREGORIAN
    ): \IntlDateFormatter {
        return \IntlDateFormatter::create(
            \Locale::getDefault(),
            is_int($dateType) ? $dateType : \IntlDateFormatter::NONE,
            is_int($timeType) ? $timeType : \IntlDateFormatter::NONE,
            \IntlTimeZone::getGMT(),
            $calendar
        );
    }

    /**
     * Get the date and/or time pattern
     * Default date pattern is short date pattern with 4 digit year.
     *
     * @param int|string $dateType
     * @param int|string $timeType
     * @param int $calendar
     * @return string
     */
    protected function getDatePattern($dateType, $timeType, int $calendar = \IntlDateFormatter::GREGORIAN): string
    {
        $createPattern =
            (is_int($dateType) && $dateType !== \IntlDateFormatter::NONE) ||
            (is_int($timeType) && $timeType !== \IntlDateFormatter::NONE);

        $pattern = $createPattern ? $this->getDatePatternFormatter($dateType, $timeType, $calendar)->getPattern() : '';

        return trim(
            (is_string($dateType) ? $dateType . ' ' : '') .
            preg_replace('/\byy?\b/', 'yyyy', $pattern) .
            (is_string($timeType) ? ' ' . $timeType : '')
        );
    }

    /**
     * Format the date and/or time value as a string based on the current locale
     *
     * @param DateTime|int|string|null $value
     * @param string|null $dateFormat  null, 'none', 'short', 'medium', 'long', 'full' or pattern
     * @param string|null $timeFormat  null, 'none', 'short', 'medium', 'long', 'full' or pattern
     * @param string $calendar    'gregorian' or 'traditional'
     * @return string|null
     */
    protected function formatLocal(
        $value,
        ?string $dateFormat,
        ?string $timeFormat,
        string $calendar = 'gregorian'
    ): ?string {
        if (!isset($value)) {
            return null;
        }

        $date = $this->valueToDateTime($value);
        $formatter = $this->getDateFormatter($dateFormat, $timeFormat, $calendar);

        return $formatter->format($date->getTimestamp());
    }

    /**
     * Format the date value as a string based on the current locale
     *
     * @param DateTime|int|string|null $date
     * @param string|null $format    null, 'short', 'medium', 'long', 'full' or pattern
     * @param string $calendar  'gregorian' or 'traditional'
     * @return string|null
     */
    public function localDate($date, ?string $format = null, string $calendar = 'gregorian'): ?string
    {
        return $this->formatLocal($date, $format, 'none', $calendar);
    }

    /**
     * Format the time value as a string based on the current locale
     *
     * @param DateTime|int|string|null $date
     * @param string $format    'short', 'medium', 'long', 'full' or pattern
     * @param string $calendar  'gregorian' or 'traditional'
     * @return string|null
     */
    public function localTime($date, string $format = 'short', string $calendar = 'gregorian'): ?string
    {
        return $this->formatLocal($date, 'none', $format, $calendar);
    }

    /**
     * Format the date/time value as a string based on the current locale
     *
     * @param DateTime|int|string|null $date
     * @param string|array|\stdClass|null $format    date format, pattern or ['date'=>format, 'time'=>format)
     * @param string $calendar  'gregorian' or 'traditional'
     * @return string
     */
    public function localDateTime($date, $format = null, string $calendar = 'gregorian'): ?string
    {
        if (is_array($format) || $format instanceof \stdClass || !isset($format)) {
            $formatDate = $format['date'] ?? null;
            $formatTime = $format['time'] ?? 'short';
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
     * @param int $max
     * @return array
     */
    protected function splitDuration(int $seconds, int $max): array
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
     * @param int|null $value     Time in seconds
     * @param array $units     Time units (seconds, minutes, hours, days, weeks, years)
     * @param string $separator
     * @return string
     */
    public function duration(
        ?int $value,
        array $units = ['s', 'm', 'h', 'd', 'w', 'y'],
        string $separator = ' '
    ): ?string {
        if (!isset($value)) {
            return null;
        }

        $parts = $this->splitDuration($value, count($units) - 1) + array_fill(0, 6, null);

        $duration = '';

        for ($i = 5; $i >= 0; $i--) {
            if (isset($parts[$i]) && isset($units[$i])) {
                $duration .= $separator . $parts[$i] . $units[$i];
            }
        }

        return trim($duration, $separator);
    }

    /**
     * Get the age (in years) based on a date.
     *
     * @param DateTime|string|null $value
     * @return int|null
     */
    public function age($value): ?int
    {
        if (!isset($value)) {
            return null;
        }

        $date = $this->valueToDateTime($value);

        return (int)$date->diff(new DateTime())->format('%y');
    }
}
