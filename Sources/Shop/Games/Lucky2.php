<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Games;

use Shop\Shop;
use Shop\View\GamesRoom;
use Shop\Helper\Format;

if (!defined('SMF'))
	die('No direct access...');

class Lucky2 extends GamesRoom
{
	/**
	 * @var array Array with the payouts.
	 */
	private $_faces = [];

	/**
	 * @var string Name of the game.
	 */
	private $_game = 'lucky2';

	/**
	 * @var string Type of game.
	 */
	private $_type = 'roll';

	/**
	 * @var array Virtual "dice" of the game.
	 */
	private $_dice = [];

	/**
	 * @var int Stopping value.
	 */
	private $_stop;

	/**
	 * @var int Result of the dice.
	 */
	private $_result;

	/**
	 * @var bool If the user wins or not.
	 */
	private $_winner = false;

	/**
	 * Lucky2::init()
	 *
	 * Load the data for this game
	 */
	
	public function init()
	{
		// Set the images url for this game
		$this->_images_dir .= $this->_game . '/';

		// Set up the payouts
		$this->payouts();

		// Game details
		$this->details();
	}

	public function payouts()
	{
		$this->_faces = [
			1 => 0,
			2 => 'price',
			3 => 0,
			4 => 0,
			5 => 0,
			6 => 0,
		];
	}

	public function details()
	{
		global $context, $scripturl;

		// Images folder
		$context['shop_game_images'] = $this->_images_dir;

		// Linktree
		$context['linktree'][] = [
			'url' => $scripturl . '?action=shop;sa=games;play=' . $this->_game,
			'name' => Shop::getText('games_' . $this->_game),
		];

		// Title and description
		$context['game']['title'] = Shop::getText('games_' . $this->_game);
		$context['page_title'] .= ' - ' . $context['game']['title'];
		$context['page_description'] = Shop::getText('games_' . $this->_game . '_desc');

		// Sub template
		$context['sub_template'] = 'games_play';

		// Faces
		$context['game']['faces'] = $this->_faces;

		// User cash
		$context['user']['games']['real_money'] = Format::cash($context['user']['shopMoney']);

		// Spin wheel
		$context['shop_game_spin'] = [true, 1];

		// Type of action
		$context['shop_game_type'] = $this->_type;
	}

	public function play()
	{
		global $context, $modSettings, $user_info;

		if (isset($_REQUEST['do']))
		{
			// Check session
			checkSession();

			// Construct dice
			foreach ($this->_faces as $face => $pay)
				$this->_dice[] = $face;

			// The value
			$this->_stop = mt_rand(count($this->_dice), 10*count($this->_dice)) % count($this->_dice);

			// The result!
			$this->_result = $this->_dice[$this->_stop];

			// Use these results
			$context['shop_game']['wheel'][1] = $this->_result;

			// By default user's a loser
			$context['game_result'] = [$this->_winner, sprintf(Shop::getText('games_loser'), Format::cash($modSettings['Shop_settings_' . $this->_game . '_losing']))];

			// You are very lucky
			if ($this->_result == 2)
			{
				// Winner
				$this->_winner = true;

				// The user is a winner
				$context['game_result'] = [$this->_winner, sprintf(Shop::getText('games_winner'), Format::cash($modSettings['Shop_settings_' . $this->_game . '_' . $this->_faces[$this->_result]]))];
			}

			// Update user cash
			$this->_log->game($user_info['id'], (!empty($this->_winner) ? $modSettings['Shop_settings_' . $this->_game . '_' . $this->_faces[$this->_result]] : ((-1) * $modSettings['Shop_settings_' . $this->_game . '_losing'])), $this->_game);

			// User real money
			$context['user']['games']['real_money'] = Format::cash(empty($this->_winner) ? ($user_info['shopMoney'] - $modSettings['Shop_settings_' . $this->_game . '_losing']) : ($user_info['shopMoney'] + $modSettings['Shop_settings_' . $this->_game . '_' . $this->_faces[$this->_result]]));
		}
	}
}