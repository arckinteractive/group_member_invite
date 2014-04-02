<?php

require_once 'lib/hooks.php';
require_once 'lib/events.php';

elgg_register_event_handler('init', 'system', 'group_member_invite_init');

function group_member_invite_init() {
	elgg_extend_view('css/elgg', 'group_member_invite/css');
	elgg_register_plugin_hook_handler('action', 'groups/invite', 'group_member_collection_invite');
	elgg_register_plugin_hook_handler('action', 'groups/invite', 'group_member_invitation_hook');
	
	elgg_register_event_handler('create', 'invited', 'group_member_invitation_message');
	elgg_register_event_handler('create', 'annotation', 'group_member_invitation_create_annotation');
	elgg_register_event_handler('join', 'group', 'group_member_invitation_join_group');
	
	if (elgg_is_admin_logged_in()) {
		run_function_once('gmi_upgrade_20131009');
	}
}


/**
 * Group tools added their own members can invite interface that conflicted with this one
 * As this was to use their systems it makes sense to hand this info back to group_tools
 * 
 * This takes all settings of our previous system and translates it to the group_tools system
 */
function gmi_upgrade_20131009() {
	// process all groups
	set_time_limit(0);

	$options = array(
		'type' => 'group',
		'limit' => false,
		'metadata_names' => array('member_invite_enable'),
		'metadata_values' => array('yes', 'no')
	);

	$groups = new ElggBatch('elgg_get_entities_from_metadata', $options);

	foreach ($groups as $group) {
		if ($group->member_invite_enable) {
			$group->invite_members = $group->member_invite_enable;
		}
	}
}