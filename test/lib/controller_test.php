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
require_once dirname(__FILE__) . '/../../examples/vendor/flexi/lib/flexi.php';
Trails_Tests::setup();

/**
 * Testcase for Controller.
 *
 * @package    trails
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: controller_test.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

Mock::generate('Trails_Dispatcher');

class ControllerTestCase extends UnitTestCase {

  function setUp() {
  }

  function tearDown() {
  }


  function test_controller_is_instantiable() {
    $dispatcher = new MockTrails_Dispatcher();
    $controller = new Trails_Controller($dispatcher, '');
    $this->assertNotNull($controller);
    $this->assertIsA($controller, 'Trails_Controller');
  }

  function test_perform() {
    $dispatcher = new MockTrails_Dispatcher();
    $controller = new Trails_Controller($dispatcher, '');

    var_dump(__METHOD__ . " more todo here");

    #$response = $controller->perform('');

    #var_dump($response);

  }
}
