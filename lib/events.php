<?php

/**
 * when a user is invited into a group send a message
 * 
 * @param type $event
 * @param type $type
 * @param type $relationship
 */
function group_member_invitation_message($event, $type, $relationship) {
	
	if (!($relationship instanceof ElggRelationship)) {
		return;
	}
	
	$group = get_entity($relationship->guid_one);
	$user = get_user($relationship->guid_two);
	
	if (!elgg_instanceof($group, 'group') || !elgg_instanceof($user, 'user')) {
		return;
	}
	
	
	$inviter = elgg_get_logged_in_user_entity();
	// Send notification
		$url = elgg_normalize_url("groups/invitations/$user->username");
		notify_user($user->getGUID(), $group->owner_guid,
				elgg_echo('groups:invite:subject', array($inviter->name, $group->name)),
				elgg_echo('groups:invite:body', array(
					$user->name,
					$inviter->name,
					$group->name,
					$url,
				)),
				NULL,
                'site');
}


function group_member_invitation_create_annotation($event, $type, $annotation) {
	if ($annotation instanceof ElggAnnotation) {
		if ($annotation->name == 'email_invitation') {
			$parts = explode('|', $annotation->value);
			$token = $parts[0];
			
			create_annotation($annotation->entity_guid, 'email_invitation_friend', $token, '', elgg_get_logged_in_user_guid(), ACCESS_PUBLIC);
		}
	}
}


function group_member_invitation_join_group($event, $type, $params) {
	$annotations = elgg_get_annotations(array(
		'guid' => $params['group']->guid,
		'annotation_names' => array('email_invitation_friend'),
		'annotation_values' => get_input('invite_code')
	));
	
	if ($annotations) {
		$friend = get_user($annotations[0]->owner_guid);
		
		if ($friend) {
			$params['user']->addFriend($friend->guid);
		}
	}
}