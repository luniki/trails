<?php

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
      $this->send_header(sprintf('HTTP/1.1 %d %s',
                                 $this->status, $this->reason),
                         TRUE,
                         $this->status);
    }

    foreach ($this->headers as $k => $v) {
      $this->send_header("$k: $v");
    }

    echo $this->body;
  }


  /**
   * Internally used function to actually send headers
   *
   * @param  string     the HTTP header
   * @param  bool       optional; TRUE if previously sent header should be
   *                    replaced – FALSE otherwise (default)
   * @param  integer    optional; the HTTP response code
   *
   * @return void
   */
  function send_header($header, $replace = FALSE, $status = NULL) {
    if (isset($status)) {
      header($header, $replace, $status);
    }
    else {
      header($header, $replace);
    }
  }
}

