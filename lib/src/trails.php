<?php
function fgc($files/*, ... */) {
  $result = '';
  $files = func_get_args();
  foreach ($files as $file) {
    $string = file_get_contents(dirname(__FILE__)."/$file.php");
    $result .= preg_replace("/^(<\?(php)?)|(\?>)\s*\v+$/", "", $string);
  }
  return $result;
}
?>
<?= "<?php" ?>
<?= fgc("HEADER") ?>

<?= fgc("dispatcher", "response", "controller",
        "inflector", "flash", "exception") ?>
