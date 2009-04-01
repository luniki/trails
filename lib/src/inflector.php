<?php

/**
 * The Inflector class is a namespace for inflections methods.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 7001 2008-04-04 11:20:27Z mlunzena $
 */

class Trails_Inflector {


  /**
   * Returns a camelized string from a lower case and underscored string by
   * replacing slash with underscore and upper-casing each letter preceded
   * by an underscore. TODO
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  static function camelize($word) {
    $parts = explode('/', $word);
    foreach ($parts as $key => $part) {
      $parts[$key] = str_replace(' ', '',
                                 ucwords(str_replace('_', ' ', $part)));
    }
    return join('_', $parts);
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  static function underscore($word) {
    $parts = explode('_', $word);
    foreach ($parts as $key => $part) {
      $parts[$key] = preg_replace('/(?<=\w)([A-Z])/', '_\\1', $part);
    }
    return strtolower(join('/', $parts));
  }
}
