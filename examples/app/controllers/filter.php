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


/**
 * <ClassDescription>
 *
 * @package    <package>
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class FilterController extends Trails_Controller {

  function before_filter(&$action, &$args) {
    if ('filter' == $action) {
      $action = 'filtered';
      $args = array(1, 2, 3);
    }

    else if ('fail' == $action) {
      return FALSE;
    }
  }

  function after_filter($action, $args) {
    $args = func_get_args();
    echo $this->debug(__FUNCTION__, $args);
  }


  function index_action() {
    $args = func_get_args();
    $this->render_text($this->debug(__FUNCTION__, $args));
  }


  function filtered_action() {
    $args = func_get_args();
    $this->render_text($this->debug(__FUNCTION__, $args));
  }

  function debug($method) {
    $args = func_get_args();
    array_shift($args);
    return sprintf("Currently in %s\n<br>\n%s\n<p>\n",
                   $method, var_export($args, 1));
  }
}
