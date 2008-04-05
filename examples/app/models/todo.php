<?php

# Copyright (c) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
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

class Todo {


  public $id = NULL, $description = '', $done = FALSE;


  static function &cache() {
    if (!isset($_SESSION['todo'])) {
      $_SESSION['todo'] = array(
        1 => new Todo(array('id' => 1, 'description' => 'Call Tobias', 'done' => TRUE)),
        2 => new Todo(array('id' => 2, 'description' => 'Call Marco')),
        3 => new Todo(array('id' => 3, 'description' => 'Call Elmar')));
    }
    return $_SESSION['todo'];
  }


  function __construct($array = array()) {
    foreach ($array as $key => $value) {
      $this->$key = $value;
    }
  }


  static function &find_all() {
    return Todo::cache();
  }


  static function &find($id) {
    $items =& Todo::cache();
    return $items[$id];
  }

  function save() {
    $items =& Todo::cache();
    if (is_null($this->id)) {
      $this->id = max(array_keys($items)) + 1;
    }
    $items[$this->id] = $this;
    return TRUE;
  }

  function delete() {
    $items =& Todo::cache();
    if (is_null($this->id)) {
      return FALSE;
    }
    unset($items[$this->id]);
    return TRUE;
  }
}
