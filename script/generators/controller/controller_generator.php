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
 * @package     trails
 * @subpackage  generators
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class ControllerGenerator extends Trails_Generator {

  function manifest() {

    $controller = current($this->args);
    $actions    = array_slice($this->args, 1);

    $this->directory('app/controllers');
    $this->directory('app/views/'.$controller);

    $this->template('controller.php',
                    sprintf('app/controllers/%s.php', $controller),
                    compact('actions', 'controller'));

    foreach ($actions as $action) {
      $this->template('view.php',
                      sprintf('app/views/%s/%s.php', $controller, $action),
                      compact('action', 'controller'));
    }
  }
}
