<?php

$guid = get_input('guid');
$type = get_input('type');

$entity = get_entity($guid);

if (!$entity || !$entity->canEdit() || !elgg_has_media($entity, $type)) {
	register_error(elgg_echo('media:remove:error:no_media'));
	forward(REFERER);
}

if (elgg_remove_media($entity, $type)) {
	system_message(elgg_echo('media:remove:succes'));
} else {
	register_error(elgg_echo('media:remove:error'));
}
forward(REFERER);
