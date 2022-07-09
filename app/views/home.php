<?php foreach ($GLOBALS['msgbox'] as $alert): ?>
<div class="alert alert-<?=e($alert['style'])?> alert-dismissible fade show" role="alert">
	<strong><?=e($alert['title'])?>!</strong> <?=e($alert['text'])?>
	<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endforeach ?>

<form method="post" action="<?=$this->url()?>" enctype="multipart/form-data" class="mb-3">
	<div class="form-floating mb-3">
		<input type="file" id="editor-image" name="image" class="form-control" required="" accept="image/*">
		<label for="editor-image"><?=_('Image')?></label>
	</div>
	<div class="form-floating mb-3">
		<input type="text" id="editor-name" name="name" class="form-control" maxlength="255" required="" placeholder="icon" value="icon">
		<label for="editor-name"><?=_('Name')?></label>
	</div>
	<div class="text-end">
		<input type="submit" value="<?=_('Submit')?>" class="btn btn-primary">
	</div>
</form>
