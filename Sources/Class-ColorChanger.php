<?php
/**
 * @package Curve2 Color Changer
 * @author SMF Customization Team (www.simplemachines.org)
 * @license BSD 3-clause
 * @version 1.4
 */

class ColorChanger
{
	/**
	 * Editing the theme's colors
	 *
	 * Called by
	 *		integrate_theme_settings
	 */
	public static function themeSettings()
	{
		global $context, $settings, $txt;

		// This only applies to themes that have color changes
		if ($settings['theme_id'] != 1 && empty($settings['color_changes']))
			return;

		// Load the language strings
		loadLanguage('ColorChanger');

		// Load more javascript
		loadJavaScriptFile('ColorChanger.js', array('minimize' => true), 'smf_color_changer');

		$color_palettes = self::getColorPalette();
		if (!empty($color_palettes))
		{
			addJavaScriptVar('color_palettes', json_encode($color_palettes));
			addJavaScriptVar('txt_cc_palettes', '"' . $txt['cc_palettes_title'] . '"');
		}

		// The problem with color inputs is that they don't take an empty value >:(
		// So if the user wants to revert to the default colors, we have to change it to a text input using Js
		$controls = function ($input_id) use ($color_palettes, $txt)
		{
			// Only set colors if the palette exists for them
			if (!isset($color_palettes['default'][str_replace('cc_', '', $input_id)]))
				return;

			return '<a onclick="$(\'#' . $input_id . '\').attr(\'type\', \'color\').val(\'' . $color_palettes['default'][str_replace('cc_', '', $input_id)] . '\')">' . $txt['cc_default_color'] . '</a>';
		};

		// Add the settings to the default theme's settings page (Curve2)
		$cc_settings = array(
			$txt['cc_color_changer'] . (!empty($color_palettes) ? ' <a onclick="return applyColorPalette(\'default\')" id="cc_reset_all">[' . $txt['cc_reset_all'] . ']</a>' : ''),
			array(
				'id' => 'cc_primary_color',
				'label' => $txt['cc_primary_color'],
				'description' => $controls('cc_primary_color'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_secondary_color',
				'label' => $txt['cc_secondary_color'],
				'description' => $controls('cc_secondary_color'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_background',
				'label' => $txt['cc_background'],
				'description' => $controls('cc_background'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_foreground',
				'label' => $txt['cc_foreground'],
				'description' => $controls('cc_foreground'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_top_section',
				'label' => $txt['cc_top_section'],
				'description' => $controls('cc_top_section'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_footer',
				'label' => $txt['cc_footer'],
				'description' => $controls('cc_footer'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_links',
				'label' => $txt['cc_links'],
				'description' => $controls('cc_links'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_gradient_start',
				'label' => $txt['cc_gradient_start'],
				'description' => $controls('cc_gradient_start'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_gradient_end',
				'label' => $txt['cc_gradient_end'],
				'description' => $controls('cc_gradient_end'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_blocks_color',
				'label' => $txt['cc_blocks_color'],
				'description' => $controls('cc_blocks_color'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_blocks_alternate_color',
				'label' => $txt['cc_blocks_alternate_color'],
				'description' => $controls('cc_blocks_alternate_color'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_content_target_bg',
				'label' => $txt['cc_content_target'],
				'description' => $controls('cc_content_target_bg'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_special_titles_color',
				'label' => $txt['cc_special_titles_color'],
				'description' => $controls('cc_special_titles_color'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_borders_color',
				'label' => $txt['cc_borders_color'],
				'description' => $controls('cc_borders_color'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_buttons_text_color',
				'label' => $txt['cc_buttons_text_color'],
				'description' => $controls('cc_buttons_text_color'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_buttons_bg',
				'label' => $txt['cc_buttons_bg'],
				'description' => $controls('cc_buttons_bg'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_buttons_border',
				'label' => $txt['cc_buttons_border'],
				'description' => $controls('cc_buttons_border'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_sticky_topic',
				'label' => $txt['sticky_topic'],
				'description' => $controls('cc_sticky_topic'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_sticky_locked_topic',
				'label' => $txt['sticky_topic'] . ' + ' . $txt['locked_topic'],
				'description' => $controls('cc_sticky_locked_topic'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_unapproved_topic',
				'label' => $txt['mc_unapproved_poststopics'],
				'description' => $controls('cc_unapproved_topic'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_locked_topic',
				'label' => $txt['locked_topic'],
				'description' => $controls('cc_locked_topic'),
				'type' => 'color',
				'size' => 10
			),
			array(
				'id' => 'cc_remove_shadows',
				'label' => $txt['cc_remove_shadows'],
			),
			array(
				'id' => 'cc_admin_only',
				'description' => $txt['cc_admin_only_help'],
				'label' => $txt['cc_admin_only'],
			)
		);

		// If this is a custom theme and is compatible with this mod, use its custom color settings only
		if ($settings['theme_id'] != 1 && !empty($settings['color_changes']))
		{
			// Which exisiting color settings are available
			$available = array();
			foreach ($settings['color_changes'] as $color => $content)
			{
				$available[] = 'cc_' . $color;
			}

			// Unset the unavailable ones
			foreach ($cc_settings as $key => $setting)
			{
				if(empty($setting['id']))
					continue;

				if (!in_array($setting['id'], $available))
					unset($cc_settings[$key]);
				else
				{
					$aKey = array_flip($available)[$setting['id']];
					unset($available[$aKey]);
				}
			}

			// Has the author added new color settings ?
			if(!empty($available))
				foreach ($available as $color)
					$cc_settings[] = array(
						'id' => $color,
						'label' => !empty($txt[$color]) ? $txt[$color] : $txt['cca_unknown_color'],
						'description' => $controls($color),
						'type' => 'color',
						'size' => 10
					);
		}

		// Merge our new settings
		$context['theme_settings'] = array_merge($context['theme_settings'], $cc_settings);

		addInlineCss('#color_palettes{display: flex;flex-wrap: wrap;clear: both;gap:15px;}.cc_p_color{width: 8px;height: 36px;display: inline-block;}#color_palettes .cc_palette{cursor: pointer;display: flex;flex-wrap: wrap;padding: 4px;border-radius: 0;margin: 0 3px;background: transparent;}');
	}

	/**
	 * Outputs the css which overrides the default css
	 *
	 * Called by
	 *		integrate_pre_css_output
	 */
	public static function outputCss()
	{
		global $user_info, $settings;

		// This only applies to the default theme (Curve2)
		if (($settings['theme_id'] == 1 || !empty($settings['color_changes'])) && (empty($settings['cc_admin_only']) || (!empty($settings['cc_admin_only']) && $user_info['is_admin'])))
			addInlineCss(self::buildCss());
	}

	/**
	 * Returns the css code
	 *
	 * @return string
	 */
	public static function buildCss()
	{
		global $settings;

		$color_changes = array(
			'background' => array(
				array(
					'elements' => 'body',
					'properties' => array('background')
				)
			),
			'foreground' => array(
				array(
					'elements' => 'body, strong, .strong, h1, h2, h3, h4, h5, h6, h3.titlebg, h4.titlebg, .titlebg, h3.subbg, h4.subbg, .subbg,
					#detailedinfo dt, #tracking dt, .approvebg',
					'properties' => array('color')
				)
			),
			'primary_color' => array(
				array(
					'elements' => 'div.cat_bar, .amt, .dropmenu li a:hover, .dropmenu li:hover a, .dropmenu li a:focus,
					#top_info > li > a:hover, #top_info > li:hover > a, #top_info > li > a.open, .button.active, .button.active:hover',
					'properties' => array('background', 'border-color')
				),
				array(
					'elements' => '#footer',
					'properties' => array('background')
				),
				array(
					'elements' => '.button.active, .button.active:hover',
					'properties' => array('color' => '#fff')
				)
			),
			'secondary_color' => array(
				array(
					'elements' => '.title_bar',
					'properties' => array('border-top-color', 'border-bottom-color')
				),
				array(
					'elements' => '.dropmenu li a.active, #top_info li a.active, .dropmenu li a.active:hover, .dropmenu li:hover a.active, a.moderation_link,
					a.moderation_link:visited, .new_posts, .generic_bar .bar, .progress_bar .bar',
					'properties' => array('background', 'border-color')
				)
			),
			'links' => array(
				array(
					'elements' => 'a, a:visited, .dropmenu li:hover li a, .dropmenu li li a, .button, .quickbuttons li, .quickbuttons li a,
					.quickbuttons li:hover a, .titlebg a, .subbg a, .quickbuttons li ul li a:hover',
					'properties' => array('color')
				)
			),
			'gradient_end' => array(
				array(
					'elements' => '#wrapper, .dropmenu li ul, .top_menu, .dropmenu li li:hover, .button, .dropmenu li li:hover > a, .dropmenu li li a:focus,
					.dropmenu li li a:hover, #top_section, #search_form .button, .quickbuttons li, .quickbuttons li ul, .quickbuttons li ul li:hover,
					.quickbuttons ul li a:focus, .popup_window, #inner_section, .button:hover, .quickbuttons li:hover, .navigate_section ul, .popup_content, .up_contain,
					.button:focus, #search_form .button:hover, .quickbuttons li:hover',
					'properties' => array('background')
				)
			),
			'gradient_start' => array(
				array(
					'elements' => '.dropmenu li ul, .top_menu, .dropmenu li li:hover, .button, .dropmenu li li:hover > a, .dropmenu li li a:focus,
					.dropmenu li li a:hover, #top_section, #search_form .button, .quickbuttons li, .quickbuttons li ul, .quickbuttons li ul li:hover,
					.quickbuttons ul li a:focus, .popup_window, #inner_section',
					'properties' => array('background-image' => 'linear-gradient(to bottom, {color} 0%, transparent 70%)')
				),
				array(
					'elements' => '.button:hover, .button:focus, .quickbuttons li:hover, .navigate_section ul, .popup_content, .up_contain,
					#search_form .button:hover, .quickbuttons li:hover',
					'properties' => array('background-image' => 'linear-gradient(to bottom, transparent 0%, {color} 70%)')
				)
			),
			'blocks_color' => array(
				array(
					'elements' => '.windowbg:nth-of-type(even), .bg.even, .information, .roundframe, .generic_list_wrapper, .approvebg,
					.windowbg:nth-of-type(odd) blockquote, .windowbg:nth-of-type(even) .bbc_alternate_quote',
					'properties' => array('background')
				)
			),
			'blocks_alternate_color' => array(
				array(
					'elements' => '.windowbg:nth-of-type(odd), .bg.odd, .unread_notify:hover, .title_bar, tr.windowbg:hover,
					.windowbg:nth-of-type(even) blockquote, .windowbg:nth-of-type(odd) .bbc_alternate_quote, .bbc_code',
					'properties' => array('background')
				)
			),
			'content_target_bg' => array(
				array(
					'elements' => '.windowbg:target',
					'properties' => array('background')
				),
			),
			'borders_color' => array(
				array(
					'elements' => '#wrapper, .button, .button:hover, .button:focus, .windowbg, .roundframe, .information, #top_section, .navigate_section ul, .dropmenu li ul, .top_menu,
					.unread_notify, .pm_unread, .alerts_unread, .dropmenu li li:hover > a, .dropmenu li li a:focus, .dropmenu li li a:hover, .up_contain,
					.boardindex_table .board_stats p, .children, #ic_recentposts td, .sub_bar, #info_center .sub_bar, .generic_bar, .progress_bar,
					#detailedinfo dl, #tracking dl, .inner, .signature, .attachments, .under_message, .custom_fields_above_signature, .custom_fields_below_signature,
					.quickbuttons li, .quickbuttons li:hover, .quickbuttons li ul, .quickbuttons li ul li:hover, .action_admin .table_grid td, .generic_list_wrapper,
					#topic_container .windowbg, #topic_icons .information, #messageindex .information, .approvebg, .popup_content, fieldset, #alerts tr.windowbg td,
					blockquote, #manage_boards li.windowbg, #manage_boards li.windowbg:last-child, #footer, .bbc_code, #inner_wrap',
					'properties' => array('border-color')
				),
				array(
					'elements' => '.title_bar',
					'properties' => array('border-left-color', 'border-right-color')
				),
				array(
					'elements' => 'hr',
					'properties' => array('background', 'border-color')
				)
			),
			'top_section' => array(
				array(
					'elements' => '#top_section',
					'properties' => array('background')
				)
			),
			'footer' => array(
				array(
					'elements' => '#footer',
					'properties' => array('background')
				)
			),
			'buttons_text_color' => array(
				array(
					'elements' => 'a.button, .button, .quickbuttons li a, .button:hover, .button:focus, .quickbuttons li:hover>a, .quickbuttons li:hover li a',
					'properties' => array('color')
				),
			),
			'buttons_bg' => array(
				array(
					'elements' => '.button, .quickbuttons li, .button:hover, .button:focus, .quickbuttons li:hover, #search_form .button, #search_form .button:hover',
					'properties' => array('background')
				)
			),
			'buttons_border' => array(
				array(
					'elements' => '.button, .quickbuttons li, .button:hover, .button:focus, .quickbuttons li:hover, #search_form .button, #search_form .button:hover',
					'properties' => array('border-color')
				)
			),
			'special_titles_color' => array(
				array(
					'elements' => '.info .subject, h1.forumtitle a, .poster h4, .poster h4 a, .poster li:hover h4 a, .poster h4 a:hover .poster li h4 a,
					.poster h4 a:focus, #smfAnnouncements dt a, .keyinfo h5 a, .keyinfo h5 a strong, .current_page',
					'properties' => array('color')
				),
				array(
					'elements' => '#smfAnnouncements dt',
					'properties' => array('border-color')
				)
			),
			'sticky_topic' => array(
				array(
					'elements' => '.windowbg.sticky',
					'properties' => array('background')
				),
			),
			'sticky_locked_topic' => array(
				array(
					'elements' => '.windowbg.sticky.locked',
					'properties' => array('background')
				),
			),
			'unapproved_topic' => array(
				array(
					'elements' => '.windowbg.approvetopic, .windowbg.approvepost, .approvebg',
					'properties' => array('background')
				),
			),
			'locked_topic' => array(
				array(
					'elements' => '.windowbg.locked',
					'properties' => array('background')
				),
			),
			'remove_shadows' => array(
				array(
					'elements' => '*',
					'properties' => array('box-shadow' => 'none !important', 'text-shadow' => 'none !important')
				)
			)
		);

		// Does the current theme provide color changing options ?
		if (!empty($settings['color_changes']))
			$color_changes = $settings['color_changes'];

		$defaultPalette = self::getColorPalette()['default'] ?? [];

		// Ignore for some color changes
		$ignoreColorChange = array('remove_shadows');

		// This creates the CSS code string
		$css = '';
		$css_root = '';
		foreach ($color_changes as $color_key => $color)
		{
			// Check for certain conditions
			if (!isset($settings['cc_' . $color_key]) || (!empty($defaultPalette) && $defaultPalette[$color_key] == $settings['cc_' . $color_key] && !in_array($color_key, $ignoreColorChange)))
				continue;

			// Ignore these color changes
			elseif (in_array($color_key, $ignoreColorChange) && empty($settings['cc_' . $color_key]))
				continue;

			// Create the css block
			foreach ($color as $key => $code_block)
			{
				// Using variables?
				if (!empty($code_block['variable']))
				{
					$css_root .= '--' . $code_block['variable'] . ': ' . $settings['cc_' . $color_key] . ';';
				}
				// Elements?
				// Check for properties too
				if (!empty($code_block['elements']) && !empty($code_block['properties']))
				{
					$css .= $code_block['elements'] . ' {';
					foreach ($code_block['properties'] as $property => $value)
					{
						// Is the property really the property ?
						if (is_string($property))
							$css .= $property . ': ' . str_replace('{color}', $settings['cc_' . $color_key], $value) . ';';
						// Otherwise the $value is the $property
						else
							$css .= $value . ': ' . $settings['cc_' . $color_key] . ';';
					}
					$css .= '}';
				}
			}
		}

		if(!empty($css) || !empty($css_root))
		{
			// Add root
			$css = (!empty($css_root) ? (':root' . (isset($settings['color_changes_root']) && !empty($settings['color_changes_root']) ? ',:root'. implode(',:root', $settings['color_changes_root']) : '') . '{' . $css_root . '}' . $css) : $css);
			// Remove tabs and line break
			$css = preg_replace('/[\t\r\n]+/', '', $css);
			// Sandwitch the code
			$css = "\n/* start of Color Changer mod output */\n" . $css . "\n/* end of Color Changer mod output */\n";
		}

		return $css;
	}

	/**
	 * Returns the color palettes array
	 *
	 * @return array
	 */
	private static function getColorPalette()
	{
		global $settings;

		// let's define some ready to use color palettes
		$colors = array(
			'background','foreground','primary_color','secondary_color','top_section','footer','links','gradient_end','gradient_start','blocks_color',
			'blocks_alternate_color','borders_color','buttons_text_color','buttons_bg','buttons_border','special_titles_color', 'remove_shadows'
		);
		$color_palettes = array(
			'default' => array(
				'background'             => '#e9eef2',
				'foreground'             => '#4d4d4d',
				'primary_color'          => '#557ea0',
				'secondary_color'        => '#f49a3a',
				'top_section'            => '#ffffff',
				'footer'                 => '#3e5a78',
				'links'                  => '#334466',
				'gradient_end'           => '#ffffff',
				'gradient_start'         => '#e2e9f3',
				'blocks_color'           => '#f0f4f7',
				'blocks_alternate_color' => '#fdfdfd',
				'content_target_bg'     => '#ffffe0',
				'borders_color'          => '#dddddd',
				'buttons_text_color'     => '#444444',
				'buttons_bg'             => '#ffffff',
				'buttons_border'         => '#dddddd',
				'special_titles_color'   => '#a85400',
				'sticky_topic'             => '#cfdce8',
				'sticky_locked_topic'          => '#e8d8cf',
				'unapproved_topic'     => '#e4a17c',
				'locked_topic'          => '#e7eaef',
				'remove_shadows'         => false,
			),
			'brownish' => array(
				'background'             => '#fcf1ef',
				'foreground'             => '#4d4d4d',
				'primary_color'          => '#b16a52',
				'secondary_color'        => '#dd8b42',
				'top_section'            => '#ffffff',
				'footer'                 => '#b16a52',
				'links'	                 => '#681e11',
				'gradient_end'           => '#ffffff',
				'gradient_start'         => '#f0e7e1',
				'blocks_color'           => '#f5eeeb',
				'blocks_alternate_color' => '#f3ded6',
				'content_target_bg'     => '#f0e6d3',
				'borders_color'          => '#dddddd',
				'buttons_text_color'     => '#681e11',
				'buttons_bg'             => '#ffffff',
				'buttons_border'         => '#dddddd',
				'special_titles_color'   => '#c6574a',
				'sticky_topic'             => '#cad7e3',
				'sticky_locked_topic'          => '#dbc9bf',
				'unapproved_topic'     => '#db9d7b',
				'locked_topic'          => '#dcdfe3',
				'remove_shadows'         => false,
			),
			'redish' => array(
				'background'             => '#fceff0',
				'foreground'             => '#4d4d4d',
				'primary_color'          => '#cb383c',
				'secondary_color'        => '#e87020',
				'top_section'            => '#ffffff',
				'footer'                 => '#cb383c',
				'links'                  => '#842b2d',
				'gradient_end'           => '#ffffff',
				'gradient_start'         => '#fef5f7',
				'blocks_color'           => '#fcf3f3',
				'blocks_alternate_color' => '#f8dadb',
				'content_target_bg'     => '#d1c7c7',
				'borders_color'          => '#dddddd',
				'buttons_text_color'     => '#842b2d',
				'buttons_bg'             => '#ffffff',
				'buttons_border'         => '#dddddd',
				'special_titles_color'   => '#c22529',
				'sticky_topic'             => '#bf9797',
				'sticky_locked_topic'          => '#a37e7e',
				'unapproved_topic'     => '#db9d7b',
				'locked_topic'          => '#b39b9b',
				'remove_shadows'         => true,
			),
			'dark_blue' => array(
				'background'             => '#152348',
				'blocks_alternate_color' => '#0f142d',
				'content_target_bg'     => '#1c1f2e',
				'blocks_color'           => '#121831',
				'borders_color'          => '#1d2e5a',
				'top_section'            => '#ffffff',
				'footer'                 => '#0e1425',
				'buttons_bg'             => '#2573c0',
				'buttons_border'         => '#2b68db',
				'buttons_text_color'     => '#ffffff',
				'foreground'             => '#cdcdcd',
				'gradient_end'           => '#0e1425',
				'gradient_start'         => '#161f3a',
				'links'                  => '#2e6ccb',
				'primary_color'          => '#188dd3',
				'secondary_color'        => '#4e6adc',
				'special_titles_color'   => '#4a8ad0',
				'sticky_topic'             => '#283466',
				'sticky_locked_topic'          => '#1e2645',
				'unapproved_topic'     => '#662c2c',
				'locked_topic'          => '#090e24',
				'remove_shadows'         => true,
			),
			'flat' => array(
				'primary_color'          => '#24292e',
				'secondary_color'        => '#0366d6',
				'background'             => '#f6f8fa',
				'foreground'             => '#000000',
				'top_section'            => '#ffffff',
				'footer'                 => '#24292e',
				'links'                  => '#334466',
				'gradient_start'         => '#ffffff',
				'gradient_end'           => '#ffffff',
				'blocks_color'           => '#f6f8fa',
				'blocks_alternate_color' => '#f6f8fa',
				'content_target_bg'     => '#e0edff',
				'special_titles_color'   => '#24292e',
				'borders_color'	         => '#d1d6dc',
				'buttons_text_color'     => '#24292e',
				'buttons_bg'             => '#ffffff',
				'buttons_border'         => '#24292e',
				'sticky_topic'             => '#cad7e3',
				'sticky_locked_topic'          => '#a0acb8',
				'unapproved_topic'     => '#db9d7b',
				'locked_topic'          => '#dcdfe3',
				'remove_shadows'         => true,
			)
		);

		// Theme authors can also specify some color palettes if they wish
		if (!empty($settings['color_palettes']))
			$color_palettes = $settings['color_palettes'];
		elseif($settings['theme_id'] != 1)
			return;

		return $color_palettes;
	}

	/**
	 * Insert some default settings
	 *
	 * @return void
	 */
	public static function defaults()
	{
		global $settings;

		// Defaults
		$palettes = self::getColorPalette();
		$default_settings = [];

		// Initialize settings
		if (!empty($palettes['default']))
		{
			foreach ($palettes['default'] as $color_setting => $color)
				if (!isset($settings['cc_' . $color_setting]))
					$default_settings['cc_' . $color_setting] = $color;

			// Add settings
			$settings = array_merge($default_settings, $settings);
		}
	}
}