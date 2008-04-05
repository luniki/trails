<?php

/*
 * text_helper.php - Help with texts.
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * TextHelper.
 *
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id$
 */

class TextHelper {

  /**
   * Returns a camelized string from a lower case and underscored string by
   * replacing slash with underscore and upper-casing each letter preceded
   * by an underscore.
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  function camelize($word) {
    return str_replace(' ', '',
                       ucwords(str_replace(array('_', '/'),
                                           array(' ', ' '), $word)));
  }
}

