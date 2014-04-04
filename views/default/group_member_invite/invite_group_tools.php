<?php
	/**
	 * Elgg groups plugin
	 *
	 * @package ElggGroups
	 */

	$group = elgg_extract("entity", $vars, elgg_get_page_owner_entity());
	$invite_site_members = elgg_extract("invite", $vars, "no");
	$invite_email = elgg_extract("invite_email", $vars, "no");;
	$invite_csv = elgg_extract("invite_csv", $vars, "no");;
	
	$owner = $group->getOwnerEntity();
	$forward_url = $group->getURL();
	
	if ($friends = elgg_get_logged_in_user_entity()->getFriends("", false)) {
		$toggle_content = "<span>" . elgg_echo("group_tools:group:invite:friends:select_all") . "</span>";
		$toggle_content .= "<span class='hidden'>" . elgg_echo("group_tools:group:invite:friends:deselect_all") . "</span>";
		
		$friendspicker = elgg_view("output/url", array("text" => $toggle_content, "href" => "javascript:void(0);", "onclick" => "group_tools_toggle_all_friends();", "id" => "friends_toggle", "class" => "float-alt elgg-button elgg-button-action"));
		$friendspicker .= elgg_view('input/friendspicker', array('entities' => $friends, 'name' => 'user_guid', 'highlight' => 'all'));
	} else {
		$friendspicker = elgg_echo('groups:nofriendsatall');
	}

	// which options to show
	if(in_array("yes", array($invite_site_members, $invite_email, $invite_csv))){
		$tabs = array(
			"friends" => array(
				"text" => elgg_echo("friends"),
				"href" => "#",
				"rel" => "friends",
				"priority" => 300,
				"onclick" => "group_tools_group_invite_switch_tab(\"friends\");",
			)
		);
		
		// invite friends
		$form_data = '<div id="group_tools_group_invite_friends" class="hidden">';
		$form_data .= $friendspicker;
		$form_data .= "</div>";
		
		//invite from friend collections
		// allowed by default as they're all friends anyway
		$tabs['collections'] = array(
			'text' => elgg_echo('group_member_invite:invite:bycollections'),
			'href' => '#',
			'rel' => 'collections',
			'priority' => 200,
			'onclick' => 'group_tools_group_invite_switch_tab("collections");'
		);
		
		$user = elgg_get_logged_in_user_entity();
		$site = elgg_get_site_entity();
		$dbprefix = elgg_get_config('dbprefix');
		$form_data .= '<div id="group_tools_group_invite_collections" class="hidden">';
		// get their collections alphabetically
		$collections = array();
		if (elgg_is_logged_in()) {
			$query = "SELECT * FROM {$dbprefix}access_collections
				WHERE owner_guid = {$user->guid}
				AND site_guid = {$site->guid}
				ORDER BY name ASC";

			$collections = get_data($query);
		}
		
		if ($collections) {
			$form_data .= elgg_view('output/longtext', array(
				'value' => elgg_echo('group_member_invite:collections:help'),
				'class' => 'elgg-subtext'
			));
			foreach ($collections as $collection) {
				$form_data .= '<div>';
				$form_data .= elgg_view('input/checkbox', array(
					'name' => 'collections[]',
					'value' => $collection->id,
					'default' => false,
				));
				$form_data .= '<label>' . $collection->name . '</label>';
				$form_data .= '</div>';
			}
		}
		else {
			$form_data .= elgg_echo('group_member_invite:nocollections');
		}
		
		$link = elgg_view('output/url', array(
			'text' => elgg_echo('group_member_invite:collections:manage'),
			'href' => elgg_normalize_url('collections/' . elgg_get_logged_in_user_entity()->username),
				));
		$form_data .= '<br><br>' . $link;
		$form_data .= '</div>';

		//invite all site members
		if($invite_site_members == "yes"){
			$tabs["users"] = array(
				"text" => elgg_echo("group_tools:group:invite:users"),
				"href" => "#",
				"rel" => "users",
				"priority" => 300,
				"onclick" => "group_tools_group_invite_switch_tab(\"users\");"
			);
			
			$form_data .= "<div id='group_tools_group_invite_users'>";
			$form_data .= "<div>" . elgg_echo("group_tools:group:invite:users:description") . "</div>";
			$form_data .= elgg_view("input/group_invite_autocomplete", array("name" => "user_guid",
																				"id" => "group_tools_group_invite_autocomplete",
																				"group_guid" => $group->getGUID(),
																				"relationship" => "site"));
			if(elgg_is_admin_logged_in()){
				$form_data .= elgg_view("input/checkbox", array("name" => "all_users", "value" => "yes"));
				$form_data .= elgg_echo("group_tools:group:invite:users:all");
			}
			
			$form_data .= "</div>";
		}
		
		// invite by email
		if($invite_email == "yes"){
			$tabs["email"] = array(
				"text" => elgg_echo("group_tools:group:invite:email"),
				"href" => "#",
				"rel" => "users",
				"priority" => 100,
				"onclick" => "group_tools_group_invite_switch_tab(\"email\");",
				"selected" => true
			);
			
			$form_data .= "<div id='group_tools_group_invite_email'>";
			$form_data .= "<div>" . elgg_echo("group_tools:group:invite:email:description") . "</div>";
			$form_data .= elgg_view("input/plaintext", array(
                    "name" => "group_member_emails", 
                    "id" => "group_tools_group_invite_email_input",
            ));
			$form_data .= "</div>";
		}
		
		//invite by cvs upload
		if($invite_csv ==  "yes"){
			$tabs["csv"] = array(
				"text" => elgg_echo("group_tools:group:invite:csv"),
				"href" => "#",
				"rel" => "users",
				"priority" => 500,
				"onclick" => "group_tools_group_invite_switch_tab(\"csv\");"
			);
			
			$form_data .= "<div id='group_tools_group_invite_csv'>";
			$form_data .= "<div>" . elgg_echo("group_tools:group:invite:csv:description") . "</div>";
			$form_data .= elgg_view("input/file", array("name" => "csv"));
			$form_data .= "</div>";
		}
		
	} else {
		// only friends
		$form_data = $friendspicker;
	}
	
	// optional text
	$form_data .= elgg_view_module("aside", elgg_echo("group_tools:group:invite:text"), elgg_view("input/longtext", array("name" => "comment")));
	
	// renotify existing invites
	if ($group->canEdit()) {
		$form_data .= "<div>";
		$form_data .= "<input type='checkbox' name='resend' value='yes' />";
		$form_data .= "&nbsp;" . elgg_echo("group_tools:group:invite:resend");
		$form_data .= "</div>";
	}
	
	// build tabs
	if(!empty($tabs)){
		foreach($tabs as $name => $tab){
			$tab["name"] = $name;
				
			elgg_register_menu_item("filter", $tab);
		}
		echo elgg_view_menu("filter", array("sort_by" => "priority"));
	}
	
	// show form
	echo $form_data;
	
	// show buttons
	echo '<div class="elgg-foot">';
	echo elgg_view('input/hidden', array('name' => 'forward_url', 'value' => $forward_url));
	echo elgg_view('input/hidden', array('name' => 'group_guid', 'value' => $group->guid));
	echo elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo('invite')));
	if(elgg_is_admin_logged_in()){
		echo elgg_view("input/submit", array('name' => 'submit', "value" => elgg_echo("group_tools:add_users"), "onclick" => "return confirm(\"" . elgg_echo("group_tools:group:invite:add:confirm") . "\");"));
	}
	echo '</div>';
	
?>
<script type="text/javascript">
	function group_tools_group_invite_switch_tab(tab){
		$('#invite_to_group li').removeClass('elgg-state-selected');

		$('#invite_to_group li.elgg-menu-item-' + tab).addClass('elgg-state-selected');

		switch(tab){
			case "users":
				$('#group_tools_group_invite_friends').hide();
				$('#group_tools_group_invite_email').hide();
				$('#group_tools_group_invite_csv').hide();
				$('#group_tools_group_invite_collections').hide();
				
				$('#group_tools_group_invite_users').show();
				break;
			case "email":
				$('#group_tools_group_invite_friends').hide();
				$('#group_tools_group_invite_users').hide();
				$('#group_tools_group_invite_csv').hide();
				$('#group_tools_group_invite_collections').hide();
				
				$('#group_tools_group_invite_email').show();
				break;
			case "csv":
				$('#group_tools_group_invite_friends').hide();
				$('#group_tools_group_invite_users').hide();
				$('#group_tools_group_invite_email').hide();
				$('#group_tools_group_invite_collections').hide();
				
				$('#group_tools_group_invite_csv').show();
				break;
			case "collections":
				$('#group_tools_group_invite_friends').hide();
				$('#group_tools_group_invite_users').hide();
				$('#group_tools_group_invite_email').hide();
				$('#group_tools_group_invite_csv').hide();
				
				$('#group_tools_group_invite_collections').show();
				break;
			default:
				$('#group_tools_group_invite_users').hide();
				$('#group_tools_group_invite_email').hide();
				$('#group_tools_group_invite_csv').hide();
				$('#group_tools_group_invite_collections').hide();
				
				$('#group_tools_group_invite_friends').show();
				break;
		}
	}

	function group_tools_toggle_all_friends(){

		if($('#friends_toggle span:first').is(':visible')){
			$('#friends-picker1 input[type="checkbox"]').attr("checked", "checked");
		} else {
			$('#friends-picker1 input[type="checkbox"]').removeAttr("checked");
		}

		$('#friends_toggle span').toggle();
	}
</script>
