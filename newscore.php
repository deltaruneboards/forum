<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

// Provides support for phpbb games
if (!isset($_POST['game_name']))
	die('Hacking attempt...');

$_POST['action'] = 'arcade';
$_POST['sa'] = 'submit';
$_POST['phpbb'] = true;

require_once(getcwd() . '/index.php');
?>