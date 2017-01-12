<?php

namespace Jasny\Twig;


class DateExtensionTest extends \PHPUnit_Framework_TestCase  {

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        \Locale::setDefault("en_EN");
    }
    
    private function buildEnv($template) {
        $loader = new \Twig_Loader_Array(array(
            'template' => $template,
        ));
        $twig = new \Twig_Environment($loader);
        $twig->addExtension(new DateExtension());
        return $twig;
    }
    
    private function process($template, $data = array()) {
        $twig = $this->buildEnv($template);
        $result = $twig->render('template', $data);
        return $result;
    }
    
    private function check($expected, $template) {
        $result = $this->process($template);
        $this->assertEquals($expected, $result);
    }
    
    public function testLocalDate() {
        $this->check('9/20/2015', "{{ '20-09-2015'|localdate() }}");
    }

    public function testLocalDateLong() {
        $this->check('September 20, 2015', "{{ '20-09-2015'|localdate('long') }}");
    }

    public function testLocalDateShort() {
        $this->check('9/20/15', "{{ '20-09-2015'|localdate('short') }}");
    }

    public function testLocalTime() {
        $this->check('11:14 PM', "{{ '23:14:12'|localtime() }}");
    }

    public function testLocalTimeLong() {
        $this->check('11:14:12 PM GMT', "{{ '23:14:12'|localtime('long') }}");
    }

    public function testLocalTimeShort() {
        $this->check('11:14 PM', "{{ '23:14:12'|localtime('short') }}");
    }

}
