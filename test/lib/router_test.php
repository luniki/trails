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

Mock::generatePartial('Trails_Dispatcher', 'MockDispatcher',
                      array('file_exists'));

class RouterTestCase extends UnitTestCase {

  var $router;

  function setUp() {
    $this->router = new MockDispatcher($this);

    $this->router->setReturnValue('file_exists', TRUE, array('path'));
    $this->router->setReturnValue('file_exists', TRUE, array('path/file.php'));
    $this->router->setReturnValue('file_exists', FALSE);

    $this->router->__construct('', '', '');
  }

  function tearDown() {
    $this->router = NULL;
  }

  function test_route_matches() {
    $this->assertEqual($this->router->parse('path/file'),
                       array('path/file', ''));
  }

  function test_throws_expception_if_route_not_found() {
    $this->expectException();
    $this->router->parse('');
  }

  function test_throws_expception_if_route_only_partially_matches() {
    $this->expectException();
    $this->router->parse('path');
  }
}

