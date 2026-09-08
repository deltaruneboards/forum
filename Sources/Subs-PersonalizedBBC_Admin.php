<?php
/*
 * Personalized BBC was developed for SMF forums c/o Chen Zhen @ https://web-develop.ca
 * Copyright 2025 @ web-develop.ca
 * Distributed under the CC BY-ND 4.0 License (http://creativecommons.org/licenses/by-nd/4.0/)
*/

// This file contains the Personalized BBC admin sub functions.
if (!defined('SMF'))
	die('Hacking attempt...');

function createPersonalizedBBC_setting($tableName, $columnName, $value, $name)
{
	global $smcFunc;

	if (empty($tableName) || empty($columnName) || empty($name) || $columnName === 'delete')
		return;
	elseif (empty($value))
		$value = '';

	$request = $smcFunc['db_query']('', "
		UPDATE {db_prefix}{raw:tableName}
		SET {raw:columnName} = {string:value}
		WHERE name = {string:name}
		LIMIT {int:limit}",
		array('name' => $name, 'limit' => 1, 'tableName' => $tableName, 'columnName' => $columnName, 'value' => $value)
	);
}

function PersonalizedBBC_CheckUpload($name, $file)
{
	global $settings, $modSettings, $smcFunc, $scripturl;

	$imageType = 'png';
	$extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

	if ($file["error"] > 0)
		return false;

	$newFile = $smcFunc['strtolower']($file["name"]);
	if (in_array($file["type"], array("image/gif", "image/png", "image/jpg", "image/jpeg", "image/bmp")) && $file["size"] < 30000)
	{
		if (file_exists($settings['default_theme_dir'] . '/images/bbc/personalizedBBC/' . $newFile))
			return false;

		move_uploaded_file($file["tmp_name"], $settings['default_theme_dir'] . '/images/bbc/personalizedBBC/' . $newFile);
		$check = PersonalizedBBC_imageResize($settings['default_theme_dir'] . '/images/bbc/personalizedBBC/' . $newFile, $settings['default_theme_dir'] . '/images/bbc/personalizedBBC/' . $newFile, 20, 20, 1);
		if (!empty($check)) {
			$val = PersonalizedBBC_CheckImage(array($name => trim($newFile)));
			$val = 'personalizedBBC/' . preg_replace('/\.[^.]*$/', '', $val);
			createPersonalizedBBC_setting('personalized_bbc', 'image', $val, $name);
			redirectexit($scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;name=' . $name . ';#persoanlized_bbc_settings');
		}
	}

	redirectexit($scripturl . '?action=admin;area=PersonalizedBBC;sa=personalizedBBC_Entry;name=' . $name . ';#persoanlized_bbc_settings');
}

function PersonalizedBBC_CheckImage($image = array('bbc' => 'bbc'))
{
	global $settings, $modSettings;

	$key = key($image);
	$imageType = '.png';
	$file = PersonalizedBBC_SanitizeFileName($image[$key]);

	if (PersonalizedBBC_FileTypes($file) && @file_exists($settings['theme_dir'] . '/images/bbc/personalizedBBC/' . $file))
		return 'personalizedBBC/' . $file;
	elseif (PersonalizedBBC_FileTypes($file) && @file_exists($settings['default_theme_dir'] . '/images/bbc/personalizedBBC/' . $file))
	{
		if ((is_dir($settings['theme_dir'] . '/images/bbc/personalizedBBC')) && PersonalizedBBC_copyFile($settings['default_theme_dir'] . '/images/bbc/personalizedBBC/' . $file, $settings['theme_dir'] . '/images/bbc/personalizedBBC/' . $file))
			return 'personalizedBBC/' . $file;
		elseif (PersonalizedBBC_copyDirectory($settings['default_theme_dir'] . '/images/bbc/personalizedBBC', $settings['theme_dir'] . '/images/bbc/personalizedBBC'))
			return 'personalizedBBC/' . $file;
	}

	return $key . $imageType;
}

// Check for valid file type (gif/png only)
function PersonalizedBBC_FileTypes($file = '')
{
	global $modSettings;
	$imageType = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'gif' : 'png';

	if (strripos($file, $imageType) !== false)
		return true;

	return false;
}

function PersonalizedBBC_SanitizeFileName($filename)
{
	global $modSettings;

	$imageType = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'gif' : 'png';
	$filename_raw = $filename;
	$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", chr(0));
	$filename = str_replace($special_chars, '', $filename);
	$filename = preg_replace('/[\s-]+/', '-', $filename);
	$filename = trim($filename, '.-_');

	$parts = explode('.', $filename);

	if (count($parts) <= 2)
		return $filename;

	$filename = array_shift($parts);
	$extension = array_pop($parts);
	$mimes = array($imageType);

	foreach ((array)$parts as $part)
	{
		$filename .= '.' . $part;

		if (preg_match("/^[a-zA-Z]{2,5}\d?$/", $part))
		{
			$allowed = false;
			foreach ($mimes as $ext_preg => $mime_match)
			{
				$ext_preg = '!^(' . $ext_preg . ')$!i';
				if (preg_match($ext_preg, $part))
				{
					$allowed = true;
					break;
				}
			}
			if (!$allowed)
				$filename .= '_';
		}
	}

	$filename .= '.' . $extension;

	return $filename;
}

function PersonalizedBBC_copyDirectory($source, $destination)
{
	if (@is_dir($source))
	{
		if (@!is_dir($destination))
			@mkdir($destination);

		$directory = @dir($source);
		while (FALSE !== ($readdirectory = $directory->read()))
		{
			if ($readdirectory == '.' || $readdirectory == '..')
				continue;

			$PathDir = $source . '/' . $readdirectory;
			if (@is_dir($PathDir))
			{
				PersonalizedBBC_copyDirectory($PathDir, $destination . '/' . $readdirectory);
				continue;
			}

			copy($PathDir, $destination . '/' . $readdirectory);
		}

		$directory->close();
	}
	elseif (@file_exists($source) && !@file_exists($destination))
		@copy($source, $destination);
	else
		return false;

	return true;
}

function PersonalizedBBC_copyFile($source, $destination)
{
	if (@file_exists($source) && !@file_exists($destination))
		@copy($source, $destination);
	else
		return false;

	return true;
}

function cleanPersonalizedBBC_String($string = false)
{
	$string = preg_replace(array('/[^a-zA-Z0-9_\+\s]/', '#\s{2,}#', '/\s+/'), array('', '_', '_'), (string)$string);
	return trim($string);
}

function cleanPersonalizedBBC_Code($string = false, $trim = 0)
{
	if ($trim == 3)
	{
		// trim every whitespace
		$string = preg_replace(array("#(\s+)(?![^<|>])(?=[^>]*(<|$))#", "#\s*$^\s*#m", "#(?:(\\n|\\r|\<br( />)){1,})#m"), "", $string);
		$string = preg_replace(array('#(?:(\s|\\n|\\r|&nbsp;|\\x0B)){1,}(?![^<|>]*>)#x', '#\s+(?![^>]*(\<|$))#'), ' ', trim($string));
		$string = preg_replace(array('#(\>\s){1,}#', '#(\s\<){1,}#'), array('>', '<'), $string);
	}
	elseif ($trim == 2)
	{
		// trim whitespace outside html tags
		$string = preg_replace("#(\s+)(?![^<|>])(?=[^>]*(<|$))#", "", $string);
		$string = preg_replace_callback('#(.*?)\<#', function($m){return preg_replace('#\s+#', ' ', $m[0]);}, trim($string), 1);
		$string = preg_replace_callback('#(\>(?:.(?!\>))+$)#', function($m){return preg_replace('#\s+#', ' ', $m[0]);}, trim($string), 1);
	}
	elseif ($trim == 1)
	{
		// trim whitespace inside all html tags (ignore outside)
		$string = preg_replace("#(\s+)(?![^<|>])(?=[^>]*(<|$))#", "", $string);
		$string = preg_replace('#\s+(?![^>]*(\<|$))#', ' ', $string);
		$string = preg_replace_callback('#\>(.*?)\<#', function($m){return preg_replace('#(\s|\\x0B){1,}#i', ' ', $m[0]);}, $string);
		$string = preg_replace(array('#(\>\s){1,}#', '#(\s\<){1,}#'), array('>', '<'), $string);
	}

	return $string;
}

function cleanPersonalizedBBC_Url($type, $parse, $rfc, $string = false)
{
	// fix entities for possible url's within the input
	global $smcFunc, $context;

	if (empty($string))
		return '';

	$matches[0] = array();
	$utf8 = !empty($context['utf8']) ? 'UTF-8' : '';
	$type = !empty($type) ? $type : 'encode';
	$parse = !empty($parse) ? $parse : '';
	$rfc = (!empty($rfc)) && $rfc == 'rfc3986x' ? 'rfc3986x' : 'rfc3986';

	preg_match_all('~https?://\S+~', $string, $matches);
	foreach ($matches[0] as $key => $match)
	{
		$fixedUrl = $type === 'encode' ? $smcFunc['htmltrim'](PersonalizedBBC_uri($match, $rfc), ENT_NOQUOTES, $utf8) : $smcFunc['htmltrim'](PersonalizedBBC_rev_uri($match, $rfc), ENT_NOQUOTES, $utf8);
		if ($match !== $fixedUrl)
		{
			$fixedUrl = preg_replace(array('~%7Bcontent%7D~', '~%7Boption%7D~', '~%7Boption1%7D~', '~%7Boption2%7D~'), array('{content}', '{option}', '{option1}', '{option2}'), $fixedUrl);

			if ($parse !== 'prior')
			{
				if (substr($match, -2, 1) === '"' && substr($fixedUrl, -3) === '%2C')
					$fixedUrl = rtrim($fixedUrl, '%2C') . '"' . substr($match, -1);
				elseif (substr($fixedUrl, -3) === '%2C')
					$fixedUrl = rtrim($fixedUrl, '%2C') . '"';
			}

			$string = str_replace($match, $fixedUrl, $string);
		}
	}

	return $string;
}

function PersonalizedBBC_rev_uri($url, $rfc)
{
	global $smcFunc;
	if ($rfc === 'rfc3986')
	{
		$parts = explode('/', $url);
		$oldParts = explode('?', $parts[count($parts)-1], 2);
		$oldPart = count($oldParts) > 1 ? rawurldecode($oldParts[0]) . '?' . urldecode($oldParts[1]) : urldecode($oldParts[0]);

		$newParts = array_pop($parts);
		$parts[count($parts)] = $oldPart;
		$newUrl = !empty($parts) ? implode('/', $parts) : $url;

		return $newUrl;
	}
	elseif ($rfc === 'rfc3986x')
	{
		$uparts = parse_url($url);
		$scheme = array_key_exists('scheme', $uparts) ? $uparts['scheme'] : '';
		$pass = array_key_exists('pass', $uparts) ? $uparts['pass']  : '';
		$user = array_key_exists('user', $uparts) ? $uparts['user']  : '';
		$port = array_key_exists('port', $uparts) ? $uparts['port']  : '';
		$host = array_key_exists('host', $uparts) ? $uparts['host']  : '';
		$path = array_key_exists('path', $uparts) ? $uparts['path']  : '';
		$query = array_key_exists('query', $uparts) ? $uparts['query']  : '';
		$fragment = array_key_exists('fragment', $uparts) ? $uparts['fragment']  : '';

		if (!empty($scheme))
			$scheme .= '://';

		if (!empty($pass) && !empty($user))
		{
			$user = rawurldecode($user) . ':';
			$pass = rawurldecode($pass) . '@';
		}
		elseif (!empty($user))
			$user .= '@';

		if (!empty($port) && !empty($host))
			$host .= ':';

		if (!empty($path))
		{
			$arr = preg_split("/([\/;=])/", $path, -1, PREG_SPLIT_DELIM_CAPTURE);
			$path = "";
			foreach($arr as $var)
			{
				switch($var)
				{
					case "/":
					case ";":
					case "=":
						$path .= $var;
						break;
					default:
						$path .= rawurldecode($var);
				}
			}

			// legacy patch
			$path = str_replace("/%7E","/~", $path);
		}

		if (!empty($query))
		{
			$arr = preg_split("/([&=])/", $query, -1, PREG_SPLIT_DELIM_CAPTURE);
			$query = "?";
			foreach($arr as $var)
			{
				if ("&" == $var || "=" == $var)
					$query .= $var;
				else
					$query .= urldecode($var);
			}
		}

		if(!empty($fragment))
			$fragment = urldecode(ltrim($fragment, '#'));

		return implode('', array($scheme, $user, $pass, $host, $port, $path, $query, $fragment));
	}
	else
		return $url;

}

function PersonalizedBBC_uri($url, $rfc)
{
	if ($rfc === 'rfc3986')
	{
		$parts = explode('/', $url);
		$oldParts = explode('?', $parts[count($parts)-1], 2);
		$oldPart = count($oldParts) > 1 ? rawurlencode($oldParts[0]) . '?' . urlencode($oldParts[1]) : urlencode($oldParts[0]);

		$newParts = array_pop($parts);
		$parts[count($parts)] = $oldPart;
		$newUrl = !empty($parts) ? implode('/', $parts) : $url;

		return $newUrl;
	}
	elseif ($rfc === 'rfc3986x')
	{
		$uparts = parse_url($url);
		$scheme = array_key_exists('scheme', $uparts) ? $uparts['scheme'] : '';
		$pass = array_key_exists('pass', $uparts) ? $uparts['pass']  : '';
		$user = array_key_exists('user', $uparts) ? $uparts['user']  : '';
		$port = array_key_exists('port', $uparts) ? $uparts['port']  : '';
		$host = array_key_exists('host', $uparts) ? $uparts['host']  : '';
		$path = array_key_exists('path', $uparts) ? $uparts['path']  : '';
		$query = array_key_exists('query', $uparts) ? $uparts['query']  : '';
		$fragment = array_key_exists('fragment', $uparts) ? $uparts['fragment']  : '';

		if (!empty($scheme))
			$scheme .= '://';

		if (!empty($pass) && !empty($user))
		{
			$user = rawurlencode($user) . ':';
			$pass = rawurlencode($pass) . '@';
		}
		elseif (!empty($user))
			$user .= '@';

		if (!empty($port) && !empty($host))
			$host .= ':';

		if (!empty($path))
		{
			$arr = preg_split("/([\/;=])/", $path, -1, PREG_SPLIT_DELIM_CAPTURE);
			$path = "";
			foreach($arr as $var)
			{
				switch($var)
				{
					case "/":
					case ";":
					case "=":
						$path .= $var;
						break;
					default:
						$path .= rawurlencode($var);
				}
			}

			// legacy patch
			$path = str_replace("/%7E","/~", $path);
		}

		if (!empty($query))
		{
			$arr = preg_split("/([&=])/", $query, -1, PREG_SPLIT_DELIM_CAPTURE);
			$query = "?";
			foreach($arr as $var)
			{
				if ("&" == $var || "=" == $var)
					$query .= $var;
				else
					$query .= urlencode($var);
			}
		}

		if(!empty($fragment))
			$fragment = '#' . urlencode($fragment);

		return implode('', array($scheme, $user, $pass, $host, $port, $path, $query, $fragment));
	}
	else
		return $url;
}

function PersonalizedBBC_load_membergroups()
{
	global $smcFunc, $txt;

	loadLanguage('ManageBoards');

	$groups = array(
		-1 => $txt['parent_guests_only'],
		0 => $txt['parent_members_only'],
	);

	$request = $smcFunc['db_query']('', '
		SELECT group_name, id_group, min_posts
		FROM {db_prefix}membergroups
		WHERE id_group NOT IN ({raw:groups})
		ORDER BY min_posts, group_name',
		array(
			'groups' => '"1", "3"',
		)
	);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$groups[(int)$row['id_group']] = trim($row['group_name']);
	$smcFunc['db_free_result']($request);

	return $groups;
}

function PersonalizedBBC_images()
{
	global $settings, $modSettings;
	$imagesetlist = array();

	if (is_dir($settings['default_theme_dir'] . '/images/bbc/personalizedBBC'))
		$imagesDir = @opendir($settings['default_theme_dir'] . '/images/bbc/personalizedBBC');


	if (!empty($imagesDir))
	{
		while (($file = readdir($imagesDir)) !== false)
		{
			if (preg_match('#\.(?:png)$#', $file) && $file !== 'no_image.png')
				$imagesetlist[] = $file;
		}
		closedir($imagesDir);
	}

	natsort($imagesetlist);
	return array_merge(array('no_image.png'), $imagesetlist);
}

function PersonalizedBBC_pagination($content, $redirect, $count = 10)
{
	/* PHP pagination - max 7 visible integers and 6 periods (all links) - current page encircled with square brackets
	 * This php pagination code was developed by Underdog copyright 2013, 2014
	 * http://web-develop.ca
	 * Licensed under the GNU Public License: http://www.gnu.org/licenses/gpl.html
	*/

	// This particular function is only used when opting an entire table else it is not necessary
	global $context, $scripturl;

	/*  Set the $context variables for the display template  */
	$context['current_count'] = count($content);
	$context['current_pages'] = ((int)($context['current_count'] / $count)) + 1;
	$redirect = !empty($redirect) ? $redirect : $scripturl;
	$maxPages = 1;

	if (count($content) <= $count || $count < 2)
	{
		$context['current_pages'] = 0;
		return $content;
	}

	// Did they enter an invalid page via the url?
	if ((int)($context['current_count']/$count) == $context['current_count']/$count)
		$maxPages = $context['current_count']/$count;
	elseif($context['current_count'] %$count != 0)
		$maxPages += ($context['current_count'] - ($context['current_count'] % $count)) / $count;

	if ((int)$context['current_page']+1 > $maxPages)
		redirectexit($redirect . 'current_page=' . $maxPages . ';#page_top');
	elseif ((int)$context['current_page'] < 0)
		redirectexit($redirect . 'current_page=1;#page_top');

	// Now calculate the part of the array to display
	if (($context['current_count'] / $count) == (int)($context['current_count'] / $count))
		$context['current_pages'] = ($context['current_count'] / $count);

	$context['current_showResults'] = array(((int)$context['current_page'] * $count), (((int)$context['current_page'] + 1) * $count) - 1);

	if ((int)$context['current_page']+1 == (int)$context['current_pages'])
	    $context['current_showResults'][1] = count($content);
	else
	    $context['current_showResults'][1] = (int)$context['current_page']*$count + ($count-1);

	foreach($content as $key => $var)
	{
		$counter = array_search($key , array_keys($content));
		if ($counter >= (int)$context['current_showResults'][0] && $counter <= (int)$context['current_showResults'][1])
			$new_content[$key] = $var;
	}

	if (!empty($new_content))
		$context['current_showResults'][1] = ((int)$context['current_page']*$count) + count($new_content);
	else
		$new_content = $content;

	return $new_content;
}

function PersonalizedBBC_pages($lang, $anchor, $link, $pages, $sort=false, $order=false)
{
	/* PHP pagination - max 7 visible integers and 6 periods (all links) - current page encircled with square brackets
	 * This php pagination code was developed by Underdog copyright 2013, 2014
	 * http://web-develop.ca
	 * Licensed under the GNU Public License: http://www.gnu.org/licenses/gpl.html
	*/
	global $context;

	$pageCount = 1;
	$display = array('page' => false, 'pages' => '0');
	$page = !empty($context['current_page']) ? (int)$context['current_page'] : 0;
	$display['pages'] = !empty($pages) ? (int)$pages : 1;

	if ($display['pages'] > 1)
	{
	    $display['page'] =  '
    <script type="text/javascript"><!-- // --><![CDATA[
	function changeColor(s)
	{
		document.getElementById("link"+s).style.color = "red";
	}
	function changeColorBack(s)
	{
		document.getElementById("link"+s).style.color = "blue";
	}
    // ]]></script>
    <span style="text-align:center;position:relative;width:99%;display:inline-block;">
	' . $lang . '<br />';

	    while ($pageCount < (int)$display['pages']+1)
	    {
		$current_page = (int)$page+1;
		$total = (int)$display['pages'];

		if ($pageCount == 1 || $pageCount == $total || $pageCount == $current_page || $pageCount == $current_page+1 ||
		    $pageCount == $current_page+2 || $pageCount == $current_page-1 || $pageCount == $current_page-2)
		{
		    if ((int)$pageCount == (int)$page+1)
			$display['page'] .= '
	<a onclick="this.href=\'javascript: void(0)\';" onmouseout="changeColor('. $pageCount . ')" onmouseover="changeColorBack(' . $pageCount . ')" id="link' . $pageCount . '" style="color:red;text-decoration:none;" href="'. $link . ';current_page=' . $pageCount . ';' . $sort . $order . $anchor . '">[' . $pageCount . ']</a> ';
		    else
			$display['page'] .= '
	<a onmouseout="changeColorBack(' . $pageCount . ')" onmouseover="changeColor(' . $pageCount . ')" id="link' . $pageCount . '" style="color:blue;text-decoration:none;" href="' . $link . ';current_page=' . $pageCount . ';' . $sort . $order . $anchor . '">' . $pageCount . '</a> ';
		}
		elseif ($pageCount < $current_page-2 && $pageCount > $current_page-6)
			$display['page'] .= '
	<a onmouseout="changeColorBack(' . $pageCount . ')" onmouseover="changeColor(' . $pageCount . ')" id="link' . $pageCount . '" style="color:blue;text-decoration:none;" href="' . $link . ';current_page=' . $pageCount . ';' . $sort . $order . $anchor . '">.</a> ';
		elseif ($pageCount > $current_page+2 && $pageCount < $current_page+6)
			$display['page'] .= '
	<a onmouseout="changeColorBack(' . $pageCount . ')" onmouseover="changeColor(' . $pageCount . ')" id="link' . $pageCount . '" style="color:blue;text-decoration:none;" href="' . $link . ';current_page=' . $pageCount . ';' . $sort . $order . $anchor . '">.</a> ';

		$pageCount++;
	    }

	    $display['page'] .= '
    </span>';
	}

	return $display;
}

function PersonalizedBBC_checkDefaults()
{
	global $modSettings, $smcFunc, $personalized_BBC;

	$val = array('kissy', 'chrissy');
	$personalized_BBC = !empty($personalized_BBC) ? $personalized_BBC : array();
	$personalizedBBC = array();
	$enabledBBC = parse_bbc(false);
	$disabledBBC = !empty($modSettings['disabledBBC']) ? explode(',', $modSettings['disabledBBC']) : array();

	foreach ($personalized_BBC as $pbbc)
		$personalizedBBC[] = $pbbc['name'];

	foreach ($enabledBBC as $bbc => $x)
		$val[] = $enabledBBC[$bbc]['tag'];

	$val = array_merge($val, $disabledBBC);
	$val = array_diff($val, $personalizedBBC);
	return $val;
}

function PersonalizedBBC_imageResize($src, $dst, $width, $height, $crop=0)
{
	if(!list($w, $h) = getimagesize($src)) {
		return false;
	}

	$type = strtolower(pathinfo($src, PATHINFO_EXTENSION));
	$type = $type == 'jpeg' ? 'jpg' : $type;

	switch($type) {
		case 'bmp':
			$img = imagecreatefromwbmp($src);
			break;
		case 'gif':
			$img = imagecreatefromgif($src);
			break;
		case 'jpg':
		$img = imagecreatefromjpeg($src);
		break;
		case 'png':
		$img = imagecreatefrompng($src);
		break;
		default:
			return false;
	}

	if($crop) {
		if($w < $width or $h < $height) {
			return false;
		}
		$ratio = max($width/$w, $height/$h);
		$h = $height / $ratio;
		$x = ($w - $width / $ratio) / 2;
		$w = $width / $ratio;
	}
	else {
		if($w < $width and $h < $height) {
			return false;
		}
		$ratio = min($width/$w, $height/$h);
		$width = $w * $ratio;
		$height = $h * $ratio;
		$x = 0;
	}

	$new = imagecreatetruecolor($width, $height);

	if(in_array($type, array('gif', 'png'))) {
		imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
		imagealphablending($new, false);
		imagesavealpha($new, true);
	}

	imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
	imagepng($new, $dst);
	clearstatcache();

	if (file_exists($new) && file_exists($dst)) {
		@unlink($dst);
		return true;
	}

	return false;
}
?>
