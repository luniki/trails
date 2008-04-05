<?php

/*
 * js_helper.php - Help with javascripts.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Provides functionality for working with JavaScript in your views.
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    David Heinemeier Hansson
 * @copyright (c) Authors
 * @version   $Id: js_helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class JsHelper {

  /**
   * Returns a link that'll trigger a javascript function using the
   * onclick handler and return false after the fact.
   *
   * Example:
   *   JsHelper::link_to_function('Greeting', "alert('Hello world!')");
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function link_to_function($name, $function, $html = array()) {
    $html['href'] = isset($html['href']) ? $html['href'] : '#';
    $html['onclick'] = $function.'; return false;';
    return TagHelper::content_tag('a', $name, $html);
  }

  /**
   * Returns a link that'll trigger a JavaScript function using the onclick
   * handler.
   *
   * Examples:
   *   JsHelper::button_to_function("Greeting", "alert('Hello world!')");
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function button_to_function($name, $function, $html_options = array()) {

    $html_options['type'] = 'button';
    $html_options['value'] = $name;
    $html_options['onclick'] = sprintf('%s%s;',
      isset($html_options['onclick']) ? $html_options['onclick'] . '; ' : '',
      $function);
    return TagHelper::tag('input', $html_options);
  }

  /**
   * Escape carrier returns and single and double quotes for Javascript
   * segments.
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function escape_javascript($javascript = '') {
    $javascript = preg_replace('/\r\n|\n|\r/', "\\n", $javascript);
    $javascript = preg_replace('/(["\'])/', '\\\\\1', $javascript);
    return $javascript;
  }

  /**
   * Returns a JavaScript tag with the '$content' inside.
   * Example:
   *   JsHelper::javascript_tag("alert('All is good')");
   *   => <script type="text/javascript">alert('All is good')</script>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function javascript_tag($content) {
    return TagHelper::content_tag('script',
                                  JsHelper::js_cdata_section($content),
                                  array('type' => 'text/javascript'));
  }

  /**
   * @ignore
   */
  function js_cdata_section($content) {
    return "\n//".TagHelper::cdata_section("\n$content\n//")."\n";
  }

  /**
   * @ignore
   */
  function options_for_javascript($opt) {
    $opts = array();
    foreach ($opt as $key => $value)
      $opts[] = "$key:$value";
    sort($opts);

    return '{'.join(', ', $opts).'}';
  }

  /**
   * @ignore
   */
  function array_or_string_for_javascript($option) {
    if (is_array($option))
      return "['".join("','", $option)."']";
    else if ($option)
      return "'$option'";
  }
}
