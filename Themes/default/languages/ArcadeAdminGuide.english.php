<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

global $txt;

$txt['arcadeHooksGuidance'] = '
[color=blue][b][size=12pt][u]Arcade Hooks[/u][/size][/b][/color]
[hr]
[b][size=10pt]integrate_arcade_match[/size][/b]
[list]
[li][b]Called from[/b]: ArcadeGame.php, just after arena match data has been saved.[/li]
[li][b]Purpose[/b]: Allows you to use the arena score data variables (read only).[/li]
[li][b]Accepts[/b]: 1 function name.[/li]
[li][b]Sends[/b]: $id_match, $user_info[\'id\'], $matchInfo[\'current_round\'], $submit_info[\'score\'], $submit_info[\'duration\'], $submit_info[\'end_time\']
[list]
[li]$id_match - match id number[/li]
[li]$user_info[\'id\'] - user id of submitted score[/li]
[li]$matchInfo[\'current_round\'] - round number[/li]
[li]$matchInfo[\'name\'] - match name[/li]
[li]$submit_info[\'score\'] - game score[/li]
[li]$submit_info[\'duration\'] - length of time game was played[/li]
[li]$submit_info[\'end_time\'] - when the score was submitted[/li]
[/list]
[/li]
[/list]
[hr]

[b][size=10pt]integrate_arcade_score[/size][/b]
[list]
[li][b]Called from[/b]: ArcadeGame.php, just after a game score has been saved.[/li]
[li][b]Purpose[/b]: Allows you to use the game score data variables (read only).[/li]
[li][b]Accepts[/b]: 1 function name.[/li]
[li][b]Sends[/b]: $context[\'game\'], $member, $score
[list]
[li]$context[\'game\'] - array of game data[/li]
[li]$member - array of member data (id, name, ip)[/li]
[li]$score - array of score data (score, duration, endTime)[/li]
[/list]
[/li]
[/list]
[hr]

[b][size=10pt]integrate_arcade_guest[/size][/b]
[list]
[li][b]Called from[/b]: ArcadeGame.php, just after a guest game score has been saved.[/li]
[li][b]Purpose[/b]: Allows you to use the game score data variables (read only).[/li]
[li][b]Accepts[/b]: 1 function name.[/li]
[li][b]Sends[/b]: $context[\'game\'], $submit_info[\'score\']
[list]
[li]$context[\'game\'] - array of game data[/li]
[li]$submit_info[\'score\'] - game score[/li]
[/list]
[/li]
[/list]
[hr]

[b][size=10pt]Example usage for adding the above hooks:[/size][/b]
add_integration_function(\'integrate_arcade_match\', \'modName_arcade_match\');
add_integration_function(\'integrate_arcade_score\', \'modName_arcade_score\');
add_integration_function(\'integrate_arcade_guest\', \'modName_arcade_guest\');
[hr]

[b][size=10pt]Example usage for removing the above hooks:[/size][/b]
remove_integration_function(\'integrate_arcade_match\', \'modName_arcade_match\');
remove_integration_function(\'integrate_arcade_score\', \'modName_arcade_score\');
remove_integration_function(\'integrate_arcade_guest\', \'modName_arcade_guest\');
[hr]

[b][size=10pt]Example usage for adding functions to your modification:[/size][/b]
[b][size=7pt]Note that functions are provided with arguments which are arrays and/or variables.
You can use var_dump or print_r to see what each array or string contains.[/size][/b]
[code]
function modName_arcade_match($id_match, $userid, $matchRound, $matchName, $score, $duration, $end_time)
{
  // use above arrays & variables as you wish (read only)
}

function modName_arcade_score($game, $member, $score)
{
  // use arrays as you wish (read only)
  // log_error($score[\'score\']);
}

function modName_arcade_guest($game, $score)
{
  // use above array & variable as you wish (read only)
}

[/code]
[hr]

[color=blue][b][size=12pt][u]Arcade List Of Games Function[/u][/size][/b][/color]
[hr]
[b][size=10pt]Arcade_gameData($conditions)[/size][/b]
This function is available which returns an array containing a list of games in the database.
All game data from the arcade_games table is included. The function allows unique query conditions as a string.
The default conditions are: $conditions = \'WHERE enabled=1 ORDER BY id_game DESC\';
This function can be called/used in your modification\'s PHP files.
[hr]

[color=blue][b][size=12pt][u]Arcade Mod Settings Global Variable[/u][/size][/b][/color]
[hr]
The use of $arcadeModSettings was introduced in SMF-Arcade v2.7.0.2+ voiding the previous use of SMF\'s $modSettings for any SMF-Arcade generalized settings.
Any old portal blocks or plug-ins that have not been updated may no longer function due to this change.

[b][size=10pt]$arcadeModSettings[/size][/b]
This global variable is available for the arcade which is an array containing all forum wide SMF-Arcade settings.
It is used in the exact same way as the SMF global $modSettings but its data is stored in the arcade_modsettings DB table.
All of its data can be manipulated in the same way as $modSettings except the function names for usage are obviously different & are available on site load from ../ArcadeSources/ArcadeHooks.php

[b]updateArcadeSettings($changeArray, $update)[/b]
- $changeArray should include: variable => value ( ie. updateArcadeSettings(array(\'myvar\' => $myval)) )
- $update boolean either update current value else replace/create a new value

[b]arcadeSaveDBSettings($config_vars)[/b]
- $config_vars is the general array of values created in your typical SMF admin template ( used exactly the same way as saveDBSettings() )

[b]arcadePrepareDBSettingContext($config_vars)[/b]
- $config_vars is the general array of values created in your typical SMF admin template ( used exactly the same way as prepareDBSettingContext() )
- also includes data from the \'integrate_prepare_db_settings\' integration hook
[hr]

[color=blue][b][size=12pt][u]SMF Hooks For Admin & Profiles[/u][/size][/b][/color]
[hr]
SMF-Arcade does have admin functions that you can use shown below for skins & lists, but you have the option of using SMF\'s built in hooks for your Admin & User Profile settings.
Please see SMF documentation for instructions regarding the use of those hooks: [url="https://wiki.simplemachines.org/smf/Integration_hooks#List_of_Integration_Hooks"]List of SMF Integration Hooks[/url]

[color=blue][b][size=12pt][u]Arcade Custom Skins & Mobile Skins[/u][/size][/b][/color]
[hr]
Custom skins & mobile skins can be added to SMF-Arcade via the database and custom source + template files.
Any source files you provide should be located in the "ArcadeSources" directory path & any template files should be located in the default theme path.
Language files can be loaded in your source file using the existing SMF loadLanguage() function.
For this feature, you should have the ability to create your own arcade plug-in that writes the necessary data to the table shown below.
Creating custom source files & templates is also your responsibility if you want to use this option.
[i]The admin_function will store its data in the "arcade_modsettings" DB table & will be available from the $arcadeModSettings global (shown above).[/i]
[i]Note: The skin tables have been changed for SMF-Arcade v2.7.0.2 therefore any plug-ins designed for previous SMF-Arcade versions will need to be updated.[/i]

[b][i][color=#696969]Database table for adding skins:[/color] [color=#663399]arcade_skins[/color]
[color=#696969]Database table for adding mobile skins:[/color] [color=#663399]arcade_mobile_skins[/color]
[color=#696969]Structure for both tables:[/color][color=#663399]
id_skin, skin_name, skin_source_file, skin_function, admin_function, lang_function, skin_template, enabled[/color]

[color=#696969]id_skin is the key column which auto increments when you add a custom skin.[/color]

Explanation of columns:
[color=#663399]skin_name[/color] : [color=#696969]The language variable containing your skin name ( ie. $txt[\'my_unique_skin_name\'] = \'Example Skin Name\'; )[/color]
[color=#663399]skin_source_file[/color] : [color=#696969]name of custom php file located in ../ArcadeSources directory path[/color]
[color=#663399]skin_function[/color] : [color=#696969]function being used in your source file (Imo use global $context to store your data for the template)[/color]
[color=#663399]admin_function[/color] : [color=#696969]return array of settings available in the arcade admin section[/color] (use this function to load your admin language file)
[color=#663399]lang_function[/color] : [color=#696969]use global $txt & load your language file for the skin[/color]
[color=#663399]skin_template[/color] : [color=#696969]name of template file located in ../Themes/default directory path[/color]
[color=#663399]enabled[/color] : [color=#696969]0 or 1 to disable/enable template -> when enabled this will appear as an option in the admin & to your users[/color]

There is a global variable setting available that will toggle whether the toolbar is shown on the arena and stats pages.
You can set the variable to true if you want the toolbar hidden else it will be shown by default.
Put this setting in your skin source file.
[/i][/b]
[code]
global $arcadeTempSettings;

$arcadeTempSettings[\'arcade_hide_buttons\'] = true;
[/code]

[b][i][color=#696969]The Mobile Dark Skin/List plug-in is currently available for the Arcade from the download section. You can peruse its files to get an idea on how to use this feature.[/color][/i][/b]

[color=#696969]Example of adding the appropriate database entry for a custom skin:[/color]
[code]
$smcFunc[\'db_insert\'](\'insert\',
    \'{db_prefix}arcade_skins\',
    array(\'skin_name\' => \'string\', \'skin_source_file\' => \'string\', \'skin_function\' => \'string\', \'admin_function\' => \'string\', \'lang_function\' => \'string\', \'skin_template\' => \'string\', \'enabled\' => \'int\'),
    array(\'my_unique_skin_name\', \'Subs-ArcadeSkinCustom1.php\', \'myarcade_skin_func1\', \'myarcade_skin_settings1\', \'myarcade_lang_func1\', \'ArcadeCustomSkin1\', 1),
    array(\'id_skin\')
);
[/code]
[hr]

[color=blue][b][size=12pt][u]Arcade Custom Lists & Mobile Lists[/u][/size][/b][/color]
[hr]
Custom lists & mobile lists can be added to SMF-Arcade via the database and custom source + template files.
Any source files you provide should be located in the "ArcadeSources" directory path & any template files should be located in the default theme path.
Language files can be loaded in your source file using the existing SMF loadLanguage() function.
For this feature, you should have the ability to create your own arcade plug-in that writes the necessary data to the table shown below.
Creating custom source files & templates is also your responsibility if you want to use this option.
[i]The admin_function will store its data in the "arcade_modsettings" DB table & will be available from the $arcadeModSettings global (shown above).[/i]
[i]Note: The list tables have been changed for SMF-Arcade v2.7.0.2 therefore any plug-ins designed for previous SMF-Arcade versions will need to be updated.[/i]

[b][i][color=#696969]Database table for adding lists:[/color][color=#663399] arcade_lists[/color]
[color=#696969]Database table for adding mobile lists:[/color][color=#663399] arcade_mobile_lists[/color]
[color=#696969]Structure for both tables:[/color][color=#663399]
id_list, list_name, list_source_file, list_function, admin_function, lang_function, list_template, enabled[/color]

[color=#696969]id_list is the key column which auto increments when you add a custom list.[/color]

Explanation of columns:
[color=#663399]list_name[/color] : [color=#696969]The language variable containing your list name ( ie. $txt[\'my_unique_list_name\'] = \'Example List Name\'; )[/color]
[color=#663399]list_source_file[/color] : [color=#696969]name of custom php file located in ../ArcadeSources directory path (use this function to load your language file)[/color]
[color=#663399]list_function[/color] : [color=#696969]function being used in your source file (Imo use global $context to store your data for the template)[/color]
[color=#663399]admin_function[/color] : [color=#696969]return array of settings available in the arcade admin section[/color] (use this function to load your admin language file)
[color=#663399]lang_function[/color] : [color=#696969]use global $txt & load your language file for the list[/color]
[color=#663399]list_template[/color] : [color=#696969]name of template file located in ../Themes/default directory path[/color]
[color=#663399]enabled[/color] : [color=#696969]0 or 1 to disable/enable template -> when enabled this will appear as an option in the admin & to your users[/color]

[b][i][color=#696969]The Mobile Dark Skin/List plug-in is currently available for the Arcade from the download section. You can peruse its files to get an idea on how to use this feature.[/color][/i][/b]

[/i][/b]
[color=#696969]Example of adding the appropriate database entry for a custom list:[/color]
[code]
$smcFunc[\'db_insert\'](\'insert\',
    \'{db_prefix}arcade_lists\',
    array(\'list_name\' => \'string\', \'list_source_file\' => \'string\', \'list_function\' => \'string\', \'admin_function\' => \'string\', \'lang_function\' => \'string\', \'list_template\' => \'string\', \'enabled\' => \'int\'),
    array(\'my_unique_list_name\', \'arcadeCustomList2.php\', \'mylistFunc2\', \'myadminfunc2\', \'mylangfunc2\', \'mylisttemplate2\', 1),
    array(\'id_list\')
);
[/code]

[hr]
[color=blue][b][size=12pt][u]Reporting Bugs[/u][/size][/b][/color]
Please report bugs to the [url="https://web-develop.ca/index.php?board=9.0"]Official Support Thread[/url]!
This helps make this modification better!

Copyright (c) 2004 - 2012, [url="http://www.madjoki.com/"]Niko Pahajoki[/url]
Copyright (c) 2015 - 2024, [url="https://web-develop.ca/index.php?page=arcade_license_BSD2"]Chen Zhen[/url]
Current support provided by Chen Zhen @ [url="https://web-develop.ca/"]web-develop.ca[/url]
SMF-Arcade License: [url="https://web-develop.ca/index.php?page=arcade_license_BSD2"]BSD 2[/url]';

?>