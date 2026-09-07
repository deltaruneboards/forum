<?php

/**
 * Class-Spoiler.php
 *
 * @package Spoiler
 * @link https://custom.simplemachines.org/index.php?mod=4365
 * @author Bugo https://dragomano.ru/mods/spoiler
 * @copyright 2021-2023 Bugo
 * @license https://opensource.org/licenses/BSD-3-Clause BSD
 *
 * @version 0.3.2
 */

if (!defined('SMF'))
	die('No direct access...');

class Spoiler
{
	/**
	 * Used hooks
	 *
	 * @return void
	 */
	public function hooks()
	{
		if ($this->isItShouldNotWork())
			return;

		add_integration_function('integrate_load_theme', __CLASS__ . '::loadTheme#', false, __FILE__);
		add_integration_function('integrate_pre_css_output', __CLASS__ . '::preCssOutput#', false, __FILE__);
		add_integration_function('integrate_sceditor_options', __CLASS__ . '::sceditorOptions#', false, __FILE__);
		add_integration_function('integrate_bbc_buttons', __CLASS__ . '::bbcButtons#', false, __FILE__);
		add_integration_function('integrate_bbc_codes', __CLASS__ . '::bbcCodes#', false, __FILE__);
	}

	/**
	 * Load necessary assets
	 *
	 * @return void
	 */
	public function loadTheme()
	{
		loadLanguage('Spoiler/');

		// Lazy loading for images within spoiler
		addInlineJavaScript('
		$(".spoiler_content img").each(function() {
			$(this).attr("data-src", $(this).attr("src"));
			$(this).attr("src", smf_default_theme_url + "/images/loading_sm.gif");
		});
		$("body").on("click", ".bbc_spoiler summary", function() {
			content = $(this).parent().children(".spoiler_content");
			content.find("img").each(function() {
				$(this).attr("src", $(this).attr("data-src"));
			});
		});', true);
	}

	public function preCssOutput()
	{
		loadCSSFile('spoiler.css');
	}

	/**
	 * Add spoiler as a plugin for SCEditor
	 *
	 * @param array $sce_options
	 * @return void
	 */
	public function sceditorOptions(&$sce_options)
	{
		$sce_options['plugins'] = ($sce_options['plugins'] !== '' ? $sce_options['plugins'] . ',' : '') . 'spoiler';
	}

	/**
	 * Add spoiler button
	 *
	 * @param array $buttons
	 * @return void
	 */
	public function bbcButtons(&$buttons)
	{
		global $settings, $txt;

		addJavaScriptVar(
			'spoilerCss',
			str_replace(
				['..', "\n", "\t"],
				[$settings['default_theme_url'], '', ''],
				file_get_contents($settings['default_theme_dir'] . '/css/spoiler.css')
			),
			true
		);

		addJavaScriptVar('smf_txt_spoiler', $txt['spoiler'], true);
		addJavaScriptVar('smf_txt_spoiler_title', $txt['spoiler_title'], true);

		loadJavaScriptFile('spoiler.js', array('minimize' => true));

		$buttons[count($buttons) - 1][] = array(
			'code'        => 'spoiler',
			'description' => $txt['spoiler']
		);
	}

	/**
	 * Spoiler tag
	 *
	 * @param array $codes
	 * @return void
	 */
	public function bbcCodes(&$codes)
	{
		global $txt;

		if (empty($txt['spoiler']))
			return;

		$codes = array_merge(
			$codes,
			array(
				array(
					'tag'         => 'spoiler',
					'before'      => '<details class="bbc_spoiler"><summary>' . $txt['spoiler'] . '</summary><div class="spoiler_content">',
					'after'       => '</div></details>',
					'block_level' => true
				),
				array(
					'tag'         => 'spoiler',
					'type'        => 'parsed_equals',
					'quoted'      => 'optional',
					'before'      => '<details class="bbc_spoiler"><summary>$1</summary><div class="spoiler_content">',
					'after'       => '</div></details>',
					'block_level' => true
				)
			)
		);
	}

	/**
	 * @return bool
	 */
	private function isItShouldNotWork()
	{
		global $modSettings;

		return empty($modSettings['enableBBC']) || (!empty($modSettings['disabledBBC']) && in_array('spoiler', explode(',', $modSettings['disabledBBC'])));
	}
}
