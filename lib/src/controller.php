<?php

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
    $performed,
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
    $this->erase_response();
  }


  /**
   * Resets the response of the controller
   *
   * @return void
   */
  function erase_response() {
    $this->performed = FALSE;
    $this->response = new Trails_Response();
  }


  /**
   * Return this controller's response
   *
   * @return mixed  the controller's response
   */
  function get_response() {
    return $this->response;
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

    list($action, $args, $format) = $this->extract_action_and_args($unconsumed);

    # set format
    $this->format = isset($format) ? $format : 'html';

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
   * @return array        an array with two elements - a string containing the
   *                      action and an array of strings representing the args
   */
  function extract_action_and_args($string) {

    if ('' === $string) {
      return array('index', array(), NULL);
    }

    // find optional file extension
    $format = NULL;
    if (preg_match('/^(.*[^\/.])\.(\w+)$/', $string, $matches)) {
      list($_, $string, $format) = $matches;
    }

    // TODO this should possibly remove empty tokens
    $args = explode('/', $string);
    $action = array_shift($args);
    return array($action, $args, $format);
  }


  /**
   * Maps the action to an actual method name.
   *
   * @param  string  the action
   *
   * @return string  the mapped method name
   */
  function map_action($action) {
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
  function before_filter(&$action, &$args) {
  }


  /**
   * Callback function being called after an action is executed.
   *
   * @param string Name of the action to perform.
   * @param array  An array of arguments to the action.
   *
   * @return void
   */
  function after_filter($action, $args) {
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  function does_not_understand($action, $args) {
    throw new Trails_UnknownAction("No action responded to '$action'.");
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  function redirect($to) {

    if ($this->performed) {
      throw new Trails_DoubleRenderError();
    }

    $this->performed = TRUE;

    # get uri; keep absolute URIs
    $url = preg_match('#^(/|\w+://)#', $to)
           ? $to
           : $this->url_for($to);

    $this->response->add_header('Location', $url)->set_status(302);
  }


  /**
   * Renders the given text as the body of the response.
   *
   * @param string  the text to be rendered
   *
   * @return void
   */
  function render_text($text = ' ') {

    if ($this->performed) {
      throw new Trails_DoubleRenderError();
    }

    $this->performed = TRUE;

    $this->response->set_body($text);
  }


  /**
   * Renders the empty string as the response's body.
   *
   * @return void
   */
  function render_nothing() {
    $this->render_text('');
  }


  /**
   * Renders the template of the given action as the response's body.
   *
   * @param string  the action
   *
   * @return void
   */
  function render_action($action) {
    $class = get_class($this);
    $controller_name =
      Trails_Inflector::underscore(substr($class, 0, -10));

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
  function render_template($template_name, $layout = NULL) {

    # open template
    $factory = new Flexi_TemplateFactory($this->dispatcher->trails_root .
                                         '/views/');

    $template = $factory->open($template_name);

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
  function get_assigned_variables() {

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
  function set_layout($layout) {
    $this->layout = $layout;
  }


  /**
   * Returns a URL to a specified route to your Trails application.
   *
   * Example:
   * Your Trails application is located at 'http://example.com/dispatch.php'.
   * So your dispatcher's trails_uri is set to 'http://example.com/dispatch.php'
   * If you want the URL to your 'wiki' controller with action 'show' and
   * parameter 'page' you should send:
   *
   *   $url = $controller->url_for('wiki/show', 'page');
   *
   * $url should then contain 'http://example.com/dispatch.php/wiki/show/page'.
   *
   * The first parameter is a string containing the controller and optionally an
   * action:
   *
   *   - "{controller}/{action}"
   *   - "path/to/controller/action"
   *   - "controller"
   *
   * This "controller/action" string is not url encoded. You may provide
   * additional parameter which will be urlencoded and concatenated with
   * slashes:
   *
   *     $controller->url_for('wiki/show', 'page');
   *     -> 'wiki/show/page'
   *
   *     $controller->url_for('wiki/show', 'page', 'one and a half');
   *     -> 'wiki/show/page/one+and+a+half'
   *
   * @param  string   a string containing a controller and optionally an action
   * @param  strings  optional arguments
   *
   * @return string  a URL to this route
   */
  function url_for($to/*, ...*/) {

    # urlencode all but the first argument
    $args = func_get_args();
    $args = array_map('urlencode', $args);
    $args[0] = $to;

    return $this->dispatcher->trails_uri . '/' . join('/', $args);
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
  function set_content_type($type) {
    $this->response->add_header('Content-Type', $type);
  }


  /**
   * Exception handler called when the performance of an action raises an
   * exception.
   *
   * @param  object     the thrown exception
   *
   * @return object     a response object
   */
  function rescue($exception) {
    return $this->dispatcher->trails_error($exception);
  }

  function respond_to($ext) {
    return $this->format === $ext;
  }
}
