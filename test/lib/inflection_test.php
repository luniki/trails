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

class InflectorTestCase extends UnitTestCase {

  function inflector_strings() {
    $strings = array(
      'hello'               => 'Hello',
      'hello_world'         => 'HelloWorld',
      'h_e_l_l_o'           => 'HELLO',
      'hello123world'       => 'Hello123world',
      'hello/world'         => 'Hello_World',
      'hello/another_world' => 'Hello_AnotherWorld',
      );
    return $strings;
  }

  function test_camelize() {
    foreach ($this->inflector_strings() as $lower_case => $camelized)
      $this->assertEqual($camelized, Trails_Inflector::camelize($lower_case));
  }

  function test_underscore() {
    foreach ($this->inflector_strings() as $lower_case => $camelized)
      $this->assertEqual($lower_case, Trails_Inflector::underscore($camelized));
  }
}

