<?php
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />
         Please make sure IN_MYBB is defined.");
}

/* --- Hooks: --- */

$plugins->add_hook("usercp_avatar_start", "chehrak_usercp_avatar_start");
$plugins->add_hook("usercp_do_avatar_start", "chehrak_usercp_do_avatar_start");

/* --- Plugin API: --- */

function chehrak_info()
{
    return array(
        "name"          => "Chehrak",
        "description"   => "This plugin lets users display their Chehrak avatar.",
        "website"       => "http://www.chehrak.com",
        "author"        => "Andreas Klauer",
        "authorsite"    => "mailto:Andreas.Klauer@metamorpher.de",
        "version"       => "0.1",
        "guid"          => "1e97df95cb52aaf639aaa37f58c0fbee",
        "compatibility" => "14*,15*,16*"
        );
}

function chehrak_deactivate()
{
    global $db;

    require_once MYBB_ROOT."inc/adminfunctions_templates.php";

    find_replace_templatesets('usercp_avatar',
                              "#([\r\n ]*\\{\\\$chehrak\\}[\r\n ]*)#",
                              "\n",
                              0); // work around MyBB bug

    $db->delete_query("templates", "title='chehrak'");
}

function chehrak_activate()
{
    global $db;

    // Remove stuff first to avoid doubling problem.
    chehrak_deactivate();

    // Insert {$chehrak} into the usercp_avatar template.
    require_once MYBB_ROOT."inc/adminfunctions_templates.php";

    find_replace_templatesets('usercp_avatar',
                              "#(</table>[\r\n ]*<br />)#i",
                              "\n{\$chehrak}\n\\1");

    $template = array("title" => "chehrak",
                      "sid" => "-1",
                      "template" => "
<tr>
<td class=\"trow1\" width=\"40%\">
<strong>{\$lang->chehrak}</strong>
</td>
<td class=\"trow1\">
<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
<tbody>
<tr>
<td>
{\$lang->chehrak_caption}<br />
{\$lang->chehrak_email}
</td>
<td align=\"right\">
<label><input type=\"checkbox\" name=\"chehrak\" value=\"1\" /><img align=\"middle\" src=\"{\$chehrak_url}\" alt=\"{\$lang->chehrak}\" title=\"{\$lang->chehrak}\"></label>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
",
        );

    $db->insert_query("templates", $template);
}

/* --- Helpers: --- */

function chehrak_get_link($email)
{
    return "http://rokh.chehrak.com/".md5(trim(my_strtolower($email)));
}

/* --- Functionality: --- */

/*
 * Display a Chehrak checkbox.
 */
function chehrak_usercp_avatar_start()
{
    global $mybb, $lang, $templates, $chehrak;

    $lang->load('chehrak');

    $lang->chehrak_email = $lang->sprintf($lang->chehrak_email,
                                           $mybb->user['email']);

    $chehrak_url = chehrak_get_link($mybb->user['email']);

    eval("\$chehrak = \"".$templates->get("chehrak")."\";");
}

/*
 * Check if the user checked the Chehrak box,
 * and then just set the Avatar URL to the Chehrak URL.
 */
function chehrak_usercp_do_avatar_start()
{
    global $mybb;

    if($mybb->input['chehrak'])
    {
        $mybb->input['avatarurl'] = chehrak_get_link($mybb->user['email']);
    }
}

/* --- End of file. --- */
?>
