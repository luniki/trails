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


class RouterTestCase extends UnitTestCase {

  private $dispatcher;

  function setUp() {
    $this->setUpFS();
    $this->dispatcher = new Trails_Dispatcher("var://app/", "http://trai.ls", "default");
  }

  function tearDown() {
    stream_wrapper_unregister("var");
    unset($this->dispatcher);
  }

  function setUpFS() {
    ArrayFileStream::set_filesystem(array(
      'app' => array(
          'controllers' => array(
              'bar.php' => '<?',
              'foo.php' => '<?',
              'foo' => array(
                  'foobar.php' => '<?'
              ),
              'baz' => array(
                  'file.php' => '<?'
              ),
          ),
      ),
    ));
    stream_wrapper_register("var", "ArrayFileStream") or die("Failed to register protocol");
  }

  function test_route_matches() {
    $this->assertEqual(array('bar', ''),
                       $this->dispatcher->parse('bar'));
  }

  function test_throws_expception_if_route_not_found() {
    $this->expectException('Trails_RoutingError');
    $this->dispatcher->parse('grue');
  }

  function test_throws_expception_if_route_only_partially_matches() {
    $this->expectException('Trails_RoutingError');
    $this->dispatcher->parse('baz');
  }

  function test_parse() {

    $paths = array(
      'bar'                 => array('bar', ''),
      'bar/show'            => array('bar', 'show'),
      'bar/show/1/2'        => array('bar', 'show/1/2'),
      'bar/show///1///2'    => array('bar', 'show///1///2'),
      'foo'                 => array('foo', ''),
      'foo/foobar/list'     => array('foo', 'foobar/list'),
    );

    foreach ($paths as $path => $expected) {
        $this->assertEqual($expected, $this->dispatcher->parse($path));
    }
  }
}

