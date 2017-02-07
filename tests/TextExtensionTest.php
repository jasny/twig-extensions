<?php

namespace Jasny\Twig;


class TextExtensionTest extends \PHPUnit_Framework_TestCase
{

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
