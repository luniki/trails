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

class FooController extends Trails_Controller {
  function index_action() {
  }
}

class ControllerRenderTestCase extends UnitTestCase {
  function setUp() {
    $this->setUpFS();
  }

  function tearDown() {
    stream_wrapper_unregister("var");
  }

  function setUpFS() {
    ArrayFileStream::set_filesystem(array(
      'app' => array(
        'views' => array(
          'layout.php' => '[<?= $content_for_layout ?>]',
          'foo' => array(
            'index.php'  => 'foo/index',
          )
        )
      ),
    ));
    stream_wrapper_register("var", "ArrayFileStream") or die("Failed to register protocol");
  }

  function test_should_render_default_template() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new FooController($dispatcher);
    $response = $controller->perform('index');
    $this->assertEqual($response, new Trails_Response('foo/index'));
  }

  function test_should_render_index_action_when_there_is_no_other() {
    $controller = new FilteringController();
    $controller->expectOnce('before_filter', array('index', array()));
    $controller->setReturnValue('before_filter', FALSE);
    $response = $controller->perform('');
  }

  function test_should_throw_exception_if_action_is_missing() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new FooController($dispatcher);
    $this->expectException('Trails_UnknownAction');
    $response = $controller->perform('missing');
  }

  function test_should_redirect_if_requested() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new Trails_Controller($dispatcher);
    $controller->redirect('where');
    $this->assertEqual($controller->get_response(),
                       new Trails_Response('',
                                           array('Location' => 'trails_uri/where'),
                                           302));
  }

  function test_should_redirect_server_relative_paths() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new Trails_Controller($dispatcher);
    $controller->redirect('/where');
    $this->assertEqual($controller->get_response(),
                       new Trails_Response('',
                                           array('Location' => '/where'),
                                           302));
  }

  function test_should_redirect_to_absolute_urls() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new Trails_Controller($dispatcher);
    $controller->redirect('http://example.com');
    $this->assertEqual($controller->get_response(),
                       new Trails_Response('',
                                           array('Location' => 'http://example.com'),
                                           302));
  }

  function test_should_throw_exception_if_rendering_more_than_once() {
    $controller = new Trails_Controller(NULL);
    $this->expectException('Trails_DoubleRenderError');
    $controller->render_nothing();
    $controller->render_nothing();
  }

  function test_should_throw_exception_if_rendering_and_redirecting() {
    $controller = new Trails_Controller(NULL);
    $this->expectException('Trails_DoubleRenderError');
    $controller->render_nothing();
    $controller->redirect('');
  }

  function test_should_render_template_undecorated_with_implicit_layout() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new Trails_Controller($dispatcher);
    $controller->set_layout('layout');
    $controller->render_template('foo/index');
    $this->assertEqual($controller->get_response(), new Trails_Response('foo/index'));
  }

  function test_should_render_template_decorated_with_explicit_layout() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new Trails_Controller($dispatcher);
    $controller->render_template('foo/index', 'layout');
    $this->assertEqual($controller->get_response(), new Trails_Response('[foo/index]'));
  }

  function test_should_render_action() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new FooController($dispatcher);
    $controller->render_action('index');
    $this->assertEqual($controller->get_response(), new Trails_Response('foo/index'));
  }

  function test_should_render_action_with_layout() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new FooController($dispatcher);
    $controller->set_layout('layout');
    $controller->render_action('index');
    $this->assertEqual($controller->get_response(), new Trails_Response('[foo/index]'));
  }

  function test_respond_to_defaults_to_html() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new FooController($dispatcher);
    $response = $controller->perform('index');
    $this->assertTrue($controller->respond_to('html'));
  }

  function test_respond_to_forced_to_html() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new FooController($dispatcher);
    $response = $controller->perform('index.html');
    $this->assertTrue($controller->respond_to('html'),
      "Controller does not respond to html but to {$controller->format}");
  }

  function test_respond_to_forced_to_xml() {
    $dispatcher = new Trails_Dispatcher('var://app', 'trails_uri', 'default');
    $controller = new FooController($dispatcher);
    $response = $controller->perform('index.xml');
    $this->assertFalse($controller->respond_to('html'));
    $this->assertTrue($controller->respond_to('xml'),
      "Controller does not respond to xml but to {$controller->format}");
  }
}

