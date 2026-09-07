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

class Number extends GamesRoom
{
	/**
	 * @var array Array with the payouts.
	 */
	private $_faces = [];

	/**
	 * @var string Name of the game.
	 */
	private $_game = 'number';

	/**
	 * @var array Virtual "wheel" of the game.
	 */
	private $_wheel = [];

	/**
	 * @var int Payout.
	 */
	private $_payout;

	/**
	 * @var bool If the user wins or not.
	 */
	private $_winner = true;

	/**
	 * Number::init()
	 *
	 * Load the data for this game
	 */
	function init()
	{
		// Set the images url for this game
		$this->_images_dir .= $this->_game . '/';

		// Set up the payouts
		$this->payouts();

		// Game details
		$this->details();

		// Initialize our wheel with 3 spins
		$this->_wheel = [
			1 => [],
			2 => [],
			3 => [],
		];
	}

	public function payouts()
	{
		$this->_faces = [
			1 => 'complete',
			2 => 'firsttwo', 
			3 => 'secondtwo', 
			4 => 'firstlast',
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
		$context['shop_game_spin'] = [true, 0];

		// Numbers!
		$context['shop_game_number'] = true;
	}

	public function play()
	{
		global $context, $modSettings, $user_info;

		if (isset($_REQUEST['do']))
		{
			// Check session
			checkSession();

			// The results!!! Let's see if we are lucky
			$this->_wheel[1] = mt_rand(0,9);
			$this->_wheel[2] = mt_rand(0,9);
			$this->_wheel[3] = mt_rand(0,9);

			// Use these results
			$context['shop_game']['wheel'] = $this->_wheel;

			// Complete
			if ($this->_wheel[1] == $this->_wheel[2] && $this->_wheel[2] == $this->_wheel[3])
				$this->_payout = 1;
			// First two
			elseif ($this->_wheel[1] == $this->_wheel[2])
				$this->_payout = 2;
			// Last two
			elseif ($this->_wheel[2] == $this->_wheel[3])
				$this->_payout = 3;
			// First and last
			elseif ($this->_wheel[1] == $this->_wheel[3])
				$this->_payout = 4;
			// Loser
			else
				$this->_winner = false;

			// Loser message
			$context['game_result'] = [$this->_winner, (empty($this->_winner) ? sprintf(Shop::getText('games_loser'), Format::cash($modSettings['Shop_settings_' . $this->_game . '_losing'])) : sprintf(Shop::getText('games_winner'), Format::cash($modSettings['Shop_settings_' . $this->_game . '_' . $this->_faces[$this->_payout]])))];

			// Update user cash
			$this->_log->game($user_info['id'], (!empty($this->_winner) ? $modSettings['Shop_settings_' . $this->_game . '_' . $this->_faces[$this->_payout]] : ((-1) * $modSettings['Shop_settings_' . $this->_game . '_losing'])), $this->_game);

			// User real money
			$context['user']['games']['real_money'] = Format::cash(empty($this->_winner) ? ($user_info['shopMoney'] - $modSettings['Shop_settings_' . $this->_game . '_losing']) : ($user_info['shopMoney'] + $modSettings['Shop_settings_' . $this->_game . '_' . $this->_faces[$this->_payout]]));
		}
	}
}