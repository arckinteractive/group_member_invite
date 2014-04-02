<?php

// process all groups
set_time_limit(0);

$options = array(
	'type' => 'group',
	'limit' => false
);

$groups = new ElggBatch('elgg_get_entities', $options);

foreach ($groups as $group) {
	if (!$group->member_invite_enable) {
		// set a default
		// 'no' for closed groups, 'yes' for open groups
		if ($group->isPublicMembership()) {
			$group->member_invite_enable = 'yes';
		}
		else {
			$group->member_invite_enable = 'no';
		}
	}
}