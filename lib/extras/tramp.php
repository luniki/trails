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


class Tramp_Controller extends Trails_Controller {

  /**
   * Extracts action and args from a string.
   *
   * @param  string       the processed string
   *
   * @return arraye       an array with two elements - a string containing the
   *                      action and an array of strings representing the args
   */
  protected function extract_action_and_args($string) {
    return array($this->get_verb(), explode('/', $string));
  }


  protected function map_action($action) {
    return strtolower($action);
  }


  protected function get_verb() {

    $verb = strtoupper(isset($_REQUEST['_method'])
      ? $_REQUEST['_method'] : @$_SERVER['REQUEST_METHOD']);

    if (!preg_match('/^DELETE|GET|POST|PUT|HEAD|OPTIONS$/', $verb)) {
      throw new Trails_Exception(405);
    }

    return $verb;
  }
}
