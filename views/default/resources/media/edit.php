<?php

$entity = elgg_extract('entity', $vars);
$type = elgg_extract('type', $vars, 'icon');

if (!$entity instanceof \ElggEntity) {
	return;
}

if (!$entity->canEdit() || !elgg_media_is_allowed_type($entity, $type)) {
	return;
}

if ($entity instanceof ElggUser || $entity instanceof ElggGroup) {
	elgg_set_page_owner_guid($entity->guid);
} else {
	elgg_set_page_owner_guid($entity->owner_guid);
}

elgg_push_breadcrumb(elgg_echo('media'));
elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());

$title = elgg_echo("media:$type:edit");
elgg_push_breadcrumb($title);

$content = elgg_view('media/edit', $vars);

$body = elgg_view_layout('content', [
	'content' => $content,
	'title' => $title,
	'filter' => elgg_view('media/filter', [
		'entity' => $entity,
		'filter_context' => $type,
	]),
		]);

echo elgg_view_page($title, $body);

