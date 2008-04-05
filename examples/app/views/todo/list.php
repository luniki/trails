<ul id="todolist">
  <?= $this->render_partial_collection('todo/item', $items) ?>
</ul>

<hr size="3">
<form method="post" action="add">
  New item:
  <input type="text" name="item[description]" />
  <input type="submit" value="Add item" />
</form>
