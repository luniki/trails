<?='<?php'?>

class <?= Trails_Dispatcher::camelize($controller) ?>Controller extends Trails_Controller {
<? foreach ($actions as $action) : ?>

  function <?= $action ?>_action() {
  }
<? endforeach ?>
}
