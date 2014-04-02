<?php

if (elgg_is_active_plugin('group_tools')) {
	echo elgg_view('group_member_invite/invite_group_tools', $vars);
}
else {
	echo elgg_view('group_member_invite/invite', $vars);
}