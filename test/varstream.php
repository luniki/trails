<?php

class ArrayFileStream {

  private $open_file, $position;

  private static $fs;

  static function set_filesystem(array $fs) {
    ArrayFileStream::$fs = $fs;
  }

  private static function &get_element($path) {
    $result =& ArrayFileStream::$fs;
    foreach (preg_split('/\//', $path, -1, PREG_SPLIT_NO_EMPTY) as $element) {
      if (!isset($result[$element])) {
        $null = NULL;
        return $null;
      }
      $result =& $result[$element];
    }
    return $result;
  }

  private static function &get_file($path) {
    $url = parse_url($path);
    $file =& self::get_element($url['host'] . $url['path']);

    if (is_null($file)) {
      throw new Exception("file not found.");
    }
    return $file;
  }

  function stream_close() {
    # nothing to do
  }

  function stream_flush() {
    # nothing to do
  }

  function stream_open($path, $mode, $options, $opened_path) {
    try {
      $this->open_file =& self::get_file($path);
      $this->position = 0;
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  function stream_read($count) {
    $ret = substr($this->open_file, $this->position, $count);
    $this->position += strlen($ret);
    return $ret;
  }

  function stream_write($data) {
    $left  = substr($this->open_file, 0, $this->position);
    $right = substr($this->open_file, $this->position + strlen($data));
    $this->open_file = $left . $data . $right;
    $this->position += strlen($data);
    return strlen($data);
  }

  function stream_tell() {
    return $this->position;
  }

  function stream_eof() {
    return $this->position >= strlen($this->open_file);
  }

  function stream_seek($offset, $whence) {

    switch ($whence) {
      case SEEK_SET:
        if ($offset < strlen($this->open_file) && $offset >= 0) {
          $this->position = $offset;
          return true;
        }
        else {
          return false;
        }
        break;

      case SEEK_CUR:
        if ($offset >= 0) {
          $this->position += $offset;
          return true;
        }
        else {
          return false;
        }
        break;

      case SEEK_END:
        if (strlen($this->open_file) + $offset >= 0) {
          $this->position = strlen($this->open_file) + $offset;
          return true;
        }
        else {
          return false;
        }
        break;

      default:
        return false;
    }
  }

  function stream_stat() {
    return array('size' => is_array($this->open_file)
                           ? sizeof($this->open_file)
                           : strlen($this->open_file));
  }

  function unlink($path) {

    $parent =& self::get_file(dirname($path));

    if (is_array($parent) && isset($parent[basename($path)])) {
      unset($parent[basename($path)]);
      return TRUE;
    }

    return FALSE;
  }

  function rename($path_from, $path_to) {
    throw new Exception('not implemented yet');
  }

  function mkdir($path, $mode, $options) {
    throw new Exception('not implemented yet');
  }

  function rmdir($path, $options) {
    throw new Exception('not implemented yet');
  }

  function dir_opendir($path, $options) {
    throw new Exception('not implemented yet');
  }

  function url_stat($path, $flags) {
    $time = time();

    $keys = array(
      'dev'     => 0,
      'ino'     => 0,
      'mode'    => 33216, // chmod 700
      'nlink'   => 0,
      'uid'     => function_exists('posix_getuid') ? posix_getuid() : 0,
      'gid'     => function_exists('posix_getgid') ? posix_getgid() : 0,
      'rdev'    => 0,
      'size'    => $flags & STREAM_URL_STAT_QUIET
                   ? @strlen($this->_pointer) : strlen($this->_pointer),
      'atime'   => $time,
      'mtime'   => $time,
      'ctime'   => $time,
      'blksize' => 0,
      'blocks'  => 0
    );
    return array_merge(array_values($keys), $keys);
  }

  function dir_readdir() {
    throw new Exception('not implemented yet');
  }

  function dir_rewinddir() {
    throw new Exception('not implemented yet');
  }

  function dir_closedir() {
    throw new Exception('not implemented yet');
  }
}


#ArrayFileStream::set_filesystem(
#  array('tmp' =>
#    array('a' => 'my content',
#          'b' => '<? echo "hallo welt!";')));


#stream_wrapper_register("var", "ArrayFileStream")
#    or die("Failed to register protocol");

#$fp = fopen("var://tmp/a", "rw+");


#while (!feof($fp)) {
#    var_dump(fgets($fp));
#}

#fwrite($fp, "line1\n");
#fwrite($fp, "line2\n");
#fwrite($fp, "line3\n");

#rewind($fp);

#var_dump(file_get_contents('var://tmp/a'));

#fclose($fp);
#unlink('var://tmp/a');

#var_dump(include 'var://tmp/b');

