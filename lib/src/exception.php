<?php

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
  function __construct($status = 500, $reason = NULL, $headers = array()) {
    if ($reason === NULL) {
      $reason = Trails_Response::get_reason($status);
    }
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
}


class Trails_DoubleRenderError extends Trails_Exception {

  function __construct() {
    $message =
      "Render and/or redirect were called multiple times in this action. ".
      "Please note that you may only call render OR redirect, and at most ".
      "once per action.";
    parent::__construct(500, $message);
  }
}


class Trails_MissingFile extends Trails_Exception {
  function __construct($message) {
    parent::__construct(500, $message);
  }
}


class Trails_RoutingError extends Trails_Exception {

  function __construct($message) {
    parent::__construct(400, $message);
  }
}


class Trails_UnknownAction extends Trails_Exception {

  function __construct($message) {
    parent::__construct(404, $message);
  }
}


class Trails_UnknownController extends Trails_Exception {

  function __construct($message) {
    parent::__construct(404, $message);
  }
}


class Trails_SessionRequiredException extends Trails_Exception {
  function __construct() {
    $message = "Tried to access a non existing session.";
    parent::__construct(500, $message);
  }
}
