<?php

/*
 * php_template.php - Template engine using PHP
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * A template engine that uses PHP to render templates.
 *
 * @package   flexi
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id$
 */

class Flexi_PhpTemplate extends Flexi_Template {

  /**
   * Parse, render and return the presentation.
   *
   * @return string A string representing the rendered presentation.
   */
  function _render() {

    # extract attributes
    extract($this->attributes, EXTR_REFS);

    # include template, parse it and get output
    ob_start();
    require $this->template;
    $content_for_layout = ob_get_clean();


    # include layout, parse it and get output
    if (isset($this->layout)) {
      $defined = get_defined_vars();
      unset($defined['this']);
      $content_for_layout = $this->layout->render($defined);
    }

    return $content_for_layout;
  }


  /**
   * Parse, render and return the presentation of a partial template.
   *
   * @param string A name of a partial template.
   * @param array  An optional associative array of attributes and their
   *               associated values.
   *
   * @return string A string representing the rendered presentation.
   */
  function render_partial($partial, $attributes = array()) {
    return $this->factory->render($partial, $attributes + $this->attributes);
  }


  /**
   * TODO
   *
   * @param string A name of a partial template.
   * @param array  The collection to be rendered.
   * @param string Optional a name of a partial template used as spacer.
   * @param array  An optional associative array of attributes and their
   *               associated values.
   *
   * @return string A string representing the rendered presentation.
   */
  function render_partial_collection($partial, $collection,
                                     $spacer = NULL, $attributes = array()) {

    $template =& $this->factory->open($partial);
    $template->set_attributes($this->attributes);
    $template->set_attributes($attributes);

    $collected = array();
    $iterator_name = array_pop(explode('/', $partial));
    foreach ($collection as $element)
      $collected[] = $template->render(array($iterator_name => $element));

    $spacer = isset($spacer) ? $this->render_partial($spacer, $attributes) : '';

    return join($spacer, $collected);
  }
}
