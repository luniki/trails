<?php

# Copyright (c)  2007 - Marcus Lunzenauer <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.


require_once dirname(__FILE__) . '/../trails_tests.php';
Trails_Tests::setup();

/**
 * Testcase for Flash.
 *
 * @package    trails
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: flash_test.php 6258 2007-10-02 11:18:05Z mlunzena $
 */

class FlashTestCase extends UnitTestCase {

  var $flash;

  function setUp() {
    Trails_Flash::fire();
    $this->flash =& Trails_Flash::flash();
  }

  function tearDown() {
    Trails_Flash::flash(null);
    # TODO (mlunzena) a test should not know about this
    unset($_SESSION['trails_flash'], $this->flash);
  }

  function simulateRedirect() {
    Trails_Flash::sweep();
    Trails_Flash::flash(NULL);
    Trails_Flash::fire();
  }

  function test_fire() {
    # remove set up flash
    Trails_Flash::flash(null);
    # TODO (mlunzena) a test should not know about this
    unset($_SESSION['trails_flash']);

    $this->assertNull($this->flash);
    Trails_Flash::fire();
    $this->assertNotNull($this->flash);
  }

  function test_get_set() {
    $this->flash->set('a', 42);
    $this->assertEqual(42, $this->flash->get('a'));
  }

  function test_get_set_ref() {
    $a =& new stdClass();
    $this->flash->set_ref('a', $a);
    $got =& $this->flash->get('a');
    $this->assertReference($got, $a);
  }

  function test_redirect() {

    # set a
    $this->flash->set('a', 42);
    $this->assertEqual(42, $this->flash->get('a'));

    # simulate redirect
    $this->simulateRedirect();

    # a should be here
    $this->assertEqual(42, $this->flash->get('a'));
  }

  function test_double_redirect() {

    # first redirect
    $this->test_redirect();

    # simulate another redirect
    $this->simulateRedirect();

    # a should be deleted
    $this->assertNull($this->flash->get('a'));
  }

  function test_now() {
    # set a
    $this->flash->now('a', 42);
    $this->assertEqual(42, $this->flash->get('a'));

    # simulate redirect
    $this->simulateRedirect();

    # a should be deleted
    $this->assertNull($this->flash->get('a'));
  }

  function test_discard_one() {
    # set a and b
    $this->flash->set('a', 42);
    $this->flash->set('b', 23);
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    # discard a
    $this->flash->discard('a');

    # simulate redirect
    $this->simulateRedirect();

    # a should be deleted, but not b
    $this->assertNull($this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));
  }

  function test_discard_all() {
    # set a and b
    $this->flash->set('a', 42);
    $this->flash->set('b', 23);
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    # discard
    $this->flash->discard();

    # simulate redirect
    $this->simulateRedirect();

    # a and b should be deleted
    $this->assertNull($this->flash->get('a'));
    $this->assertNull($this->flash->get('b'));
  }

  function test_keep_one() {
    # set a and b
    $this->flash->set('a', 42);
    $this->flash->set('b', 23);
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    # simulate 1st redirect
    $this->simulateRedirect();

    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    $this->flash->keep('b');

    # simulate 2nd redirect
    $this->simulateRedirect();

    # a should be deleted, but not b
    $this->assertNull($this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));
  }

  function test_keep_all() {
    # set a and b
    $this->flash->set('a', 42);
    $this->flash->set('b', 23);
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    # simulate 1st redirect
    $this->simulateRedirect();

    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    $this->flash->keep();

    # simulate 2nd redirect
    $this->simulateRedirect();

    # a should be deleted, but not b
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));
  }
}
