<?php

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
        if (!$this->file_exists($this->default_controller . '.php')) {
          throw new Trails_MissingFile(
            "Default controller '{$this->default_controller}' not found'");
        }
        $controller_path = $this->default_controller;
        $unconsumed = $uri;
      }

      else {
        list($controller_path, $unconsumed) = $this->parse($uri);
      }

      $controller = $this->load_controller($controller_path);

      $response = $controller->perform($unconsumed);

    } catch (Exception $e) {

      $response = isset($controller) ? $controller->rescue($e)
                                     : $this->trails_error($e);
    }

    return $response;
  }

  function trails_error($exception) {
    ob_clean();

    # show details for local requests
    $detailed = $_SERVER['REMOTE_ADDR'] === '127.0.0.1';

    $body = sprintf('<html><head><title>Trails Error</title></head>'.
                    '<body><h1>%s</h1><pre>%s</pre></body></html>',
                    htmlentities($exception->__toString()),
                    $detailed
                      ? htmlentities($exception->getTraceAsString())
                      : '');

    if ($exception instanceof Trails_Exception) {
      $response = new Trails_Response($body,
                                      $exception->headers,
                                      $exception->getCode(),
                                      $exception->getMessage());
    }
    else {
      $response = new Trails_Response($body, array(), 500,
                                      $exception->getMessage());
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
      throw new Trails_RoutingError("No route matches '$head'");
    }

    $controller = (isset($controller) ? $controller . '/' : '') . $head;

    if ($this->file_exists($controller . '.php')) {
      return array($controller, $tail);
    }
    else if ($this->file_exists($controller)) {
      return $this->parse($tail, $controller);
    }

    throw new Trails_RoutingError("No route matches '$head'");
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
   * Loads the controller file for a given controller path and return an
   * instance of that controller. If an error occures, an exception will be
   * thrown.
   *
   * @param  string            the relative controller path
   *
   * @return TrailsController  an instance of that controller
   */
  function load_controller($controller) {
    require_once "{$this->trails_root}/controllers/{$controller}.php";
    $class = Trails_Inflector::camelize($controller) . 'Controller';
    if (!class_exists($class)) {
      throw new Trails_UnknownController("Controller missing: '$class'");
    }
    return new $class($this);
  }
}

