<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

namespace Shop\Modules;

use Shop\Shop;
use Shop\Helper\Database;
use Shop\Helper\Module;

if (!defined('SMF'))
	die('Hacking attempt...');

class StickyTopic extends Module
{
	/**
	 * @var int The topic selected by the user.
	 */
	private $_topic;

	/**
	 * @var array The array of topics the user has.
	 */
	private $_topics;

	/**
	 * @var string A string with the options (topics) to choose from
	 */
	private $_select = '';

	/**
	 * StickyTopic::getItemDetails()
	 *
	 * Set the details and basics of the module, along with default values if needed.
	 */
	function getItemDetails()
	{
		// Item details
		$this->authorName = 'Diego Andrés';
		$this->authorWeb = 'https://smftricks.com/';
		$this->authorEmail ='admin@smftricks.com';
		$this->name = Shop::getText('st_name');
		$this->desc = Shop::getText('st_desc');
		$this->price = 400;
		$this->require_input = true;
		$this->can_use_item = true;
	}

	function getUseInput()
	{
		global $user_info;

		// Get this user topics
		$this->_topics = Database::Get(0, 10000, 'm.subject DESC', 'topics AS t', ['t.id_member_started', 't.id_topic', 't.is_sticky', 't.id_first_msg', 'm.id_msg', 'm.subject'], 'WHERE t.id_member_started = {int:mem} AND t.is_sticky = 0', false, 'LEFT JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)', ['mem' => $user_info['id']]);

		// What if the user has 0 topics?
		if (empty($this->_topics))
			return '
			<div class="errorbox">
				' . Shop::getText('st_notopics') . '
			</div>';

		// Show a list with the topics
		else
		{
			// Loop through the topics
			foreach ($this->_topics AS $topic)
				$this->_select .= '<option value="' . $topic['id_topic'] . '">' . $topic['subject'] . '</option>';

			// Select the topic
			return '
				<dl class="settings">
					<dt>
						' . Shop::getText('st_choose_topic') . '
					<dt>
					<dd>
						<select name="stickyTopic">
							' . $this->_select . '
						</select>
					</dd>
				</dl>';
		}
	}

	function onUse()
	{
		global $user_info;

		// No topic?
		if (!isset($_REQUEST['stickyTopic']) || empty($_REQUEST['stickyTopic'])) 
			fatal_error(Shop::getText('st_error'));

		checkSession();

		// Topic ID
		$this->_topic = (int) $_REQUEST['stickyTopic'];
		
		// Valid topic?
		$this->_topics = Database::Get('', '', '', 'topics AS t', ['t.id_member_started', 't.id_topic', 't.is_sticky', 't.id_first_msg', 'm.id_msg', 'm.subject'], 'WHERE t.id_member_started = {int:mem} AND t.is_sticky = 0 AND t.id_topic = {int:id_topic}', true, 'LEFT JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)', ['mem' => $user_info['id'], 'topic' => $this->_topic]);

		// That topic wasn't on the list...
		if (empty($this->_topics))
			fatal_error(Shop::getText('st_topic_notexists'), false);

		// That topic is not yours although maybe it was...
		elseif ($this->_topics['id_member_started'] != $user_info['id'])
			fatal_error(Shop::getText('st_topic_notown'), false);

		// Make it sticky then
		Database::Update('topics', ['id_topic' => $this->_topic], 'is_sticky = 1,', 'WHERE id_topic = {int:id_topic}');

		// Sucess
		return '
			<div class="infobox">
				' . sprintf(Shop::getText('st_success'), $this->_topic, $this->_topics['subject']) .'
			</div>';
	}
}