<?php

/*
 * js_template.php - Template engine generating Javascript
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * A template engine that renders Javascript templates.
 *
 * @package   flexi
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id$
 */

class Flexi_JsTemplate extends Flexi_PhpTemplate {

  /**
   * Parse, render and return the presentation.
   *
   * @return string A string representing the rendered presentation.
   */
  function _render() {

    # put attributes into scope
    extract($this->attributes, EXTR_REFS);

    # get generator object
    $update_page =& new Flexi_JavascriptGenerator();

    # include template, parse it and remove output
    ob_start();
    require $this->template;
    ob_end_clean();

    return $update_page->to_s();
  }
}
