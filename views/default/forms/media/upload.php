<?php

elgg_load_css('media');
elgg_require_js('forms/media/upload');

$entity = elgg_extract('entity', $vars);
$type = elgg_extract('type', $vars);

// Upload module
$upload_title = elgg_echo('media:upload');
$upload_mod = '<div class="elgg-text-help">' . elgg_echo('media:upload:instructions') . '</div>';
$upload_mod .= elgg_view("input/file", [
	'name' => 'media',
	'class' => 'media-upload-input'
		]);

echo elgg_view_module('aside', $upload_title, $upload_mod, [
	'class' => 'media-upload-module',
]);

// Cropper module
$cropper_title = elgg_echo('media:crop:title');
$cropper_mod = elgg_format_element('p', ['class' => 'elgg-text-help'], elgg_echo('media:create:instructions'));
$img = '';
$sizes = elgg_media_get_thumb_sizes($entity, $type);
$ratio = $sizes['small']['square'] ? 1 : $sizes['small']['w'] / $sizes['small']['h'];

if (elgg_has_media($entity, $type)) {
	$x = $y = 0;
	$width = $height = $sizes['master']['w'];
	if ($entity->{"{$type}_x2"} > $entity->{"{$type}_x1"} && $entity->{"{$type}_y2"} > $entity->{"{$type}_y1"}) {
		$x = (int) $entity->{"{$type}_x1"};
		$y = (int) $entity->{"{$type}_y1"};
		$width = (int) $entity->{"{$type}_x2"} - (int) $entity->{"{$type}_x1"};
		$height = (int) $entity->{"{$type}_y2"} - (int) $entity->{"{$type}_y1"};
	}

	$img = elgg_view('output/img', [
		'src' => $entity->getIconURL([
			'type' => $type,
			'size' => 'master'
		]),
		'alt' => elgg_echo('media'),
		'data-x' => $x,
		'data-y' => $y,
		'data-width' => $width,
		'data-height' => $height,
	]);
}

$cropper_mod .= elgg_format_element('div', ['class' => 'media-cropper-preview'], $img);
foreach (['x1', 'y1', 'x2', 'y2'] as $coord) {
	$cropper_mod .= elgg_view('input/hidden', [
		'name' => $coord,
		'value' => (int) $entity->$coord,
		"data-$coord" => true,
	]);
}

$cropper_mod .= elgg_view('input/hidden', [
	'name' => 'type',
	'value' => $type,
		]);

echo elgg_view_module('aside', $cropper_title, $cropper_mod, [
	'class' => 'media-cropper-module hidden',
	'data-ratio' => $ratio,
]);

$footer = elgg_view('input/submit', [
	'value' => elgg_echo('save'),
	'disabled' => true,
	'class' => 'elgg-state-disabled elgg-button-submit',
		]);

$footer .= elgg_view('input/hidden', [
	'name' => 'guid',
	'value' => $entity->guid
		]);

echo elgg_format_element('div', ['class' => 'elgg-foot'], $footer);
