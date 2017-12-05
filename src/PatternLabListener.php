<?php

/*!
 * Twig Extensions Listener Class
 *
 * Copyright (c) 2016 Dave Olsen, http://dmolsen.com
 * Licensed under the MIT license
 *
 * Adds Twig Extensions support to the Twig Pattern Engine
 *
 */

namespace Jasny\Twig;

use \PatternLab\PatternEngine\Twig\TwigUtil;

class PatternLabListener extends \PatternLab\Listener {
  
  /**
  * Add the listeners for this plug-in
  */
  public function __construct() {
    
    $this->addListener("twigPatternLoader.customize","addExtensions");
    
  }
  
  /**
  * Add the extensions to the appropriate instance
  */
  public function addExtensions() {
    
    $instance = TwigUtil::getInstance();
    $instance->addExtension(new \Jasny\Twig\ArrayExtension());
    $instance->addExtension(new \Jasny\Twig\DateExtension());
    $instance->addExtension(new \Jasny\Twig\PcreExtension());
    $instance->addExtension(new \Jasny\Twig\TextExtension());
    TwigUtil::setInstance($instance);
    
  }
  
}
