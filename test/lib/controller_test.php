<?php

# Copyright (c)  2009 - Marcus Lunzenauer <mlunzena@uos.de>
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


class ControllerTestCase extends UnitTestCase {

  function setUp() {
    $this->dispatcher = new PartialMockDispatcher();
    $this->dispatcher->trails_uri = 'http://base';
  }

  function tearDown() {
    unset($this->dispatcher);
  }

  function test_controller_is_instantiable() {
    $controller = new Trails_Controller($this->dispatcher);
    $this->assertNotNull($controller);
    $this->assertIsA($controller, 'Trails_Controller');
  }

  function test_url_for_contains_the_base_url() {
    $controller = new Trails_Controller($this->dispatcher);
    $this->assertPattern('|^http://base/$|', $controller->url_for(''));
  }

  function test_url_for_urlencodes_not_the_first_argument() {
    $controller = new Trails_Controller($this->dispatcher);
    $url = 'wiki/show/one+and+a+half';
    $this->assertEqual('http://base/' . $url,
                       $controller->url_for('wiki/show', 'one and a half'));
  }

  function test_url_for_joins_arguments_with_slashes() {
    $controller = new Trails_Controller($this->dispatcher);
    $url = 'wiki/show/group/main';
    $this->assertEqual('http://base/' . $url,
                       $controller->url_for('wiki/show', 'group', 'main'));
  }

  function test_should_rescue_app_exceptions_in_controller() {

    $controller = new RescueController();
    $controller->__construct($this->dispatcher);

    $this->dispatcher->setReturnValue('parse', array('foo', 'index'));
    $this->dispatcher->setReturnValue('load_controller', $controller);

    $exception = new Exception(__LINE__);
    $controller->throwOn('index_action', $exception);
    $controller->expectOnce('rescue', array($exception));
    $controller->setReturnValue('rescue', new Trails_Response());


    $this->dispatcher->dispatch('/foo/index');
  }

  function test_should_parse_urls_with_format()
  {
    $controller = new Trails_Controller($this->dispatcher);

    $extractions = array(

        'wiki/show/group'
        => array('wiki', array('show', 'group'), NULL)

        , 'wiki/show/group.html'
        => array('wiki', array('show', 'group'), 'html')

        , ''
        => array('index', array(), NULL)

        , 'wiki.json'
        => array('wiki', array(), 'json')

        , 'wiki'
        => array('wiki', array(), NULL)

        # I do not like that empty parameter at all
        , 'wiki/'
        => array('wiki', array(''), NULL)

        # I do not like that empty parameter at all
        , 'wiki///'
        => array('wiki', array('', '', ''), NULL)

        , 'show/id-with-hyphen'
        => array('show', array('id-with-hyphen'), NULL)

        , 'show/id-with-hyphen.html'
        => array('show', array('id-with-hyphen'), 'html')
    );

    foreach ($extractions as $url => $extraction) {
        $this->assertEqual($extraction,
                           $controller->extract_action_and_args($url));
    }
  }
}


class DoesNotUnderstandController extends Trails_Controller {
  function __construct($test) {
    $this->test = $test;
  }

  function does_not_understand($action, $args) {
    $this->test->call();
    $this->performed = TRUE;
  }
}

class DoesNotUnderstandControllerTestCase extends UnitTestCase {
  function call() {
    $this->called = TRUE;
  }

  function test_should_invoke_does_not_understand_with_unknown_action() {
    $this->called = FALSE;
    $controller = new DoesNotUnderstandController($this);
    $controller->perform('gruffelo');
    $this->assertTrue($this->called, "#does_not_understand was not called");
  }
}
