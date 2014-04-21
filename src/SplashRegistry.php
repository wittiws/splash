<?php
/*
 * This file is part of the Splash package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Splash;

class SplashRegistry {
  private $classmap = array(
    'inverseregex' => '\\Splash\\Iterator\\InverseRegexIterator',
  );

  static public function go() {
    static $singleton = NULL;
    if (!isset($singleton)) {
      $singleton = new SplashRegistry();
    }
    return $singleton;
  }

  public function getIteratorClass($shortname) {
    $tmp = strtolower($shortname);
    if (isset($this->classmap[$tmp])) {
      return $this->classmap[$tmp];
    }
    return FALSE;
  }

  public function setIteratorClass($shortname, $class) {
    $this->classmap[strtolower($shortname)] = $class;
  }

}