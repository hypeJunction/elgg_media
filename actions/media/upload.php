<?php

$guid = get_input('guid');
$entity = get_entity($guid);
$type = get_input('type');

if (!$entity instanceof ElggEntity || !$entity->canEdit() || !elgg_media_is_allowed_type($entity, $type)) {
	register_error(elgg_echo('media:upload:fail'));
	forward(REFERER);
}

if ($_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
	// file has been uploaded by upload failed
	register_error(elgg_echo('media:upload:fail'));
	register_error(elgg_get_friendly_upload_error($_FILES['media']['error']));
	forward(REFERER);
} else if ($_FILES['media']['error'] === UPLOAD_ERR_OK) {
	// upload was successful, replace media file
	$file = elgg_save_media($_FILES['media'], $entity, $type);
} else {
	// grab master image
	$file = elgg_get_media_file($entity, $type, 'master');
}

$coords = [
	'x1' => (int) get_input('x1', 0),
	'y1' => (int) get_input('y1', 0),
	'x2' => (int) get_input('x2', 0),
	'y2' => (int) get_input('y2', 0),
];

if (elgg_create_media_thumbnails($file, $entity, $type, $coords)) {
	system_message(elgg_echo('media:crop:success'));
} else {
	die();
	register_error(elgg_echo('media:crop:fail'));
}

forward(REFERER);
