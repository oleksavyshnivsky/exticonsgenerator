<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?=e($this->title)?></title>

	<!-- CSS only -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha256-YvdLHPgkqJ8DVUxjjnGVlMMJtNimJ6dYkowFFvp4kKs=" crossorigin="anonymous">
	
	<!-- JavaScript Bundle with Popper -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha256-cMPWkL3FzjuaFSfEYESYmjF25hCIL6mfRSPnW8OVvM4=" crossorigin="anonymous"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light p-0 navbar-dark">
	<a class="navbar-brand" href="<?=$this->url()?>" style="padding: 2px 10px">
		<span><?=_('Image converter')?></span>
	</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapsible" aria-controls="navbar-collapsible" aria-expanded="false" aria-label="<?=_('Toggle navigation')?>">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbar-collapsible">
		<ul class="navbar-nav ml-auto mr-1">
			<!-- <li class="nav-item d-lg-none"><a class="nav-link" href="/">Головна</a></li> -->
		</ul>
	</div>
</nav>

<main class="container" id="main" data-oa-main><?=$content?></main>

</body>
</html>