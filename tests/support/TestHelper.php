<?php

namespace Jasny\Twig;

/**
 * Helper methods for unit tests
 */
trait TestHelper
{
    /**
     * Get the tested extension
     * 
     * @return \Twig_Extension
     */
    abstract protected function getExtension();
    
    /**
     * Build the Twig environment for the template
     * 
     * @param string $template
     * @return \Twig_Environment
     */
    protected function buildEnv($template)
    {
        $loader = new \Twig_Loader_Array([
            'template' => $template,
        ]);
        $twig = new \Twig_Environment($loader);
        
        $twig->addExtension($this->getExtension());
        
        return $twig;
    }
    
    /**
     * Render template
     * 
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function render($template, $data = [])
    {
        $twig = $this->buildEnv($template);
        $result = $twig->render('template', $data);
        
        return $result;
    }
    
    /**
     * Render template and assert equals
     * 
     * @param string $expected
     * @param string $template
     */
    protected function assertRender($expected, $template)
    {
        $result = $this->render($template);
        
        $this->assertEquals($expected, (string)$result);
    }
}
