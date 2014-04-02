<?php

gatekeeper();

	$group = elgg_get_page_owner_entity();

	$title = elgg_echo('groups:invite:title');

	elgg_push_breadcrumb($group->name, $group->getURL());
	elgg_push_breadcrumb(elgg_echo('groups:invite'));

	if ($group && group_member_invite_can_invite($group)) {
		$content = elgg_view_form('groups/invite', array(
			'id' => 'invite_to_group',
			'class' => 'elgg-form-alt mtm',
		), array(
			'entity' => $group,
		));
	} else {
		$content .= elgg_echo('groups:noaccess');
	}

	$params = array(
		'content' => $content,
		'title' => $title,
		'filter' => '',
	);
	$body = elgg_view_layout('content', $params);

	echo elgg_view_page($title, $body);