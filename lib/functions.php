<?php

/**
 * Checks if media type is allowed for given entity
 *
 * @param \ElggEntity $entity  Entity
 * @param string      $type    Media type
 * @param bool        $default Default permission
 * @return bool
 */
function elgg_media_is_allowed_type(\ElggEntity $entity, $type = 'icon', $default = false) {
	$hook_type = implode(':', array_filter([$entity->getType(), $entity->getSubtype()]));
	$hook_params = [
		'type' => $type,
		'entity' => $entity,
	];
	return (bool) elgg_trigger_plugin_hook("media:$type", $hook_type, $hook_params, $default);
}

/**
 * Checks if media type is allowed for given entity
 *
 * @param \ElggEntity $entity  Entity
 * @param string      $type    Media type
 * @return bool
 */
function elgg_media_get_thumb_sizes(\ElggEntity $entity, $type = 'icon') {
	$hook_type = implode(':', array_filter([$entity->getType(), $entity->getSubtype()]));
	$hook_params = [
		'type' => $type,
		'entity' => $entity,
	];
	return elgg_trigger_plugin_hook("entity:$type:sizes", $hook_type, $hook_params, []);
}

/**
 * Checks if entity has media of a given type
 *
 * @param \ElggEntity $entity  Entity
 * @param string      $type    Media type
 * @return bool
 */
function elgg_has_media(\ElggEntity $entity, $type = 'icon') {
	return (bool) $entity->{"{$type}_originalfilename"};
}

/**
 * Saves entity media
 *
 * @param mixed       $file             Path to file, or an array with 'tmp_name' and 'name', or ElggFile
 * @param \ElggEntity $entity           Entity
 * @param string      $type             Media type
 * @return \ElggFile
 */
function elgg_save_media($file, \ElggEntity $entity, $type = 'icon') {

	if ($file instanceof ElggFile) {
		$path = $file->getFilenameOnFilestore();
	} else if (is_array($file)) {
		$path = elgg_extract('tmp_name', (array) $file);
	} else {
		$path = (string) $file;
	}

	if (!file_exists($path)) {
		return false;
	}

	elgg_remove_media($entity, $type);

	$filename = is_string($file) ? basename($path) : elgg_extract('name', (array) $file);
	$originalfilename = time() . $filename; // in case it's named 'large.jpg' or similar


	$filehandler = new ElggFile();
	$filehandler->owner_guid = $entity->guid;
	$filehandler->setFilename("media/$type/$originalfilename");
	$filehandler->open('write');
	$filehandler->write(file_get_contents($path));
	$filehandler->close();

	if (!$filehandler->exists()) {
		return false;
	}

	$entity->{"{$type}_originalfilename"} = $originalfilename;

	return $filehandler;
}

/**
 * Creates entity media thumbnails
 *
 * @param mixed       $file        Path to file, or an array with 'tmp_name' and 'name', or ElggFile
 * @param \ElggEntity $entity      Entity
 * @param string      $type        Media type
 * @param array       $crop_coords Cropping coordinates
 * @return \ElggFile[]|false
 */
function elgg_create_media_thumbnails($file, \ElggEntity $entity, $type = 'icon', $crop_coords = []) {

	if ($file instanceof ElggFile) {
		$path = $file->getFilenameOnFilestore();
	} else if (is_array($file)) {
		$path = elgg_extract('tmp_name', (array) $file);
	} else {
		$path = (string) $file;
	}

	if (!file_exists($path)) {
		return false;
	}

	$thumb_sizes = elgg_media_get_thumb_sizes($entity, $type);

	$master_info = $thumb_sizes['master'];
	unset($thumb_sizes['master']); // master is used as base for cropping

	$filehandler = elgg_get_media_original($entity, $type);
	if (!$filehandler) {
		return false;
	}

	$resized = get_resized_image_from_existing_file($path, $master_info['w'], $master_info['h'], $master_info['square'], 0, 0, 0, 0, $master_info['upscale']);
	if (!$resized) {
		return false;
	}

	$master = new ElggFile();
	$master->owner_guid = $entity->guid;
	$master->setFilename("media/$type/master.jpg");
	$master->open('write');
	$master->write($resized);
	$master->close();

	$thumbs = [];

	$x1 = (int) elgg_extract('x1', $crop_coords);
	$y1 = (int) elgg_extract('y1', $crop_coords);
	$x2 = (int) elgg_extract('x2', $crop_coords);
	$y2 = (int) elgg_extract('y2', $crop_coords);

	foreach ($thumb_sizes as $name => $size_info) {
		$resized = get_resized_image_from_existing_file($path, $size_info['w'], $size_info['h'], $size_info['square'], $x1, $y1, $x2, $y2, $size_info['upscale']);
		if (!$resized) {
			foreach ($thumbs as $thumb) {
				$thumb->delete();
			}
			return false;
		}

		$thumb = new ElggFile();
		$thumb->owner_guid = $entity->guid;
		$thumb->setFilename("media/$type/$name.jpg");
		$thumb->open('write');
		$thumb->write($resized);
		$thumb->close();
		$thumbs[] = $thumb;
	}

	//$entity->{"{$type}time"} = time();
	$entity->{"{$type}_time_created"} = time();

	// normalize coords to master
	$natural_image_size = getimagesize($path);
	$master_image_size = getimagesize($master->getFilenameOnFilestore());
	$ratio = $master_image_size[0] / $natural_image_size[0];

	$entity->{"{$type}_x1"} = $x1 * $ratio;
	$entity->{"{$type}_x2"} = $x2 * $ratio;
	$entity->{"{$type}_y1"} = $y1 * $ratio;
	$entity->{"{$type}_y2"} = $y2 * $ratio;

	return $thumbs;
}

/**
 * Removes entity media
 *
 * @param \ElggEntity $entity  Entity
 * @param string      $type    Media type
 * @return bool
 */
function elgg_remove_media(\ElggEntity $entity, $type = 'icon') {

	$original = elgg_get_media_original($entity, $type);
	if (!$original || !$original->delete()) {
		return false;
	}

	unset($entity->{"{$type}_originalfilename"});
	unset($entity->{"{$type}_time_created"});
	foreach (['x1', 'x2', 'y1', 'y2'] as $c) {
		unset($entity->{"{$type}_{$c}"});
	}

	$success = true;
	$sizes = elgg_media_get_thumb_sizes($entity, $type);
	foreach ($sizes as $size => $opts) {
		$file = elgg_get_media_file($entity, $type, $size);
		if (!$file) {
			continue;
		}
		$file->delete();
	}

	return $success;
}

/**
 * Returns original media file
 *
 * @param \ElggEntity $entity  Entity
 * @param string      $type    Media type
 * @return \ElggFile|false
 */
function elgg_get_media_original(\ElggEntity $entity, $type = 'icon') {

	$filename = $entity->{"{$type}_originalfilename"};

	$file = new \ElggFile();
	$file->owner_guid = $entity->guid;
	$file->setFilename("media/{$type}/{$filename}");

	return $file->exists() ? $file : false;
}

/**
 * Returns media file
 *
 * @param \ElggEntity $entity  Entity
 * @param string      $type    Media type
 * @return \ElggFile
 */
function elgg_get_media_file(\ElggEntity $entity, $type = 'icon', $size = 'small', $ext = 'jpg') {

	$file = new \ElggFile();
	$file->owner_guid = $entity->guid;
	$file->setFilename("media/$type/$size.$ext");

	return $file->exists() ? $file : false;
}
