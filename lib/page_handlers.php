<?php

/**
 * Entity media page handler
 *
 * @param array  $segments   URL segments
 * @param string $identifier Page identifier
 * @return bool
 */
function elgg_media_page_handler($segments, $identifier) {

	$guid = array_shift($segments);
	$section = array_shift($segments);

	switch ($section) {

		case 'edit' :
		case 'upload' :
		case 'crop' :

			$type = array_shift($segments) ? : 'icon';

			echo elgg_view_resource('media/edit', [
				'entity' => get_entity($guid),
				'type' => $type,
			]);
			break;
	}

	return false;
}
