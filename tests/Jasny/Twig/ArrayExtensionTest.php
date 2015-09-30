<?php

namespace Jasny\Twig;

use Jasny\Twig\ArrayExtension;

class ArrayExtensionTest extends PHPUnit_Framework_TestCase {

    private function buildEnv($template) {
        $loader = new Twig_Loader_Array(array(
            'template' => $template,
        ));
        $twig = new Twig_Environment($loader);
        $twig->addExtension(new ArrayExtension());
        return $twig;
    }
    
    private function process($template, $data = array()) {
        $twig = $this->buildEnv($template);
        $result = $twig->render('template', $data);
        return $result;
    }
    
    private function check($expected, $template, $data = array()) {
        $result = $this->process($template, $data);
        $this->assertEquals($expected, $result);
    }
    
    public function testSum() {
        $data = array(1, 2, 3, 4);
        $this->check(10, '{{ data|sum() }}', array('data' => $data));
    }

    public function testProduct() {
        $data = array(1, 2, 3, 4);
        $this->check(24, '{{ data|product() }}', array('data' => $data));
    }

    public function testValues() {
        $data = array(1, 2, 3);
        $this->check('1-2-3-', '{% for v in data|values() %}{{v}}-{% endfor %}', array('data' => $data));
    }

    public function testHtmlAttr() {
        $data = array('href' => 'foo.html', 'class' => 'big small');
        $this->check('href="foo.html" class="big small"', '{{ data|html_attr|raw }}', array('data' => $data));
    }

    
}
