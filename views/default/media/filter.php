<?php

$entity = elgg_extract('entity', $vars);
$filter_context = elgg_extract('filter_context', $vars);
foreach (['icon', 'cover', 'skyscraper'] as $type) {
	if (elgg_media_is_allowed_type($entity, $type)) {
		elgg_register_menu_item('filter', [
			'name' => $type,
			'text' => elgg_echo("media:$type"),
			'href' => "media/$entity->guid/edit/$type",
			'selected' => $filter_context == $type,
		]);
	}
}

echo elgg_view_menu('filter', [
	'class' => 'elgg-menu-hz',
	'sort_by' => 'priority',
]);
