<form method="post" action="<?=$this->url()?>" enctype="multipart/form-data" class="mb-3">
	<div class="form-floating mb-3">
		<input type="file" id="editor-image" name="image" class="form-control" required="">
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

<?php if ($filenames): ?>
<div class="table-responsive">
	<table class="table table-sm table-striped table-hover">
		<tbody>
			<?php foreach ($filenames as $filename): ?>
			<tr>
				<td>
					<img src="<?=e($filename)?>" alt="">
				</td>
				<td class="text-end">
					<a href="<?=e($filename)?>" filename="<?=basename($filename)?>" download>
						<?=basename($filename)?>
					</a>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2" class="text-end">
					<a href="<?=e($zipfile)?>" filename="<?=basename($zipfile)?>" download="">ZIP</a>
				</td>
			</tr>
		</tfoot>
	</table>
</div>
<?php endif ?>