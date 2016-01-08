<?php

namespace Jasny\Twig;

use Jasny\Twig\PcreExtension;

class PcreExtensionTest extends PHPUnit_Framework_TestCase {

    private function buildEnv($template) {
        $loader = new Twig_Loader_Array(array(
            'template' => $template,
        ));
        $twig = new Twig_Environment($loader);
        $twig->addExtension(new PcreExtension());
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

    public function testQuote() {
        $this->check('foo\(\)', '{{ "foo()"|preg_quote }}');
    }
    
    public function testQuoteDelimiter() {
        $this->check('foo\@bar', '{{ "foo@bar"|preg_quote("@") }}');
    }
    
    public function testPregMatch() {
        $this->check('YES', '{% if "foo"|preg_match("/oo/") %}YES{% else %}NO{% endif %}');
    }

    public function testPregMatchNo() {
        $this->check('NO', '{% if "fod"|preg_match("/oo/") %}YES{% else %}NO{% endif %}');
    }

    /**
     * @expectedException Twig_Error_Runtime
     */
    public function testPregMatchError() {
        $this->check('NO', '{% if "fod"|preg_match("/o//o/") %}YES{% else %}NO{% endif %}');
    }
    
    public function testPregGet() {
        $this->check('d', '{{ "food"|preg_get("/oo(.)/", 1) }}');
    }
    
    public function testPregGetDefault() {
        $this->check('ood', '{{ "food"|preg_get("/oo(.)/") }}');
    }
    
}
