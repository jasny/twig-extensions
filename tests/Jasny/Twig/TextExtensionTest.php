<?php

namespace Jasny\Twig;

use Jasny\Twig\TextExtension;

class TextExtensionTest extends PHPUnit_Framework_TestCase {

    private function buildEnv($template) {
        $loader = new Twig_Loader_Array(array(
            'template' => $template,
        ));
        $twig = new Twig_Environment($loader);
        $twig->addExtension(new TextExtension());
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
        $this->check("<p>foo<br>\nbar</p>", "{{ 'foo\nbar'|paragraph() }}");
    }
    
    public function testLine() {
        $this->check("bar", "{{ 'foo\nbar\nbaz'|line(2) }}");
    }
    
    public function testLess() {
        $this->check("foo..", "{{ 'fooXbarXbaz'|less('..', 'X') }}");
    }
    
    public function testTruncate() {
        $this->check("foo ..", "{{ 'foo bar baz'|truncate(4, '..') }}");
    }
    
}
