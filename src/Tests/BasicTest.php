<?php
/*
 * This file is part of the Splash package.
 *
 * (c) Greg Payne
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Splash\Tests;

use Splash\Splash;
class BasicTest extends \PHPUnit_Framework_TestCase {
  public function testBuiltinIterators() {
    Splash::mount();

    // Test array_diff.
    $expected = "1 2 3";
    $test = splash(1, 2, 3, 4)->diff(4, Splash::VALUE)->toArray();
    $actual = join(' ', $test);
    $this->assertEquals($expected, $actual, "Test diff by value");
    $test = splash(1, 2, 3, 4)->diff(array(
      3 => 'delete',
    ), Splash::KEY)->toArray();
    $actual = join(' ', $test);
    $this->assertEquals($expected, $actual, "Test diff by key");
    $test = splash(1, 2, 3, 4)->diff(3, Splash::KEY_ARRAY)->toArray();
    $actual = join(' ', $test);
    $this->assertEquals($expected, $actual, "Test diff by key array");
  }

  public function testMount() {
    // Confirm that multiple calls are safe.
    Splash::mount();
    Splash::mount();
    Splash::mount();

    // Perform a basic test of the splash function.
    $expected = "1 2 3";
    $actual = '';
    foreach (splash(1, 2, 3) as $k) {
      $actual .= ' ' . $k;
    }
    $actual = trim($actual);
    $this->assertEquals($expected, $actual);

    // Test the count function.
    $this->assertEquals(0, splash()->count(), "Test count method in empty case.");
    for ($i = 1; $i <= 10; $i++) {
      $this->assertEquals($i, splash()->appendArray(array_fill(0, $i, 'X'))->count(), "Test count method in basic case.");
    }
    $this->assertEquals(3, splash(1)->push(1)->push(1)->count(), "Test count method with multiple appends.");
  }

  /**
   * @depends testMount
   */
  public function testFilesystem() {
    $match = '@(?:^|/)' . basename(__FILE__) . '$@';
    $flags = \FilesystemIterator::KEY_AS_PATHNAME
      | \FilesystemIterator::CURRENT_AS_FILEINFO;

    // The iterators should easily locate this file.
    $splash = Splash::go()->push(__DIR__);
    $this->assertEquals(1, $splash->count(), "Pushing first item should make count = 1.");
    $allpaths = $splash->recursiveDirectory($flags);
    $this->assertGreaterThanOrEqual(2, $allpaths->count(), "All paths together should be at least 2");
    $paths = $allpaths->regex($match, \RegexIterator::MATCH);
    $this->assertEquals(1, $paths->count(), "There should only be one regex match.");
    $matches = 0;
    foreach ($paths as $path) {
      ++$matches;
      $this->assertEquals(realpath(__FILE__), realpath($path));
    }
    $this->assertEquals(1, $matches);

    // Repeat the first test.
    $splash = Splash::go()->push(__DIR__);
    $this->assertEquals(1, $splash->count(), "Pushing first item should make count = 1.");
    $paths = $splash->recursiveDirectory()->regex($match);
    $matches = 0;
    foreach ($paths as $path) {
      ++$matches;
      $this->assertEquals(realpath(__FILE__), realpath($path));
    }
    $this->assertEquals(1, $matches);

    // Shorthand.
    $matches = 0;
    foreach (splash(__DIR__)->recursiveDirectory($flags)->regex($match) as $path) {
      ++$matches;
      $this->assertEquals(realpath(__FILE__), realpath($path));
    }
    $this->assertEquals(1, $matches);

    // Feed Splash an array.
    $paths = Splash::go()->appendArray(array(
      __DIR__,
    ))->recursiveDirectory()->regex($match);
    $matches = 0;
    foreach ($paths as $path) {
      ++$matches;
      $this->assertEquals(realpath(__FILE__), realpath($path));
    }
    $this->assertEquals(1, $matches);

    // Feed Splash a RecursiveDirectoryIterator.
    $paths = Splash::go()->appendRecursiveDirectory(__DIR__)->regex($match);
    $matches = 0;
    foreach ($paths as $path) {
      ++$matches;
      $this->assertEquals(realpath(__FILE__), realpath($path));
    }
    $this->assertEquals(1, $matches);

    // Feed Splash a RecursiveDirectoryIterator.
    $paths = Splash::go()->appendDirectory(__DIR__)->regex($match);
    $matches = 0;
    foreach ($paths as $path) {
      ++$matches;
      $this->assertEquals(realpath(__FILE__), realpath($path->getPathname()));
    }
    $this->assertEquals(1, $matches);
  }

  public function testUnique() {
    // The iterators should easily locate this file.
    $splash = Splash::go()->push(__DIR__, __DIR__);
    $match = '@' . basename(__FILE__) . '@';
    $paths = $splash->recursiveDirectory()->unique()->regex($match);
    $matches = 0;
    foreach ($paths as $path) {
      ++$matches;
      $this->assertEquals(realpath(__FILE__), realpath($path));
    }
    $this->assertEquals(1, $matches);

    // The iterators should easily locate this file.
    $splash = Splash::go()->appendArray(array(
      __DIR__,
      __DIR__,
    ));
    $match = '@' . basename(__FILE__) . '@';
    $paths = $splash->recursiveDirectory()->unique()->regex($match);
    $matches = 0;
    foreach ($paths as $path) {
      ++$matches;
      $this->assertEquals(realpath(__FILE__), realpath($path));
    }
    $this->assertEquals(1, $matches);
  }

}