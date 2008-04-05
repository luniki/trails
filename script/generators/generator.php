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
 * @package     <package>
 * @subpackage  <package>
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class Trails_Generator {

  var $log = array();
  var $path = '';
  var $template_factory;


  function Trails_Generator($path, $args) {
    $this->path = $path;
    $this->args = $args;
    $this->template_factory =& new Flexi_TemplateFactory($path);
  }


  function run($commands) {

    $name = array_shift($commands);
    $path = sprintf('%s/%s/%s_generator.php', dirname(__FILE__), $name, $name);
    $klass = Trails_Dispatcher::camelize($name) . 'Generator';

    if (!file_exists($path)) {
      printf("\n".
             "   You must supply a valid generator as the first command.\n\n".
             "   Available generators are:\n\n".
             "   %s\n\n",
             join("\n   ", Trails_Generator::get_generators()));
      return;
    }

    require_once $path;
    $generator =& new $klass($path, $commands);

    if (!sizeof($commands)) {
      echo $generator->usage();
      return;
    }

    $generator->manifest();

    printf("%s\n", join("\n", $generator->log));
  }


  function file($source, $destination) {
    if (file_exists($destination))
      $this->log('exists', $destination);
    else {
      $this->log('create', $destination);
      copy(sprintf('%s/templates/%s', dirname($this->path), $source),
           $destination);
    }
  }


  function template($source, $destination, $attributes) {
    if (file_exists($destination))
      $this->log('exists', $destination);
    else {
      $this->log('create', $destination);
      $name = sprintf('%s/templates/%s', dirname($this->path), $source);
      file_put_contents($destination,
                        $this->template_factory->render($name, $attributes));
    }
  }


  function directory($relative_path) {

    if (file_exists($relative_path))
      $this->log('exists', $relative_path);

    else {
      $dirs = explode('/', $relative_path);
      $parts = array();
      foreach ($dirs as $dir) {
        $parts[] = $dir;
        $path = join('/', $parts);
        $this->log('create', $path);
        if (!is_dir($path) && !mkdir($path))
          return FALSE;
      }
    }

    return TRUE;
  }


  function log($status, $message) {
    $this->log[] = sprintf("%12s  %s", $status, $message);
  }


  function get_generators() {
    $generators = array();
    foreach (glob(dirname(__FILE__) . '/*', GLOB_ONLYDIR) as $file)
      $generators[] = substr(strrchr($file, '/'), 1);
    return $generators;
  }


  function manifest() {
    trigger_error(sprintf('No manifest for "%s" generator.', get_class($this)),
                  E_USER_ERROR);
  }


  function usage() {
    $usage = file_get_contents(dirname($this->path) . '/USAGE');
    return $usage === FALSE ? "Could not locate usage file for this generator.\n"
                            : $usage;
  }
}
