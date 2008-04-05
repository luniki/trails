<form action="<?= $controller->url_for('todo/edit/' . $item->id) ?>" method="post">
  <input type="checkbox" name="item[done]" value="1" <?= $item->done ? 'checked="checked"' : '' ?> />
  <input type="text"     name="item[description]" value="<?= $item->description ?>" />
  <input type="submit"   value="Edit" />
</form>
