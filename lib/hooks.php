<?php

/**
 * invite by friend collection
 * 
 * @param type $hook
 * @param type $type
 * @param type $return
 * @param type $params
 */
function group_member_collection_invite($hook, $type, $return, $params) {
	
	// get users added by any other means
	$user_guids = get_input("user_guid");
	if (!empty($user_guids) && !is_array($user_guids)){
		$user_guids = array($user_guids);
	}
	
	$collections = get_input('collections');
	if (!$collections || !is_array($collections)) {
		return $return;
	}
	
	foreach ($collections as $collection) {
		$members = get_members_of_access_collection($collection, true);
		
		if (is_array($members)) {
			if (!is_array($user_guids)) {
				$user_guids = array();
			}
			$user_guids = array_merge($user_guids, $members);
		}
	}

	// set the guids
	set_input('user_guid', array_unique($user_guids));
}


function group_member_invitation_hook($hook, $type, $return, $params) {
    $emails = get_input('group_member_emails', '', false);
    $user_guids = get_input('user_guid');

    if (!$emails) {
        return $return;
    }
    
    if (!empty($user_guids) && !is_array($user_guids)){
		$user_guids = array($user_guids);
	}
    else {
        if (!is_array($user_guids)) {
            $user_guids = array();
        }
    }
    
    // get array of emails
	
	//normalize to clear up cases such as this
	// "Matt Beckett" <matt+kdjsf@arckinteractive.com>; "Mark Bennet" (matt+jdf@arckinteractive.com), "Mutt Bucket" <matt+mutt@arckinteractive.com>
	$emails = str_replace(array("\n", ',', ';'), ' ', $emails);
	$emails = str_replace(array('(', ')', '<', '>', '"', "'"), '', $emails);
	
    $array = explode(' ', $emails);
    $array = array_map('trim', $array);
    
    $invite_emails = array();
    $guids = array();
    foreach ($array as $value) {
        if (is_email_address($value)) {
            if ($user = get_user_by_email($value)) {
                // they already exist in the system, add them as a guid to invite
                $guids[] = $user[0]->guid;
            }
            else {
                $invite_emails[] = $value;
            }
        }
    }
    
    $invite_guids = array_merge($guids, $user_guids);
    
    array_unique($invite_guids);

    set_input('user_guid_email', $invite_emails);
    set_input('user_guid', $invite_guids);
}