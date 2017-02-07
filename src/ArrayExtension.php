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
            'sum' => new \Twig_SimpleFilter('sum', array($this, 'sum')),
            'product' => new \Twig_SimpleFilter('product', array($this, 'product')),
            'values' => new \Twig_SimpleFilter('values', array($this, 'values')),
            'as_array' => new \Twig_SimpleFilter('as_array', array($this, 'asArray')),
            'html_attr' => new \Twig_SimpleFilter('html_attr', array($this, 'HTMLAttributes')),
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
    
    /**
     * Cast an array to an HTML attribute string
     * 
     * @param mixed $array
     * @return string
     */
    public function HTMLAttributes($array)
    {
        if (!isset($array)) return null;
       
        $str = "";
        foreach ($array as $key=>$value) {
            if (!isset($value) || $value === false) continue;
            
            if ($value === true) $value = $key;
            $str .= ' ' . $key . '="' . addcslashes($value, '"') . '"';
        }
        return trim($str);
    }
}
