<?php

/**
 * A Trails_Controller is responsible for matching the unconsumed part of an URI
 * to an action using the left over words as arguments for that action. The
 * action is then mapped to method of the controller instance which is called
 * with the just mentioned arguments. That method can send the #renderAction,
 * #renderTemplate, #renderText, #renderNothing or #redirect method.
 * Otherwise the #renderAction is called with the current action as argument.
 * If the action method sets instance variables during performing, they will be
 * be used as attributes for the flexi-template opened by #renderAction or
 * #renderTemplate. A controller's response's body is populated with the output
 * of the #render* methods. The action methods can add additional headers or
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
    $this->eraseResponse();
  }


  /**
   * Resets the response of the controller
   *
   * @return void
   */
  function eraseResponse() {
    $this->performed = FALSE;
    $this->response = new Trails_Response();
  }


  /**
   * Return this controller's response
   *
   * @return mixed  the controller's response
   */
  function getResponse() {
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

    list($action, $args) = $this->extractActionAndArgs($unconsumed);

    # call before filter
    $before_filter_result = $this->beforeFilter($action, $args);

    # send action to controller
    # TODO (mlunzena) shouldn't the after filter be triggered too?
    if (!(FALSE === $before_filter_result || $this->performed)) {

      $mapped_action = $this->mapAction($action);

      # is action callable?
      if (method_exists($this, $mapped_action)) {
        call_user_func_array(array(&$this, $mapped_action), $args);
      }
      else {
        $this->doesNotUnderstand($action, $args);
      }

      if (!$this->performed) {
        $this->renderAction($action);
      }

      # call after filter
      $this->afterFilter($action, $args);
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
  function extractActionAndArgs($string) {

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
  function mapAction($action) {
    return Trails_Inflector::camelize($action) . 'Action';
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
  function beforeFilter(&$action, &$args) {
  }


  /**
   * Callback function being called after an action is executed.
   *
   * @param string Name of the action to perform.
   * @param array  An array of arguments to the action.
   *
   * @return void
   */
  function afterFilter($action, $args) {
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  function doesNotUnderstand($action, $args) {
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
           : $this->urlFor($to);

    $this->response->addHeader('Location', $url)->setStatus(302);
  }


  /**
   * Renders the given text as the body of the response.
   *
   * @param string  the text to be rendered
   *
   * @return void
   */
  function renderText($text = ' ') {

    if ($this->performed) {
      throw new Trails_DoubleRenderError();
    }

    $this->performed = TRUE;

    $this->response->setBody($text);
  }


  /**
   * Renders the empty string as the response's body.
   *
   * @return void
   */
  function renderNothing() {
    $this->renderText('');
  }


  /**
   * Renders the template of the given action as the response's body.
   *
   * @param string  the action
   *
   * @return void
   */
  function renderAction($action) {
    $class = get_class($this);
    $controller_name =
      Trails_Inflector::underscore(substr($class, 0, -10));

    $this->renderTemplate($controller_name.'/'.$action, $this->layout);
  }


  /**
   * Renders a template using an optional layout template.
   *
   * @param mixed  a flexi template
   * @param mixes  a flexi template which is used as layout
   *
   * @return void
   */
  function renderTemplate($template_name, $layout = NULL) {

    # open template
    $factory = new Flexi_TemplateFactory($this->dispatcher->trails_root .
                                         '/views/');

    $template = $factory->open($template_name);

    # template requires setup ?
    switch (get_class($template)) {
      case 'Flexi_JsTemplate':
        $this->setContentType('text/javascript');
        break;
    }

    $template->set_attributes($this->getAssignedVariables());

    if (isset($layout)) {
      $template->set_layout($layout);
    }

    $this->renderText($template->render());
  }


  /**
   * This method returns all the set instance variables to be used as attributes
   * for a template. This controller is returned too as value for
   * key 'controller'.
   *
   * @return array  an associative array of variables for the template
   */
  function getAssignedVariables() {

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
  function setLayout($layout) {
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
   *   $url = $controller->urlFor('wiki/show', 'page');
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
   *     $controller->urlFor('wiki/show', 'page');
   *     -> 'wiki/show/page'
   *
   *     $controller->urlFor('wiki/show', 'page', 'one and a half');
   *     -> 'wiki/show/page/one+and+a+half'
   *
   * @param  string   a string containing a controller and optionally an action
   * @param  strings  optional arguments
   *
   * @return string  a URL to this route
   */
  function urlFor($to/*, ...*/) {

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
  function setStatus($status, $reason_phrase = NULL) {
    $this->response->setStatus($status, $reason_phrase);
  }


  /**
   * Sets the content type of the controller's response.
   *
   * @param  string  the content type
   *
   * @return void
   */
  function setContentType($type) {
    $this->response->addHeader('Content-Type', $type);
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
    return $this->dispatcher->trailsError($exception);
  }
}

