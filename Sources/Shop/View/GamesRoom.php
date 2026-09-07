<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\View;

use Shop\Shop;
use Shop\Helper\Format;
use Shop\Helper\Log;

if (!defined('SMF'))
	die('No direct access...');

class GamesRoom
{
	/**
	 * @var object Log the user history playing at the games room.
	 */
	protected $_log;

	/**
	 * @var array List of games.
	 */
	protected $_list = [];

	/**
	 * @var string The URL for the games images
	 */
	protected $_images_dir;

	/**
	 * @var array Array of games.
	 */
	protected $_games = [];

	/**
	 * @var string The current game.
	 */
	protected $_play;

	/**
	 * GamesRoom::__construct()
	 *
	 * Set the tabs for the section and create instance of needed objects
	 */
	function __construct()
	{
		global $modSettings, $user_info, $boardurl;

		// Load language files
		loadLanguage('Shop/Games');

		// Set the images url
		$this->_images_dir = $boardurl . Shop::$gamesdir;

		// Build the tabs for this section
		$this->games();

		// Prepare to log the gift
		$this->_log = new Log;

		// Games Room is enabled?
		if (empty($modSettings['Shop_enable_games']))
			fatal_error(Shop::getText('currently_disabled_games'), false);

		// Is the user is allowed to the gamesroom?
		if (!allowedTo('shop_canManage'))
			isAllowedTo('shop_playGames');

		// Let's see if the games pass it's valid
		if (time() >= $user_info['gamesPass'])
			fatal_error(Shop::getText('games_invalid'), false);

		// Load the main details
		$this->init();
	}

	/**
	 * GamesRoom::init()
	 *
	 * Sets the basic details for the games
	 */
	public function init()
	{
	}

	public function main()
	{
		global $context, $user_info, $scripturl;

		// Games Pass, get the days!
		$context['user']['gamedays'] = ($user_info['gamesPass'] <= time() || empty($user_info['gamesPass']) ? 0 : Format::gamespass($user_info['gamesPass']));

		// Set all the page stuff
		$context['page_title'] = Shop::getText('main_button') . ' - ' . Shop::getText('main_games');
		$context['page_description'] = sprintf(Shop::getText('games_welcome_desc'), $context['user']['gamedays']);
		$context['sub_template'] = 'games';
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=games',
			'name' => Shop::getText('main_games'),
		];

		// Shop games
		$context['shop']['games'] = $this->_list;

		// Load the game function
		if (!empty($this->_play))
		{
			// Set all the page stuff
			$context['template_layers'][] = 'games_play';

			// Let's play
			call_helper('Shop\Games\\' . $this->_list[$this->_play]['class'] . '::play#');
		}
	}

	public function games()
	{
		// Shop games
		$this->_list = [
			'slots' => [
				'name' => Shop::getText('games_slots'),
				'icon' => $this->_images_dir . 'slots.png',
				'class' => 'Slots',
			],
			'lucky2' => [
				'name' => Shop::getText('games_lucky2'),
				'icon' => $this->_images_dir . 'lucky2.png',
				'class' => 'Lucky2',
			],
			'number' => [
				'name' => Shop::getText('games_number'),
				'icon' => $this->_images_dir . 'numberslots.png',
				'class' => 'Number',
			],
			'pairs' => [
				'name' => Shop::getText('games_pairs'),
				'icon' => $this->_images_dir . 'pairs.png',
				'class' => 'Pairs',
			],
			'dice' => [
				'name' => Shop::getText('games_dice'),
				'icon' => $this->_images_dir . 'dice.png',
				'class' => 'Dice',
			],
		];
		$this->_play = isset($_REQUEST['play']) ? $_REQUEST['play'] : '';

		// More games?
		call_integration_hook('integrate_shop_games_list', [&$this->_list, &$this->_play, &$this->_images_dir]);
	}
}