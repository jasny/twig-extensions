<?php

namespace Jasny\Twig;

/**
 * @covers Jasny\Twig\DateExtension
 */
class DateExtensionTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        date_default_timezone_set('UTC');
        \Locale::setDefault("en_EN");
    }
    
    protected function buildEnv($template)
    {
        $loader = new \Twig_Loader_Array(array(
            'template' => $template,
        ));
        
        $twig = new \Twig_Environment($loader);
        $twig->addExtension(new DateExtension());
        return $twig;
    }
    
    protected function process($template, $data = array())
    {
        $twig = $this->buildEnv($template);
        $result = $twig->render('template', $data);
        
        return $result;
    }
    
    public function localDateTimeProvider()
    {
        return [
            ['9/20/2015', "{{ '20-09-2015'|localdate() }}"],
            ['September 20, 2015', "{{ '20-09-2015'|localdate('long') }}"],
            ['9/20/15', "{{ '20-09-2015'|localdate('short') }}"],
            ['11:14 PM', "{{ '23:14:12'|localtime() }}"],
            ['11:14:12 PM GMT', "{{ '23:14:12'|localtime('long') }}"],
            ['11:14 PM', "{{ '23:14:12'|localtime('short') }}"]
        ];
    }
    
    /**
     * @dataProvider localDateTimeProvider
     * 
     * @param string $expected
     * @param string $template
     */
    public function testLocalDateTime($expected, $template)
    {
        $result = $this->process($template);
        
        $this->assertEquals($expected, $result);
    }
}
