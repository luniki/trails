<li id="item_<?= $item->id ?>">
  <input type="checkbox" name="item[<?= $item->id ?>][done]"
          <?= $item->done ? 'checked="checked"' : '' ?>
          onClick="<?= PrototypeHelper::remote_function(array('url' => $controller->url_for('todo/toggle/' . $item->id))) ?>" />
  <?= $item->description ?>
  <a href="<?= $controller->url_for('todo/edit/' . $item->id) ?>">edit</a>
  <a href="<?= $controller->url_for('todo/delete/' . $item->id) ?>">delete</a>
</li>
