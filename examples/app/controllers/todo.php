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


require_once dirname(dirname(__FILE__)) . '/models/todo.php';


class TodoController extends Trails_Controller {


  function before_filter(&$action, &$args) {
    session_start();
    $this->set_layout('layouts/todo');
  }


  function list_action() {
    $this->items = Todo::find_all();
  }


  function edit_action($id = NULL) {
    $this->item = Todo::find($id);
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $this->item->done        = isset($_REQUEST['item']['done']);
      $this->item->description = $_REQUEST['item']['description'];
      $this->item->save();
      $this->redirect('todo/list');
    }
  }


  function add_action() {
    $item = new Todo();
    $item->description = $_REQUEST['item']['description'];
    $item->save();
    $this->redirect('todo/list');
  }


  function delete_action($id) {
    $item = Todo::find($id);
    $item->delete();
    $this->redirect('todo/list');
  }


  function toggle_action($id) {
    $this->item = Todo::find($id);
    $this->item->done = !$this->item->done;
    $this->item->save();
  }
}
