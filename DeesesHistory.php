<?php
 // This plugin logs username change. Half the size of username_history, and easier customization!
$plugins->add_hook("member_profile_end","DeesesHistory");
$plugins->add_hook("usercp_do_changename_end","UsernameChanged");
$plugins->add_hook('misc_start','ShowNames');

function DeesesHistory_info()
{
	return array(
		"name"			=> "Deesus History - Username History",
		"description"	=> "This plugin displays a history of usernames for each user.",
		"website"		=> "http://personalperson.pw",
		"author"		=> "PersonalPerson",
		"authorsite"	=> "http://personalperson.pw",
		"version"		=> "1.0"
	);
}

function DeesesHistory_activate(){
	global $db;
	//create table to store history
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."deesus_usernames` (
		`rid` int(10) NOT NULL AUTO_INCREMENT,
		`uid` int(10) NOT NULL ,
		`uName` VARCHAR( 50 ) NOT NULL,
		`lastChanged` int(50),
		PRIMARY KEY (`rid`),
		UNIQUE KEY `rid` (`rid`)
	)");
	$template[0] = array(
		"tid"		=> NULL,
		"title"		=> "deesus_usernames_username_profile",
		"template"	=> "<strong>Username Changes:</strong> <a href=\"misc.php?action=deesus_usernames&uhuid=\$userid\">{\$numChanges}</a>",
		"sid"		=> "-1",
		"version"	=> 1.0,
		"dateline"	=> time()
	);
	$template[1] = array(
		"tid"		=> NULL,
		"title"		=> "deesus_usernames_display_history",
"template"=>"<html>
<head><title>Username Changes</title>{\$headerinclude}</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"0\" cellpadding=\"10\" class=\"tborder\">
<tr>
<td class=\"thead\" colspan=\"2\"><strong>History of username changes for {\$username}</strong></td>
</tr>
<tr>
<td class=\"tcat\"><strong>Username</strong></td>
<td class=\"tcat\" align=\"center\"><strong>Date</strong></td>
</tr>
{\$previousUsernames}
</table>
{\$footer}
</body>
</html>",
		"sid"		=> "-1",
		"version"	=> 1.0,
		"dateline"	=> time()
	);
	foreach ($template as $row) {
		$db->insert_query("templates", $row);
	}
	require MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("member_profile", '#{\$online_status}#', "{\$online_status}</strong><br />\n<strong>{\$DeesesHistory}</strong>");
}

function DeesesHistory_deactivate(){
	global $db;
	$db->delete_query("templates", "title='deesus_usernames_username_profile'");
	$db->delete_query("templates", "title='deesus_usernames_display_history'");
	$db->write_query("DROP TABLE `".TABLE_PREFIX."deesus_usernames`");
	require MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets('member_profile', '#<br />\n<strong">{\$DeesesHistory}</strong>#', '', 0);
}

function DeesesHistory(){
	global $db, $memprofile, $templates, $DeesesHistory;
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."deesus_usernames WHERE uid='".$memprofile['uid']."'");
	$numChanges = $db->num_rows($query);
	$userid = $memprofile['uid'];
	eval("\$DeesesHistory = \"".$templates->get("deesus_usernames_username_profile")."\";");
}

function UsernameChanged(){
	global $mybb, $db;
	$db->write_query("INSERT INTO `".TABLE_PREFIX."deesus_usernames` VALUES ('', '".$mybb->user['uid']."', '".$mybb->user['username']."','".time()."')");
}
function ShowNames(){
	global $mybb, $db, $headerinclude, $header, $ShowNames, $footer, $templates;
	
if($mybb->input['action'] == "deesus_usernames" && is_numeric($mybb->input['uhuid']))
{
		$grabuser = $db->simple_select("users", "username", "uid = ".$mybb->input['uhuid']);
		$user = $db->fetch_array($grabuser);
		$username = $user['username'];
		$query = $db->query("SELECT * FROM ".TABLE_PREFIX."deesus_usernames WHERE uid='".$mybb->input['uhuid']."'");
		$previousUsernames = '';
		while($row = $db->fetch_array($query)){
			$previousUsernames=$previousUsernames."<tr><td class=\"trow1\" width=\"80%\">".$row['uName']."</td>
            <td class=\"trow1\" align=\"center\">".my_date($mybb->settings['dateformat'], $row['lastChanged'])."  ".my_date($mybb->settings['timeformat'], $row['lastChanged'])."</td>
            </tr>";
		}
		
	if(!$previousUsernames)
	{
		$previousUsernames ="<tr><td class=\"trow1\" colspan=\"2\">No changes are logged.</td></tr>";
	}
		eval("\$page=\"".$templates->get("deesus_usernames_display_history")."\";");
		output_page($page);
}

}
?>
