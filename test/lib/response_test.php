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


class ResponseTestCase extends UnitTestCase {

  function setUp() {
  }

  function tearDown() {
  }

  function create_response($body = NULL, $headers = NULL, $status = NULL) {
    $response = new PartialMockResponse();

    if (isset($body)) {
      $response->__construct($body, $headers, $status);
    } else {
      $response->__construct();
    }
    return $response;
  }

  function test_should_be_instantiable_wo_parameters() {
    $response = $this->create_response();
    ob_start();
    $response->output();
    $output = ob_get_clean();
    $this->assertEqual($output, '');
    $this->assertEqual(headers_list(), array());
  }

  function test_should_be_instantiable_with_parameters() {
    $response = $this->create_response(
        '<html/>', array('Content-Type' => 'application/xml'), 201);

    $response->expectAt(0, 'sendHeader',
                        array('HTTP/1.1 201 Created', TRUE, 201));
    $response->expectAt(1, 'sendHeader',
                        array('Content-Type: application/xml'));
    $response->expectCallCount('sendHeader', 2);

    ob_start();
    $response->output();
    $this->assertEqual(ob_get_clean(), '<html/>');
  }

  function test_should_send_added_headers() {
    $response = $this->create_response();

    $response->expectOnce('sendHeader',
                          array('Content-Type: application/xml'));

    $response->addHeader('Content-Type', 'application/xml');

    ob_start();
    $response->output();
    ob_end_clean();
  }


  function test_should_send_set_status() {
    $response = $this->create_response();

    $response->expectOnce('sendHeader',
                          array('HTTP/1.1 201 Created', TRUE, 201));

    $response->setStatus(201);

    ob_start();
    $response->output();
    ob_end_clean();
  }
}
