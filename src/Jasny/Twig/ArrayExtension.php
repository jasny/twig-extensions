<?php

namespace Jasny\Twig;

/**
 * Brings PHP's array functions to Twig
 * 
 * @author Arnold Daniels <arnold@jasny.net>
 */
class ArrayExtension extends \Twig_Extension
{
    /**
     * Return extension name
     * 
     * @return string
     */
    public function getName()
    {
        return 'jasny/array';
    }

    /**
     * Callback for Twig
     * @ignore
     */
    public function getFilters()
    {
        return array(
            'sum' => new \Twig_Filter_Method($this, 'sum'),
            'product' => new \Twig_Filter_Method($this, 'product'),
            'values' => new \Twig_Filter_Method($this, 'values'),
            'as_array' => new \Twig_Filter_Method($this, 'asArray'),
        );
    }
    

    /**
     * Calculate the sum of values in an array
     * 
     * @param array $array
     * @return int
     */
    public function sum($array)
    {
       if (!isset($array)) return null;
       return array_sum((array)$array);
    }
    
    /**
     * Calculate the product of values in an array
     * 
     * @param array $array
     * @return int
     */
    public function product($array)
    {
       if (!isset($array)) return null;
       return array_product((array)$array);
    }
    
    /**
     * Return all the values of an array
     * 
     * @param array $array
     * @return int
     */
    public function values($array)
    {
       if (!isset($array)) return null;
       return array_values((array)$array);
    }
    
    
    /**
     * Cast an object to an array
     * 
     * @param object $object
     * @return array
     */
    public function asArray($object)
    {
        return (array)$object;
    }
}
