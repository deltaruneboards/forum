<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */


if (!isset($_REQUEST['sessdo']))
	die('Hacking attempt...');

$_POST['action'] = 'arcade';
if (isset($_REQUEST['gamename']))
	$_POST['game'] = $_REQUEST['gamename'];
if (isset($_REQUEST['lastid']))
	$_POST['lastid'] = $_REQUEST['lastid'];
if (isset($_REQUEST['id']))
	$_POST['id'] = $_REQUEST['id'];
$_POST['v3arcade'] = true;

if ($_REQUEST['sessdo'] == 'sessionstart')
	$_POST['sa'] = 'vbSessionStart';
elseif ($_REQUEST['sessdo'] == 'permrequest')
	$_POST['sa'] = 'vbPermRequest';
elseif ($_REQUEST['sessdo'] == 'burn')
	$_POST['sa'] = 'vbBurn';
else
	die('Hacking attempt...');

require_once(dirname(__FILE__) . '/index.php');
?>