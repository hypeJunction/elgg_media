<?php

$entity = elgg_extract('entity', $vars);
$type = elgg_extract('type', $vars, 'icon');

if (!$entity instanceof \ElggEntity) {
	return;
}

if (!$entity->canEdit() || !elgg_media_is_allowed_type($entity, $type)) {
	return;
}

$current_icon = elgg_view('output/img', [
	'src' => $entity->getIconURL([
		'size' => 'small',
		'type' => $type,
	]),
	'alt' => elgg_echo("media:$type"),
		]);

$remove_button = '';
if (elgg_has_media($entity, $type)) {
	$remove_button = elgg_view('output/url', [
		'text' => elgg_echo('remove'),
		'href' => elgg_http_add_url_query_elements('/action/media/remove', [
			'guid' => $entity->guid,
			'type' => $type,
		]),
		'is_action' => true,
		'confirm' => true,
		'class' => 'elgg-button elgg-button-delete',
	]);
}

$form_params = ['enctype' => 'multipart/form-data'];
$upload_form = elgg_view_form('media/upload', $form_params, $vars);

$current = elgg_view_module('aside', elgg_echo('media:current'), $current_icon, [
	'footer' => $remove_button,
		]);

echo elgg_view_image_block($current, $upload_form, [
	'class' => 'media-upload-image-block',
]);
