<?php

/**
 * @package Enhanced Quick Reply
 * @version 1.0.2
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2022, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

class QuickReply
{
	/**
	 * Get the quick reply behavior for the user
	 * 
	 * @return string The actual and final behavior
	 */
	private static function behavior() : string
	{
		global $options, $modSettings;

		// The default and general behavior
		$behavior = $modSettings['QuickReply_behavior_general'] ?? 'full';

		// If the user has a custom behavior, use it
		if (allowedTo('QuickReply_behavior'))
			$behavior = $options['QuickReply_behavior'] ?? $behavior;

		return $behavior;
	}

	/**
	 * Hide the quick reply box
	 */
	public static function display_buttons() : void
	{
		global $context, $txt, $user_info, $memberContext, $modSettings;

		// Get the quick reply behavior
		$context['QuickReply_behavior'] = self::behavior();

		// Full?
		if ($context['QuickReply_behavior'] === 'full')
			return;

		// Language?
		loadLanguage('QuickReply/');

		// Add the javascript goodies
		addJavaScriptVar('quickreply_placeholder', $txt['QuickReply_reply_value'], true);
		loadJavascriptFile('quickreply.js', ['defer' => true, 'default_theme' => true], 'enhanced_quickreply');

		// What are we doing to the quick reply box?
		switch ($context['QuickReply_behavior'])
		{
			// Disabled? Hide it
			case 'disabled':
				addInlineCss('#quickreply { display: none; }');
				break;

			// Collapsed? Hide it and add some JS to show it
			case 'collapsed':
				// CSS
				addInlineCss('#quickreply > div.cat_bar { cursor: pointer; } #quickreply_options { scale: 0; height: 0; }');
				// JS
				addInlineJavascript('$(document).ready(function() { quickreply_collapsed_behavior(); });', true);
				break;

			// Minimalistic? Load the CSS file
			case 'minimalistic':
				// Add the avatar, hard way... inline.
				loadMemberData($user_info['id']);
				loadMemberContext($user_info['id']);
				addInlineCss('
					#quickreply form:before {
						background-image: url(' . (empty($user_info['is_guest']) ? $memberContext[$user_info['id']]['avatar']['href'] : $modSettings['avatar_url'] . '/default.png') . ');
					}
				');
				// Load the CSS file
				loadCSSFile('quickreply.css', ['minimize' => true, 'default_theme' => true, 'order_pos' => 12000], 'enhanced_quickreply');
				// JS
				addInlineJavascript('$(document).ready(function() { quickreply_minimalistic_behavior(); });', true);
				break;
		}
	}

	/**
	 * Change the quote selected button
	 * 
	 * @param array $output The message output
	 */
	public static function prepare_display_context(&$output) : void
	{
		global $context;

		// Collapsed? Add some classes
		if ($context['QuickReply_behavior'] == 'collapsed')
		{
			// Add a class to the quote selected button
			$output['quickbuttons']['quote_selected']['class'] = 'quoteSelected';
			// Add a class to the quote button
			$output['quickbuttons']['quote']['class'] = 'quoteAll';
		}
		
		// change the behavior of quotes when it's disabled
		if ($context['QuickReply_behavior'] === 'disabled')
		{
			// Can't quote selected text, sadly
			$output['quickbuttons']['quote_selected']['show'] = false;
			// Quotes go to the post page, so no javascript
			unset($output['quickbuttons']['quote']['javascript']);
		}
	}

	/**
	 * Add theme options for users
	 */
	public static function theme_options() : void
	{
		global $context, $txt, $modSettings;

		// Are they allowed to change the behavior?
		if (!allowedTo('QuickReply_behavior'))
			return;

		// Language
		loadLanguage('QuickReply/');

		// Behavior options
		$context['theme_options'][] = $txt['quick_reply'];
		$context['theme_options'][] = [
			'id' => 'QuickReply_behavior',
			'label' => $txt['QuickReply_behavior_general'],
			'description' => $txt['QuickReply_behavior_desc'],
			'options' => [
				'full' => $txt['QuickReply_full'],
				'collapsed' => $txt['QuickReply_collapsed'],
				'minimalistic' => $txt['QuickReply_minimalistic'],
				'disabled' => $txt['QuickReply_disabled'],
			],
			'enabled' => allowedTo('QuickReply_behavior'),
			'default' => $modSettings['QuickReply_behavior_general'] ?? 'full',
		];
		
	}

	/**
	 * Add the settings to the topic settings area
	 * 
	 * @param array $config_vars The settings
	 */
	public static function modify_topic_settings(&$config_vars) : void
	{
		global $txt;

		// Language
		loadLanguage('QuickReply/');

		if (!empty($config_vars))
			$config_vars[] = '';

		// Settings
		$config_vars[] = ['title', 'QuickReply_title'];
		$config_vars[] = ['select', 'QuickReply_behavior_general', [
			'full' => $txt['QuickReply_full'],
			'collapsed' => $txt['QuickReply_collapsed'],
			'minimalistic' => $txt['QuickReply_minimalistic'],
			'disabled' => $txt['QuickReply_disabled'],
		], 'subtext' => $txt['QuickReply_behavior_desc']];
		$config_vars[] = ['permissions', 'QuickReply_behavior', 'label' => $txt['QuickReply_permissions'], 'subtext' => $txt['QuickReply_permissions_desc']];
	}

	/**
	 * Load the permissions for the quick reply
	 * 
	 * @param array $permissionList The list of permissions
	 */
	public static function load_permissions(&$permissionGroups, &$permissionList) : void
	{
		// Language
		loadLanguage('QuickReply/');

		// Add the permission
		$permissionList['membergroup']['QuickReply_behavior'] = [false, 'general'];
	}

	/**
	 * Illegal guest permissions
	 */
	public static function load_illegal_guest_permissions() : void
	{
		global $context;

		$context['non_guest_permissions'][] = 'QuickReply_behavior';
	}
}
