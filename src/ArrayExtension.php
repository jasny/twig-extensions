<?php

namespace Jasny\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Expose PHP's array functions to Twig
 */
class ArrayExtension extends AbstractExtension
{
    /**
     * Return extension name
     */
    public function getName(): string
    {
        return 'jasny/array';
    }

    /**
     * Callback for Twig
     * @ignore
     */
    public function getFilters()
    {
        return [
            new TwigFilter('sum', [$this, 'sum']),
            new TwigFilter('product', [$this, 'product']),
            new TwigFilter('values', [$this, 'values']),
            new TwigFilter('as_array', [$this, 'asArray']),
            new TwigFilter('html_attr', [$this, 'htmlAttributes']),
        ];
    }


    /**
     * Calculate the sum of values in an array
     */
    public function sum(?array $array): ?int
    {
        return isset($array) ? array_sum((array)$array) : null;
    }

    /**
     * Calculate the product of values in an array
     */
    public function product(?array $array): ?int
    {
        return isset($array) ? array_product((array)$array) : null;
    }

    /**
     * Return all the values of an array or object
     *
     * @param array|object|null $array
     * @return array|null
     */
    public function values($array): ?array
    {
        return isset($array) ? array_values((array)$array) : null;
    }

    /**
     * Cast value to an array
     *
     * @param mixed $value
     * @return array
     */
    public function asArray($value): array
    {
        return is_object($value) ? get_object_vars($value) : (array)$value;
    }

    /**
     * Cast an array to an HTML attribute string
     *
     * @param mixed $array
     * @return string|null
     */
    public function htmlAttributes($array): ?string
    {
        if (!isset($array)) {
            return null;
        }

        $str = "";
        foreach ($array as $key => $value) {
            if (!isset($value) || $value === false) {
                continue;
            }

            if ($value === true) {
                $value = $key;
            }

            $str .= ' ' . $key . '="' . addcslashes($value, '"') . '"';
        }

        return trim($str);
    }
}
