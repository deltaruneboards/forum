<?php
/**
 * MoodMod — English language strings
 */

if (!defined('SMF'))
	die('Hacking attempt...');

// --- Profile ---
$txt['moodmod_profile_tab']       = 'My Mood';
$txt['moodmod_pick_instructions'] = 'Choose the mood that best describes how you are feeling right now. It will be shown on your profile and in posts.';
$txt['moodmod_no_mood']           = 'No Mood';
$txt['moodmod_save']              = 'Save Mood';
$txt['moodmod_saved']             = 'Your mood has been updated successfully.';
$txt['moodmod_not_allowed']       = 'Your member group does not have permission to set a mood.';

// --- Profile — badge colour picker ---
$txt['moodmod_color_instructions'] = 'Pick a background colour for your mood badge, or choose ∅ to use the default.';
$txt['moodmod_color_default']      = 'Default colour';
$txt['moodmod_color_custom']       = 'Custom colour';
$txt['moodmod_color_hue']          = 'Hue';
$txt['moodmod_color_sat']          = 'Saturation';
$txt['moodmod_color_light']        = 'Lightness';
$txt['moodmod_color_set']          = 'Set';

// --- Badge (used in JS / CSS title attributes) ---
$txt['moodmod_feeling']           = 'Feeling';

// --- Admin — general ---
$txt['moodmod_admin_label']       = 'Mood Mod';
$txt['moodmod_tab_settings']      = 'Settings';
$txt['moodmod_tab_moods']         = 'Manage Moods';
$txt['moodmod_settings_saved']    = 'Settings saved successfully.';

// --- Admin — settings page ---
$txt['moodmod_enabled_label']     = 'Enable Mood Mod';
$txt['moodmod_enabled_desc']      = 'Turn the mood system on or off globally.';
$txt['moodmod_show_posts_label']  = 'Show mood badge in posts';
$txt['moodmod_show_posts_desc']   = 'Display the mood badge in the member info sidebar next to each post.';
$txt['moodmod_groups_label']      = 'Allowed member groups';
$txt['moodmod_groups_desc']       = 'Allows you to choose which groups that are able to set a mood. Hold Ctrl / Cmd to select multiple.';
$txt['moodmod_groups_hint']       = 'Select "All Members" to allow everyone, or pick specific groups.';
$txt['moodmod_all_groups']        = 'All Members';

// --- Admin — mood list ---
$txt['moodmod_no_moods']          = 'To create a custom mood, click <strong>Add Mood</strong> to get started.';
$txt['moodmod_add_mood']          = 'Add Mood';
$txt['moodmod_edit_mood']         = 'Edit Mood';
$txt['moodmod_confirm_delete']    = 'Are you sure you want to delete this mood? Members who selected it will be reset to "No Mood".';

// --- Admin — mood list columns ---
$txt['moodmod_col_emoji']         = 'Emoji';
$txt['moodmod_col_name']          = 'Name';
$txt['moodmod_col_description']   = 'Description';
$txt['moodmod_col_order']         = 'Order';
$txt['moodmod_col_active']        = 'Active';
$txt['moodmod_col_actions']       = 'Actions';

// --- Admin — add/edit form ---
$txt['moodmod_field_emoji']       = 'Emoji';
$txt['moodmod_field_emoji_hint']  = 'Paste or type an emoji character (e.g. 😊), or click "Choose emoji" to pick one.';
$txt['moodmod_choose_emoji']      = 'Choose emoji';
$txt['moodmod_field_name']        = 'Name';
$txt['moodmod_field_desc']        = 'Description';
$txt['moodmod_field_order']       = 'Sort Order';
$txt['moodmod_field_order_hint']  = 'Lower numbers appear first.';
$txt['moodmod_field_active']      = 'Active';

// --- Errors ---
$txt['moodmod_error_name']        = 'Please enter a name for the mood.';
$txt['moodmod_cancel']            = 'Cancel';
