<?php

/*
 * scriptaculous_helper.php - Help with scriptaculous.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * ScriptaculousHelper.
 *
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    David Heinemeier Hansson
 * @copyright (c) Authors
 * @version   $Id: scriptaculous_helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class ScriptaculousHelper {

  /**
   * Returns a JavaScript snippet to be used on the AJAX callbacks for starting
   * visual effects.
   *
   * Example:
   *  ScriptaculousHelper::visual_effect('highlight', 'posts',
   *    array('duration' => 0.5 ));
   *
   * If no '$element_id' is given, it assumes "element" which should be a local
   * variable in the generated JavaScript execution context. This can be used
   * for example with drop_receiving_element():
   *
   *  ScriptaculousHelper::drop_receving_element(..., array(...
   *        'loading' => ScriptaculousHelper::visual_effect('fade')));
   *
   * This would fade the element that was dropped on the drop receiving element.
   *
   * You can change the behaviour with various options, see
   * http://script.aculo.us for more documentation.
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return string <description>
   */
  function visual_effect($name, $element_id = FALSE, $js_opt = array()) {

    $element = $element_id ? "'$element_id'" : 'element';

    switch ($name) {
      case 'toggle_appear':
      case 'toggle_blind':
      case 'toggle_slide':
        return sprintf("new Effect.toggle(%s, %s, %s)",
                       $element, substr($name, 7),
                       JsHelper::options_for_javascript($js_opt));
    }

    return sprintf("new Effect.%s(%s, %s)",
                   # TODO
                   TextHelper::camelize($name),
                   $element, JsHelper::options_for_javascript($js_opt));
  }


  /**
   * Makes the elements with the DOM ID specified by '$element_id' sortable
   * by drag-and-drop and make an AJAX call whenever the sort order has
   * changed. By default, the action called gets the serialized sortable
   * element as parameters.
   *
   * Example:
   *   <php echo sortable_element($my_list, array(
   *      'url' => '@order',
   *   )) ?>
   *
   * In the example, the action gets a '$my_list' array parameter
   * containing the values of the ids of elements the sortable consists
   * of, in the current order.
   *
   * You can change the behaviour with various options, see
   * http://script.aculo.us for more documentation.
   */
  function sortable_element($element_id, $options = array()) {

    if (!isset($options['with']))
      $options['with'] = "Sortable.serialize('$element_id')";

    if (!isset($options['onUpdate']))
      $options['onUpdate'] =
        sprintf('function(){%s}', PrototypeHelper::remote_function($options));

    foreach (PrototypeHelper::get_ajax_options() as $key)
      unset($options[$key]);

    foreach (array('tag', 'overlap', 'constraint', 'handle') as $option)
      if (isset($options[$option]))
        $options[$option] = "'{$options[$option]}'";

    if (isset($options['containment']))
      $options['containment'] = JsHelper::array_or_string_for_javascript($options['containment']);

    if (isset($options['hoverclass']))
      $options['hoverclass'] = "'{$options['hoverclass']}'";

    if (isset($options['only']))
      $options['only'] = JsHelper::array_or_string_for_javascript($options['only']);

    return JsHelper::javascript_tag(
      sprintf("Sortable.create('%s', %s)",
              $element_id, JsHelper::options_for_javascript($options)));
  }
}
