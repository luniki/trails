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
 * The version of the trails library.
 */
define('TRAILS_VERSION', '0.5.0');


/**
 * The Dispatcher is used to map an incoming HTTP request to a Controller
 * producing a response which is then rendered. To initialize an instance of
 * class Trails_Dispatcher you have to give three configuration settings:
 *
 *          trails_root - the absolute file path to a directory containing the
 *                        applications controllers, views etc.
 *           trails_uri - the URI to which routes to mapped Controller/Actions
 *                        are appended
 *   default_controller - the route to a controller, that is used if no
 *                        controller is given, that is the route is equal to '/'
 *
 * After instantiation of a dispatcher you have to call method #dispatch with
 * the request uri to be mapped to a controller/action pair.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

class Trails_Dispatcher {

  # TODO (mlunzena) Konfiguration muss anders geschehen

  /**
   * This is the absolute file path to the trails application directory.
   *
   * @access public
   * @var    string
   */
  public $trails_root;


  /**
   * This is the URI to which routes to controller/actions are appended.
   *
   * @access public
   * @var    string
   */
  public $trails_uri;


  /**
   * This variable contains the route to the default controller.
   *
   * @access public
   * @var    string
   */
  public $default_controller;


  /**
   * Constructor.
   *
   * @param  string  absolute file path to a directory containing the
   *                 applications controllers, views etc.
   * @param  string  the URI to which routes to mapped Controller/Actions
   *                 are appended
   * @param  string  the route to a controller, that is used if no
   *                 controller is given, that is the route is equal to '/'
   *
   * @return void
   */
  function __construct($trails_root,
                       $trails_uri,
                       $default_controller) {

    $this->trails_root        = $trails_root;
    $this->trails_uri         = $trails_uri;
    $this->default_controller = $default_controller;
  }


  /**
   * Maps a string to a response which is then rendered.
   *
   * @param string The requested URI.
   *
   * @return void
   */
  function dispatch($uri) {

    $old_handler = set_error_handler(array('Trails_Exception',
                                           'errorHandlerCallback'),
                                     E_ALL);

    ob_start();
    $level = ob_get_level();

    $this->map_uri_to_response($this->clean_uri((string) $uri))->output();

    while (ob_get_level() >= $level) {
      ob_end_flush();
    }

    if (isset($old_handler)) {
      set_error_handler($old_handler);
    }
  }


  /**
   * Maps an URI to a response by figuring out first what controller to
   * instantiate, then delegating the unconsumed part of the URI to the
   * controller who returns an appropriate response object or throws a
   * Trails_Exception.
   *
   * @param  string  the URI string
   *
   * @return mixed   a response object
   */
  function map_uri_to_response($uri) {

    try {

      if ('' === $uri) {
        $controller_path = $this->default_controller;
        $unconsumed = $uri;
      }

      else {
        list($controller_path, $unconsumed) = $this->parse($uri);
      }

      $class = $this->load_controller($controller_path);

      $controller = new $class($this);
      $response = $controller->perform($unconsumed);

    } catch (Exception $e) {

      ob_clean();

      $body = sprintf('<html><head><title>Trails Error</title></head>'.
                      '<body><h1>%s</h1><pre>%s</pre></body></html>',
                      htmlentities($e),
                      htmlentities($e->getTraceAsString()));

      if ($e instanceof Trails_Exception) {
        $response = new Trails_Response($body, $e->headers, $e->getCode(),
                                        $e->getMessage());
      }
      else {
        $response = new Trails_Response($body, array(), 500, $e->getMessage());
      }
    }

    return $response;
  }


  /**
   * Clean up URI string by removing the query part and leading slashes.
   *
   * @param  string  an URI string
   *
   * @return string  the cleaned string
   */
  function clean_uri($uri) {
    if (FALSE !== ($pos = strpos($uri, '?'))) {
      $uri = substr($uri, 0, $pos);
    }
    return ltrim($uri, '/');
  }


  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   * @param  type       <description>
   *
   * @return type       <description>
   */
  function parse($unconsumed, $controller = NULL) {

    list($head, $tail) = $this->split_on_first_slash($unconsumed);

    if (!preg_match('/^\w+$/', $head)) {
      throw new Trails_Exception(400);
    }

    $controller = (isset($controller) ? $controller . '/' : '') . $head;

    if ($this->file_exists($controller . '.php')) {
      return array($controller, $tail);
    }
    else if ($this->file_exists($controller)) {
      return $this->parse($tail, $controller);
    }

    throw new Trails_Exception(404);
  }

  function split_on_first_slash($str) {
    $pos = strpos($str, '/');
    if ($pos !== FALSE) {
      return array(substr($str, 0, $pos), substr($str, $pos + 1));
    }
    return array($str, '');
  }

  function file_exists($path) {
    return file_exists("{$this->trails_root}/controllers/$path");
  }

  /**
   * Loads the controller file for a given controller path and returns the
   * class name of that controller. If an error occures, an exception will be
   * thrown.
   *
   * @param  string  the relative controller path
   *
   * @return mixed   the controller's class name
   */
  function load_controller($controller) {
    require_once "{$this->trails_root}/controllers/{$controller}.php";
    $class = Trails_Inflector::camelize($controller) . 'Controller';
    if (!class_exists($class)) {
      throw new Trails_Exception(501, 'Controller missing: ' . $class);
    }
    return $class;
  }
}



/**
 * This class represents a response returned by a controller that was asked to
 * perform for a given request. A Trails_Response contains the body, status and
 * additional headers which can be renderer back to the client.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

class Trails_Response {


  /**
   * @ignore
   */
  public
    $body = '',
    $status,
    $reason,
    $headers = array();


  /**
   * Constructor.
   *
   * @param  string   the body of the response defaulting to ''
   * @param  array    an array of additional headers defaulting to an
   *                  empty array
   * @param  integer  the status code of the response defaulting to a
   *                  regular 200
   * @param  string   the descriptional reason for a status code defaulting to
   *                  the standard reason phrases defined in RFC 2616
   *
   * @return void
   */
  function __construct($body = '', $headers = array(),
                       $status = NULL, $reason = NULL) {

    $this->set_body($body);

    $this->headers = $headers;

    if (isset($status)) {
      $this->set_status($status, $reason);
    }
  }


  /**
   * Sets the body of the response.
   *
   * @param  string  the body
   *
   * @return mixed   this response object. Useful for cascading method calls.
   */
  function set_body($body) {
    $this->body = $body;
    return $this;
  }


  /**
   * Sets the status code and an optional custom reason. If none is given, the
   * standard reason phrase as of RFC 2616 is used.
   *
   * @param  integer  the status code
   * @param  string   the custom reason, defaulting to the one given in RFC 2616
   *
   * @return mixed    this response object. Useful for cascading method calls.
   */
  function set_status($status, $reason = NULL) {
    $this->status = $status;
    $this->reason = isset($reason) ? $reason : $this->get_reason($status);
    return $this;
  }


  /**
   * Returns the reason phrase of this response according to RFC2616.
   *
   * @param int      the response's status
   *
   * @return string  the reason phrase for this response's status
   */
  function get_reason($status) {
    $reason = array(
      100 => 'Continue', 'Switching Protocols',
      200 => 'OK', 'Created', 'Accepted', 'Non-Authoritative Information',
             'No Content', 'Reset Content', 'Partial Content',
      300 => 'Multiple Choices', 'Moved Permanently', 'Found', 'See Other',
             'Not Modified', 'Use Proxy', '(Unused)', 'Temporary Redirect',
      400 => 'Bad Request', 'Unauthorized', 'Payment Required','Forbidden',
             'Not Found', 'Method Not Allowed', 'Not Acceptable',
             'Proxy Authentication Required', 'Request Timeout', 'Conflict',
             'Gone', 'Length Required', 'Precondition Failed',
             'Request Entity Too Large', 'Request-URI Too Long',
             'Unsupported Media Type', 'Requested Range Not Satisfiable',
             'Expectation Failed',
      500 => 'Internal Server Error', 'Not Implemented', 'Bad Gateway',
             'Service Unavailable', 'Gateway Timeout',
             'HTTP Version Not Supported');

    return isset($reason[$status]) ? $reason[$status] : '';
  }


  /**
   * Adds an additional header to the response.
   *
   * @param  string  the left hand key part
   * @param  string  the right hand value part
   *
   * @return mixed   this response object. Useful for cascading method calls.
   */
  function add_header($key, $value) {
    $this->headers[$key] = $value;
    return $this;
  }


  /**
   * Outputs this response to the client using "echo" and "header".
   *
   * @return void
   */
  function output() {
    if (isset($this->status)) {
      header(sprintf('HTTP/1.1 %d %s', $this->status, $this->reason),
             TRUE, $this->status);
    }

    foreach ($this->headers as $k => $v) {
      header("$k: $v");
    }

    echo $this->body;
  }
}


/**
 * A Trails_Controller is responsible for matching the unconsumed part of an URI
 * to an action using the left over words as arguments for that action. The
 * action is then mapped to method of the controller instance which is called
 * with the just mentioned arguments. That method can send the #render_action,
 * #render_template, #render_text, #render_nothing or #redirect method.
 * Otherwise the #render_action is called with the current action as argument.
 * If the action method sets instance variables during performing, they will be
 * be used as attributes for the flexi-template opened by #render_action or
 * #render_template. A controller's response's body is populated with the output
 * of the #render_* methods. The action methods can add additional headers or
 * change the status of that response.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

class Trails_Controller {


  /**
   * @ignore
   */
  protected
    $dispatcher,
    $response,
    $performed = FALSE,
    $layout;


  /**
   * Constructor.
   *
   * @param  mixed  the dispatcher who creates this instance
   *
   * @return void
   */
  function __construct($dispatcher) {
    $this->dispatcher = $dispatcher;
  }


  /**
   * This method extracts an action string and further arguments from it's
   * parameter. The action string is mapped to a method being called afterwards
   * using the said arguments. That method is called and a response object is
   * generated, populated and sent back to the dispatcher.
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function perform($unconsumed) {

    $this->response = new Trails_Response();

    list($action, $args) = $this->extract_action_and_args($unconsumed);

    # call before filter
    $before_filter_result = $this->before_filter($action, $args);

    # send action to controller
    # TODO (mlunzena) shouldn't the after filter be triggered too?
    if (!(FALSE === $before_filter_result || $this->performed)) {

      $mapped_action = $this->map_action($action);

      # is action callable?
      if (method_exists($this, $mapped_action)) {
        call_user_func_array(array(&$this, $mapped_action), $args);
      }
      else {
        $this->does_not_understand($action, $args);
      }

      if (!$this->performed) {
        $this->render_action($action);
      }

      # call after filter
      $this->after_filter($action, $args);
    }

    return $this->response;
  }


  /**
   * Extracts action and args from a string.
   *
   * @param  string       the processed string
   *
   * @return arraye       an array with two elements - a string containing the
   *                      action and an array of strings representing the args
   */
  protected function extract_action_and_args($string) {

    if ('' === $string) {
      return array('index', array());
    }

    $args = explode('/', $string);
    $action = array_shift($args);
    return array($action, $args);
  }


  /**
   * Maps the action to an actual method name.
   *
   * @param  string  the action
   *
   * @return string  the mapped method name
   */
  protected function map_action($action) {
    return $action . '_action';
  }


  /**
   * Callback function being called before an action is executed. If this
   * function does not return FALSE, the action will be called, otherwise
   * an error will be generated and processing will be aborted. If this function
   * already #rendered or #redirected, further processing of the action is
   * withheld.
   *
   * @param string  Name of the action to perform.
   * @param array   An array of arguments to the action.
   *
   * @return bool
   */
  protected function before_filter(&$action, &$args) {
  }


  /**
   * Callback function being called after an action is executed.
   *
   * @param string Name of the action to perform.
   * @param array  An array of arguments to the action.
   *
   * @return void
   */
  protected function after_filter($action, $args) {
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  protected function does_not_understand($action, $args) {
    throw new Trails_Exception(404, 'Action missing: ' . $action);
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  protected function redirect($to) {

    if ($this->performed) {
      throw new Trails_Exception(500, 'Double Render Error');
    }

    $this->performed = TRUE;

    # get uri
    $url = $this->url_for($to);

    # redirect
    # TODO (mlunzena) quoting necessary??
    $this->response
      ->add_header('Location', $url)
      ->set_body(sprintf('<html><head><meta http-equiv="refresh" content="0;'.
                         'url=%s"/></head></html>', $url));
  }


  /**
   * Renders the given text as the body of the response.
   *
   * @param string  the text to be rendered
   *
   * @return void
   */
  protected function render_text($text = ' ') {

    if ($this->performed) {
      throw new Trails_Exception(500, 'Double Render Error');
    }

    $this->performed = TRUE;

    $this->response->set_body($text);
  }


  /**
   * Renders the empty string as the response's body.
   *
   * @return void
   */
  protected function render_nothing() {
    $this->render_text('');
  }


  /**
   * Renders the template of the given action as the response's body.
   *
   * @param string  the action
   *
   * @return void
   */
  protected function render_action($action) {
    $class = get_class($this);
    $controller_name =
      Trails_Inflector::underscore(substr($class, 0, strlen($class) - 10));

    $this->render_template($controller_name.'/'.$action, $this->layout);
  }


  /**
   * Renders a template using an optional layout template.
   *
   * @param mixed  a flexi template
   * @param mixes  a flexi template which is used as layout
   *
   * @return void
   */
  protected function render_template($template_name, $layout = NULL) {

    # open template
    $factory =
      new Flexi_TemplateFactory($this->dispatcher->trails_root . '/views/');

    $template = $factory->open($template_name);
    if (is_null($template)) {
      throw new Trails_Exception(500, sprintf('No such template: "%s"',
                                              $template_name));
    }

    # template requires setup ?
    switch (get_class($template)) {
      case 'Flexi_JsTemplate':
        $this->set_content_type('text/javascript');
        break;
    }

    $template->set_attributes($this->get_assigned_variables());

    if (isset($layout)) {
      $template->set_layout($layout);
    }

    $this->render_text($template->render());
  }


  /**
   * This method returns all the set instance variables to be used as attributes
   * for a template. This controller is returned too as value for
   * key 'controller'.
   *
   * @return array  an associative array of variables for the template
   */
  protected function get_assigned_variables() {

    $assigns = array();
    $protected = get_class_vars(get_class($this));

    foreach (get_object_vars($this) as $var => $value) {
      if (!array_key_exists($var, $protected)) {
        $assigns[$var] =& $this->$var;
      }
    }

    $assigns['controller'] = $this;

    return $assigns;
  }


  /**
   * Sets the layout to be used by this controller per default.
   *
   * @param  mixed  a flexi template to be used as layout
   *
   * @return void
   */
  protected function set_layout($layout) {
    $this->layout = $layout;
  }


  /**
   * <MethodDescription>
   *
   * @param  string  <description>
   *
   * @return string  <description>
   */
  function url_for($to) {

    $base = $this->dispatcher->trails_uri;

    # absolute URL?
    return preg_match('#^[a-z]+://#', $to)
           ? $to
           : $base . '/' . $to;
  }


  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   *
   * @return type       <description>
   */
  function set_status($status, $reason_phrase = NULL) {
    $this->response->set_status($status, $reason_phrase);
  }


  /**
   * Sets the content type of the controller's response.
   *
   * @param  string  the content type
   *
   * @return void
   */
  protected function set_content_type($type) {
    $this->response->add_header('Content-Type', $type);
  }
}


/**
 * The Inflector class is a namespace for inflections methods.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

class Trails_Inflector {


  /**
   * Returns a camelized string from a lower case and underscored string by
   * replacing slash with underscore and upper-casing each letter preceded
   * by an underscore. TODO
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  static function camelize($word) {
    $parts = explode('/', $word);
    foreach ($parts as $key => $part) {
      $parts[$key] = str_replace(' ', '',
                                 ucwords(str_replace('_', ' ', $part)));
    }
    return join('_', $parts);
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  static function underscore($word) {
    $parts = explode('_', $word);
    foreach ($parts as $key => $part) {
      $parts[$key] = preg_replace('/(?<=\w)([A-Z])/', '_\\1', $part);
    }
    return strtolower(join('/', $parts));
  }
}


/**
 * The flash provides a way to pass temporary objects between actions.
 * Anything you place in the flash will be exposed to the very next action and
 * then cleared out. This is a great way of doing notices and alerts, such as
 * a create action that sets
 * <tt>$flash->set('notice', "Successfully created")</tt>
 * before redirecting to a display action that can then expose the flash to its
 * template.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

class Trails_Flash {


  /**
   * @ignore
   */
  private
    $flash, $used;


  /**
   * Constructor
   *
   * @return void
   */
  private function __construct($flash = array(), $used = array()) {
    $this->flash = $flash;
    $this->used  = $used;
  }


  /**
   * Class field replacement.
   *
   * @param object  the flash to set.
   *
   * @return object the stored flash.
   */
  static function &flash($set = FALSE) {
    static $flash;

    if ($set !== FALSE) {
      $flash = $set;
    }

    return $flash;
  }


  /**
   * Used internally by the <tt>keep</tt> and <tt>discard</tt> methods
   *     use()               # marks the entire flash as used
   *     use('msg')          # marks the "msg" entry as used
   *     use(null, false)    # marks the entire flash as unused
   *                         # (keeps it around for one more action)
   *     use('msg', false)   # marks the "msg" entry as unused
   *                         # (keeps it around for one more action)
   *
   * @param mixed  a key.
   * @param bool   used flag.
   *
   * @return void
   */
  private function _use($k = NULL, $v = TRUE) {
    if ($k) {
      $this->used[$k] = $v;
    }
    else {
      foreach ($this->used as $k => $value) {
        $this->_use($k, $v);
      }
    }
  }


  /**
   * Marks the entire flash or a single flash entry to be discarded by the end
   * of the current action.
   *
   *     $flash->discard()             # discards entire flash
   *                                   # (it'll still be available for the
   *                                   # current action)
   *     $flash->discard('warning')    # discard the "warning" entry
   *                                   # (it'll still be available for the
   *                                   # current action)
   *
   * @param mixed  a key.
   *
   * @return void
   */
  function discard($k = NULL) {
    $this->_use($k);
  }


  /**
   * Marks flash entries as used and expose the flash to the view.
   *
   * @return void
   */
  static function fire() {
    if (!isset($_SESSION['trails_flash'])) {
      $flash =& Trails_Flash::flash(new Trails_Flash());
      $_SESSION['trails_flash'] = array($flash->flash, $flash->used);
    }
    else {
      list($_flash, $_used) = $_SESSION['trails_flash'];
      $flash =& Trails_Flash::flash(new Trails_Flash($_flash, $_used));
    }

    $flash->discard();
  }


  /**
   * Returns the value to the specified key.
   *
   * @param mixed  a key.
   *
   * @return mixed the key's value.
   */
  function &get($k) {
    $return = NULL;
    if (isset($this->flash[$k])) {
      $return =& $this->flash[$k];
    }
    return $return;
  }


  /**
   * Keeps either the entire current flash or a specific flash entry available
   * for the next action:
   *
   *    $flash->keep()           # keeps the entire flash
   *    $flash->keep('notice')   # keeps only the "notice" entry, the rest of
   *                             # the flash is discarded
   *
   * @param mixed  a key.
   *
   * @return void
   */
  function keep($k = NULL) {
    $this->_use($k, FALSE);
  }


  /**
   * Sets a flash that will not be available to the next action, only to the
   * current.
   *
   *    $flash->now('message') = "Hello current action";
   *
   * This method enables you to use the flash as a central messaging system in
   * your app. When you need to pass an object to the next action, you use the
   * standard flash assign (<tt>set</tt>). When you need to pass an object to
   * the current action, you use <tt>now</tt>, and your object will vanish when
   * the current action is done.
   *
   * Entries set via <tt>now</tt> are accessed the same way as standard entries:
   * <tt>$flash->get('my-key')</tt>.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function now($k, $v) {
    $this->discard($k);
    $this->flash[$k] = $v;
  }


  /**
   * Sets a key's value.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function set($k, $v) {
    $this->keep($k);
    $this->flash[$k] = $v;
  }


  /**
   * Sets a key's value by reference.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function set_ref($k, &$v) {
    $this->keep($k);
    $this->flash[$k] =& $v;
  }


  /**
   * Deletes the flash entries that were not marked for keeping.
   *
   * @return void
   */
  function sweep(){

    # no flash, no sweep
    if (!isset($_SESSION['trails_flash'])) {
      return;
    }

    # get flash
    $flash =& Trails_Flash::flash();

    // actually sweep
    $keys = array_keys($flash->flash);
    foreach ($keys as $k) {
      if (!$flash->used[$k]) {
        $flash->_use($k);
      } else {
        unset($flash->flash[$k], $flash->used[$k]);
      }
    }

    // cleanup if someone meddled with flash or used
    $fkeys = array_keys($flash->flash);
    $ukeys = array_keys($flash->used);
    foreach (array_diff($fkeys, $ukeys) as $k => $v) {
      unset($flash->used[$k]);
    }

    // serialize it
    $_SESSION['trails_flash'] = array($flash->flash, $flash->used);
  }
}


/**
 * TODO
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

class Trails_Exception extends Exception {

  /**
   * <FieldDescription>
   *
   * @access private
   * @var <type>
   */
  public $headers;


  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   *
   * @return type       <description>
   */
  function __construct($status, $reason, $headers = array()) {
    parent::__construct($reason, $status);
    $this->headers = $headers;
  }


  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   *
   * @return type       <description>
   */
  function __toString() {
    return "{$this->code} {$this->message}";
  }


  /**
   * <MethodDescription>
   *
   * @param  type       <description>
   *
   * @return type       <description>
   */
  static function errorHandlerCallback($errno, $string, $file, $line, $context) {

    if (!($errno & error_reporting())) {
      return;
    }

    if ($errno == E_NOTICE || $errno == E_WARNING || $errno == E_STRICT) {
      return FALSE;
    }

    $e = new Trails_Exception(500, $string);
    $e->line = $line;
    $e->file = $file;
    throw $e;
  }
}

