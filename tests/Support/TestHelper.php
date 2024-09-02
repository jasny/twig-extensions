<?php

namespace Jasny\Twig\Tests\Support;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\ArrayLoader;

/**
 * Helper methods for unit tests
 */
trait TestHelper
{
    /**
     * Get the tested extension
     *
     * @return AbstractExtension;
     */
    abstract protected function getExtension();

    /**
     * Build the Twig environment for the template
     *
     * @param string $template
     * @return Environment
     */
    protected function buildEnv($template)
    {
        $loader = new ArrayLoader([
            'template' => $template,
        ]);
        $twig = new Environment($loader);

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
    protected function render($template, array $data = [])
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
     * @param array  $data
     */
    protected function assertRender($expected, $template, array $data = [])
    {
        $result = $this->render($template, $data);

        $this->assertEquals($expected, (string)$result);
    }
}
