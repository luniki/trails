<?php

/*
 * prototype_helper.php - Help with javascripts.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * PrototypeHelper.
 *
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    David Heinemeier Hansson
 * @copyright (c) Authors
 * @version   $Id: prototype_helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class PrototypeHelper {

  /**
   * Returns a link to a remote action defined by 'url' (using the 'url_for()'
   * format) that's called in the background using XMLHttpRequest. The result of
   * that request can then be inserted into a DOM object whose id can be
   * specified with 'update'.
   *
   * Examples:
   *  link_to_remote('Delete this post',
   *                 array('update' => 'posts', 'url' => 'destroy?id='.$id))
   *
   * You can also specify a hash for 'update' to allow for
   * easy redirection of output to an other DOM element if a server-side error
   * occurs:
   *
   * Example:
   *  link_to_remote('Delete this post',
   *                 array('update' => array('success' => 'posts',
   *                                         'failure' => 'error'),
   *                       'url' => 'destroy?id='.$id))
   *
   * Optionally, you can use the 'position' parameter to influence
   * how the target DOM element is updated. It must be one of 'before', 'top',
   * 'bottom', or 'after'.
   *
   * By default, these remote requests are processed asynchronous during
   * which various JavaScript callbacks can be triggered (for progress
   * indicators and the likes). All callbacks get access to the 'request'
   * object, which holds the underlying XMLHttpRequest.
   *
   * The callbacks that may be specified are (in order):
   *
   * 'loading'                 Called when the remote document is being
   *                           loaded with data by the browser.
   * 'loaded'                  Called when the browser has finished loading
   *                           the remote document.
   * 'interactive'             Called when the user can interact with the
   *                           remote document, even though it has not
   *                           finished loading.
   * 'success'                 Called when the XMLHttpRequest is completed,
   *                           and the HTTP status code is in the 2XX range.
   * 'failure'                 Called when the XMLHttpRequest is completed,
   *                           and the HTTP status code is not in the 2XX
   *                           range.
   * 'complete'                Called when the XMLHttpRequest is complete
   *                           (fires after success/failure if present).
   *
   * You can further refine 'success' and 'failure' by adding additional
   * callbacks for specific status codes:
   *
   * Example:
   *   link_to_remote($word, array('url' => $rule,
   *                               '404' => "alert('Not found...?')",
   *                               'failure' => "alert('HTTPError!')"))
   *
   * A status code callback overrides the success/failure handlers if present.
   *
   * If you for some reason or another need synchronous processing (that'll
   * block the browser while the request is happening), you can specify
   * 'type' => 'synchronous'.
   *
   * You can customize further browser side call logic by passing
   * in JavaScript code snippets via some optional parameters. In
   * their order of use these are:
   *
   * 'confirm'             Adds confirmation dialog.
   * 'condition'           Perform remote request conditionally
   *                       by this expression. Use this to
   *                       describe browser-side conditions when
   *                       request should not be initiated.
   * 'before'              Called before request is initiated.
   * 'after'               Called immediately after request was
   *                       initiated and before 'loading'.
   * 'submit'              Specifies the DOM element ID that's used
   *                       as the parent of the form elements. By
   *                       default this is the current form, but
   *                       it could just as well be the ID of a
   *                       table row or any other DOM element.
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function link_to_remote($name, $options = array(), $html_options = array()) {
    return JsHelper::link_to_function($name,
      PrototypeHelper::remote_function($options), $html_options);
  }

  /**
   * Periodically calls the specified url ['url'] every ['frequency'] seconds
   * (default is 10). Usually used to update a specified div ['update'] with the
   * results of the remote call. The options for specifying the target with
   * 'url' and defining callbacks is the same as 'link_to_remote()'.
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function periodically_call_remote($options = array()) {
    $frequency = isset($options['frequency']) ? $options['frequency'] : 10;
    $code = sprintf('new PeriodicalExecuter(function() {%s}, %d)',
                    PrototypeHelper::remote_function($options), $frequency);
    return JsHelper::javascript_tag($code);
  }

  /**
   * Returns a form tag that will submit using XMLHttpRequest in the background
   * instead of the regular reloading POST arrangement. Even though it's using
   * JavaScript to serialize the form elements, the form submission will work
   * just like a regular submission as viewed by the receiving side
   * (all elements available in 'params'). The options for specifying the target
   * with 'url' and defining callbacks are the same as 'link_to_remote()'.
   *
   * A "fall-through" target for browsers that don't do JavaScript can be
   * specified with the 'action' and 'method' options on '$html_options'.
   *
   * Example:
   *   form_remote_tag(array(
   *     'url'      => 'tag_add',
   *     'update'   => 'question_tags',
   *     'loading'  => "Element.show('indicator'); tag.value = ''",
   *     'complete' => "Element.hide('indicator');".
   *                   visual_effect('highlight', 'question_tags')))
   *
   * The hash passed as a second argument is equivalent to the options (2nd)
   * argument in the form_tag() helper.
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function form_remote_tag($options = array(), $html_options = array()) {

    $options = TagHelper::_parse_attributes($options);
    $html_options = TagHelper::_parse_attributes($html_options);

    $options['form'] = TRUE;

    $html_options['onsubmit'] = PrototypeHelper::remote_function($options).
                                '; return false;';
    $html_options['action'] = isset($html_options['action'])
                              ? $html_options['action']
                              : $options['url'];

    $html_options['method'] = isset($html_options['method'])
                              ? $html_options['method']
                              : 'post';

    return TagHelper::tag('form', $html_options, TRUE);
  }

  /**
   * Returns a button input tag that will submit form using XMLHttpRequest in
   * the background instead of regular reloading POST arrangement. The '$opt'
   * argument is the same as in 'form_remote_tag()'.
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function submit_to_remote($name, $value, $options = array()) {

    if (!isset($options['with']))
      $options['with'] = 'Form.serialize(this.form)';

    if (!isset($options['html']))
      $options['html'] = array();

    $options['html']['type'] = 'button';
    $options['html']['onclick'] = PrototypeHelper::remote_function($options).
                                  '; return false;';
    $options['html']['name'] = $name;
    $options['html']['value'] = $value;

    return TagHelper::tag('input', $options['html']);
  }

  /**
   * Returns a Javascript function (or expression) that will update a DOM
   * element '$element_id' according to the '$opt' passed.
   *
   * Possible '$opt' are:
   * 'content'    The content to use for updating.
   * 'action'     Valid options are 'update' (default), 'empty', 'remove'
   * 'position'   If the 'action' is 'update', you can optionally specify one of
   *              the following positions: 'before', 'top', 'bottom', 'after'.
   *
   * Example:
   *   update_element_function('products',
   *                           array('position' => 'bottom',
   *                                 'content'  => "<p>New product!</p>"));
   *
   *
   * This method can also be used in combination with remote method call
   * where the result is evaluated afterwards to cause multiple updates on a
   * page.
   *
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function update_element_function($element_id, $options = array()) {

    $content = JsHelper::escape_javascript(isset($options['content'])
                                           ? $options['content']
                                           : '');

    $value = isset($options['action']) ? $options['action'] : 'update';

    switch ($value) {

      case 'update':
        $js_func = $options['position']
                   ? sprintf("new Insertion.%s('%s','%s')",
                             TextHelper::camelize($options['position']),
                             $element_id, $content)
                   : "\$('$element_id').innerHTML = '$content'";
        break;

      case 'empty':
        $js_func = "\$('$element_id').innerHTML = ''";
        break;

      case 'remove':
        $js_func = "Element.remove('$element_id')";
        break;

      default:
        trigger_error('Invalid action, choose one of update, remove, empty');
        exit;
    }

    $js_func .= ";\n";

    return isset($options['binding']) ? $js_func.$options['binding'] : $js_func;
  }

  /**
   * Returns 'eval(request.responseText)', which is the Javascript function that
   * 'form_remote_tag()' can call in 'complete' to evaluate a multiple update
   * return document using 'update_element_function()' calls.
   *
   * @return type <description>
   */
  function evaluate_remote_response() {
    return 'eval(request.responseText)';
  }

  /**
   * Returns the javascript needed for a remote function.
   * Takes the same arguments as 'link_to_remote()'.
   *
   * Example:
   *   <select id="options" onchange="<?=
   *     remote_function(array('update' => 'options',
   *                           'url' => '@update_options')) ?>">
   *     <option value="0">Hello</option>
   *     <option value="1">World</option>
   *   </select>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function remote_function($options) {
    $javascript_options = PrototypeHelper::options_for_ajax($options);

    $update = '';
    if (isset($options['update']) && is_array($options['update'])) {

      $update = array();
      if (isset($options['update']['success']))
        $update[] = "success:'".$options['update']['success']."'";

      if (isset($options['update']['failure']))
        $update[] = "failure:'".$options['update']['failure']."'";

      $update = '{'.join(',', $update).'}';

    } else if (isset($options['update'])) {
      $update .= "'".$options['update']."'";
    }

    $function = sprintf("new Ajax.%s(%s'%s', %s)",
                        $update ? 'Updater' : 'Request',
                        $update ? "$update, " : '',
                        $options['url'],
                        $javascript_options);

    if (isset($options['before']))
      $function = $options['before'].'; '.$function;

    if (isset($options['after']))
      $function = $function.'; '.$options['after'];

    if (isset($options['condition']))
      $function = 'if ('.$options['condition'].') { '.$function.'; }';

    if (isset($options['confirm'])) {
      $function = "if (confirm('" .
                  JsHelper::escape_javascript($options['confirm']) .
                  "')) { $function; }";
      if (isset($options['cancel']))
        $function .= ' else { '.$options['cancel'].' }';
    }

    return $function;
  }

  /**
   * Observes the field with the DOM ID specified by '$field_id' and makes
   * an AJAX call when its contents have changed.
   *
   * Required '$options' are:
   * 'url'                 'url_for()'-style options for the action to call
   *                       when the field has changed.
   *
   * Additional options are:
   * 'frequency'           The frequency (in seconds) at which changes to
   *                       this field will be detected. Not setting this
   *                       option at all or to a value equal to or less than
   *                       zero will use event based observation instead of
   *                       time based observation.
   * 'update'              Specifies the DOM ID of the element whose
   *                       innerHTML should be updated with the
   *                       XMLHttpRequest response text.
   * 'with'                A JavaScript expression specifying the
   *                       parameters for the XMLHttpRequest. This defaults
   *                       to 'value', which in the evaluated context
   *                       refers to the new field value.
   *
   * Additionally, you may specify any of the options documented in
   * link_to_remote().
   *
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function observe_field($field_id, $options = array()) {
    $name = isset($options['frequency']) && $options['frequency'] > 0
            ? 'Form.Element.Observer'
            : 'Form.Element.EventObserver';
    return PrototypeHelper::build_observer($name, $field_id, $options);
  }

  /**
   * Like 'observe_field()', but operates on an entire form identified by the
   * DOM ID '$form_id'. '$options' are the same as 'observe_field()', except
   * the default value of the 'with' option evaluates to the
   * serialized (request string) value of the form.
   *
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function observe_form($form_id, $options = array()) {
    $name = isset($options['frequency']) && $options['frequency'] > 0
            ? 'Form.Observer'
            : 'Form.EventObserver';
    return PrototypeHelper::build_observer($name, $form_id, $options);
  }

  /**
   * @ignore
   */
  function options_for_ajax($options) {
    $js_opt = PrototypeHelper::build_callbacks($options);

    $js_opt['asynchronous'] = isset($options['type'])
                              ? $options['type'] != 'synchronous' : 'true';

    if (isset($options['method']))
      $js_opt['method'] = PrototypeHelper::method_option_to_s($options['method']);

    if (isset($options['position']))
      $js_opt['insertion'] = 'Insertion.'.TextHelper::camelize($options['position']);

    $js_opt['evalScripts'] = !isset($options['script']) ||
                             $options['script'] == '0' ||
                             $options['script'] == 'false'
                             ? 'false' : 'true';

    if (isset($options['form']))
      $js_opt['parameters'] = 'Form.serialize(this)';
    else if (isset($options['submit']))
      $js_opt['parameters'] = "Form.serialize(document.getElementById('{$options['submit']}'))";
    else if (isset($options['with']))
      $js_opt['parameters'] = $options['with'];

    return JsHelper::options_for_javascript($js_opt);
  }

  /**
   * @ignore
   */
  function method_option_to_s($method) {
    return is_string($method) && $method[0] == "'" ? $method : "'$method'";
  }

  /**
   * @ignore
   */
  function build_observer($klass, $name, $options = array()) {
    if (!isset($options['with']) && $options['update'])
      $options['with'] = 'value';

    $callback = PrototypeHelper::remote_function($options);

    $javascript  = 'new '.$klass.'("'.$name.'", ';
    if (isset($options['frequency']))
      $javascript .= $options['frequency'].", ";

    $javascript .= 'function(element, value) {';
    $javascript .= $callback.'});';

    return JsHelper::javascript_tag($javascript);
  }

  /**
   * @ignore
   */
  function build_callbacks($options) {
    $callbacks = array();
    foreach (PrototypeHelper::get_callbacks() as $callback) {
      if (isset($options[$callback])) {
        $name = 'on'.ucfirst($callback);
        $code = $options[$callback];
        $callbacks[$name] = 'function(request, json){'.$code.'}';
      }
    }

    return $callbacks;
  }

  /**
   * @ignore
   */
  function get_callbacks() {
    static $callbacks;
    if (!$callbacks)
      $callbacks = array_merge(range(100, 599),
                               array('uninitialized', 'loading', 'loaded',
                                     'interactive', 'complete', 'failure',
                                     'success'));
    return $callbacks;
  }

  /**
   * @ignore
   */
  function get_ajax_options() {
    static $ajax_options;
    if (!$ajax_options)
      $ajax_options = array('before', 'after', 'condition', 'url',
                            'asynchronous', 'method', 'insertion', 'position',
                            'form', 'with', 'update', 'script')
                      + PrototypeHelper::get_callbacks();
    return $ajax_options;
  }
}

/**
 * JavaScriptGenerator generates blocks of JavaScript code that allow you to
 * change the content and presentation of multiple DOM elements. Use this in
 * your Ajax response bodies, either in a <script> tag or as plain JavaScript
 * sent with a Content-type of "text/javascript".
 *
 * @package   flexi
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id: prototype_helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class Flexi_JavascriptGenerator {

  /**
   * internal variables
   * @ignore
   */
  var $lines = array();

  /**
   * @ignore
   */
  function to_s() {
    $javascript = implode("\n", $this->lines);
    return "try {\n".$javascript."\n} catch (e) { ".
           "alert('JS error:\\n\\n' + e.toString()); throw e }";
  }

################################################################################
# function []
# function select
# function draggable
# function drop_receiving
# function sortable
################################################################################

  /**
   * Inserts HTML at the specified 'position' relative to the DOM element
   * identified by the given 'id'.
   *
   * 'position' may be one of:
   *
   * 'top'::    HTML is inserted inside the element, before the
   *            element's existing content.
   * 'bottom':: HTML is inserted inside the element, after the
   *            element's existing content.
   * 'before':: HTML is inserted immediately preceeding the element.
   * 'after'::  HTML is inserted immediately following the element.
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function insert_html($position, $id, $content) {
    $insertion = TextHelper::camelize($position);
    $this->call('new Insertion.'.$insertion, $id, $content);
  }

  /**
   * Replaces the inner HTML of the DOM element with the given 'id'.
   *
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function replace_html($id, $content) {
    $this->call('Element.update', $id, $content);
  }

  /**
   * Replaces the "outer HTML" (i.e., the entire element, not just its
   * contents) of the DOM element with the given 'id'.
   *
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  function replace($id, $content) {
    $this->call('Element.replace', $id, $content);
  }

  /**
   * Removes the DOM elements with the given 'ids' from the page.
   *
   * @param type <description>
   *
   * @return void
   */
  function remove($ids) {
    $ids = func_get_args();
    $this->record($this->javascript_object_for($ids).".each(Element.remove)");
  }

  /**
   * Shows hidden DOM elements with the given 'ids'.
   *
   * @param type <description>
   *
   * @return void
   */
  function show($ids) {
    $ids = func_get_args();
    array_unshift($ids, 'Element.show');
    call_user_func_array(array(&$this, 'call'), $ids);
  }

  /**
   * Hides the visible DOM elements with the given 'ids'.
   *
   * @param type <description>
   *
   * @return void
   */
  function hide($ids) {
    $ids = func_get_args();
    array_unshift($ids, 'Element.hide');
    call_user_func_array(array(&$this, 'call'), $ids);
  }

  /**
   * Toggles the visibility of the DOM elements with the given 'ids'.
   *
   * @param type <description>
   *
   * @return void
   */
  function toggle($ids) {
    $ids = func_get_args();
    array_unshift($ids, 'Element.toggle');
    call_user_func_array(array(&$this, 'call'), $ids);
  }

  /**
   * Displays an alert dialog with the given 'message'.
   *
   * @param string the given message.
   *
   * @return void
   */
  function alert($message) {
    $this->call('alert', $message);
  }

  /**
   * Redirects the browser to the given 'location'.
   *
   * @param type <description>
   *
   * @return void
   */
  function redirect_to($location) {
    $this->assign('window.location.href', $location);
  }

  /**
   * Calls the JavaScript 'function', optionally with the given 'arguments'.
   *
   * @param type <description>
   *
   * @return void
   */
  function call($function) {
    $arguments = func_get_args();
    array_shift($arguments);
    $this->record($function.'('.$this->arguments_for_call($arguments).')');
  }

  /**
   * Assigns the JavaScript 'variable' the given 'value'.
   *
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  function assign($variable, $value) {
    $this->record($variable.' = '.$this->javascript_object_for($value));
  }

  /**
   * Writes raw JavaScript to the page.
   *
   * @param string the raw JavaScript
   *
   * @return void
   */
  function append($javascript) {
    $this->lines[] = $javascript;
  }

  /**
   * Executes the given javascript after a delay of 'seconds'.
   *
   * @todo
   *
   * @param type <description>
   *
   * @return void
   */
  function delay($seconds = 1) {
    static $in_delay = FALSE;

    if (!$in_delay) {
      $in_delay = TRUE;
      $this->record("setTimeout(function() {\n\n");
    }

    else {
      $in_delay = FALSE;
      $this->record(sprintf("\n}, %d)", $seconds * 1000));
    }

    return $in_delay;
  }

  /**
   * Starts a script.aculo.us visual effect. See
   * ScriptaculousHelper for more information.
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  function visual_effect($name, $id = FALSE, $js_opt = array()) {
    $this->record(ScriptaculousHelper::visual_effect($name, $id, $js_opt));
  }

  /**
   * @ignore
   */
  function record($line) {
    $line = preg_replace('/\;$/', '', rtrim($line)) . ';';
    $this->append($line);
  }

  /**
   * @ignore
   */
  function javascript_object_for($object) {
    static $json;
    if (is_null($json)) {
      $json =& new Services_JSON();
    }
    return $json->encode($object);
  }

  /**
   * @ignore
   */
  function arguments_for_call($arguments) {
    $mapped = array();
    foreach ($arguments as $argument)
      $mapped[] = $this->javascript_object_for($argument);
    return join(',', $mapped);
  }
}
