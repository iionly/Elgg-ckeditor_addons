<?php
elgg_gatekeeper();

$user = elgg_get_logged_in_user_entity();
$data_root = elgg_get_config('dataroot');
$user_dir = new \Elgg\EntityDirLocator($user->guid);
$ck_dir = $data_root . $user_dir . 'ckeditor/';

$images = array();

if (!is_dir($ck_dir)) {
	mkdir($ck_dir, 0700, true);
}

$dir = new \DirectoryIterator($ck_dir);

foreach ($dir as $file) {
	/* @var \SplFileInfo $file */
	if (!$file->isFile() || !$file->isReadable()) {
		continue;
	}

	$path = $file->getRealPath();

	if (pathinfo($path, PATHINFO_EXTENSION) !== 'jpg') {
		continue;
	}

	$hash = pathinfo($path, PATHINFO_FILENAME);

	$image = elgg_format_element('img', array(
		'src' => elgg_normalize_url("ckeditor/image/$user->guid/$hash"),
		'class' => 'ckeditor-browser-image',
		'data-callback' => get_input('CKEditorFuncNum'),
		'width' => 100,
	));
	$images[] = elgg_format_element('li', array(
		'class' => 'elgg-item',
			), $image);
}

if (empty($images)) {
	$output = elgg_echo('ckeditor:browse:no_uploads');
} else {
	$output = elgg_format_element('ul', array(
		'class' => 'elgg-gallery',
			), implode('', $images));
}

$head = elgg_view('page/elements/head');
$foot = elgg_view('page/elements/foot');
echo elgg_view('page/elements/html', array(
	'head' => $head,
	'body' => elgg_format_element('div', array('class' => 'ckeditor-addons-browser'), $output) . $foot,
));
?>
<script>
	require(['jquery'], function ($) {
		console.log(window);
		$(document).on('click', '.ckeditor-browser-image[data-callback]', function (e) {
			e.preventDefault();
			window.opener.CKEDITOR.tools.callFunction($(this).data('callback'), $(this).attr('src'), '');
			window.close();
		});
	});
</script>