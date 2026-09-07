<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function arcadeFixTagsHTML($text, $literal = false) {
	if (!empty($literal)) {
		return htmlspecialchars($text);
	}
	list($elementstack, $stacksize, $elementqueue, $newtext) = [[], 0, '', ''];
	$single_elements = array('area', 'base', 'basefont', 'br', 'col', 'command', 'embed', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param', 'source', 'track', 'wbr');
	$nestable_elements = array('article', 'aside', 'blockquote', 'details', 'div', 'figure', 'object', 'q', 'section', 'span');
	$text = str_replace('< !--', '<    !--', $text);
	$text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);
	$element_pattern = ('#<(/?)((?:[a-z](?:[a-z0-9._]*)-(?:[a-z0-9._-]+)+)|(?:[\w:]+))(?:\s*(/?)|(\s+)([^>]*))>#');

	while (preg_match($element_pattern, $text, $regex)) {
		$full_match = $regex[0];
		$has_leading_slash = !empty($regex[1]);
		$element_name = $regex[2];
		$element = strtolower($element_name);
		$is_single_element = in_array($element, $single_elements, true);
		$pre_attribute_ws = isset($regex[4]) ? $regex[4] : '';
		$attributes = trim(isset($regex[5] ) ? $regex[5] : $regex[3]);
		$has_self_closer = '/' === substr($attributes, -1);
		$newtext .= $elementqueue;
		$i = strpos($text, $full_match);
		$l = strlen($full_match);
		$elementqueue = '';
		if ($has_leading_slash) {
			if ($stacksize <= 0) {
				$element = '';
			}
			elseif ($elementstack[$stacksize - 1] === $element) {
				$element = '</' . $element . '>';
				array_pop($elementstack);
				$stacksize--;
			}
			else {
				for ($j = $stacksize - 1; $j >= 0; $j--) {
					if ($elementstack[$j] === $element) {
						for ($k = $stacksize - 1; $k >= $j; $k--) {
							$elementqueue .= '</' . array_pop($elementstack) . '>';
							$stacksize--;
						}
						break;
					}
				}
				$element = '';
			}
		}
		else {
			if ($has_self_closer) {
				if (!$is_single_element) {
					$attributes = trim(substr($attributes, 0, -1)) . '></' . $element;
				}
			}
			elseif ($is_single_element) {
				$pre_attribute_ws = ' ';
				$attributes .= '/';
			}
			else {
				if ($stacksize > 0 && !in_array($element, $nestable_elements, true) && $elementstack[$stacksize - 1] === $element) {
					$elementqueue = '</' . array_pop($elementstack) . '>';
					$stacksize--;
				}
				$stacksize = array_push($elementstack, $element);
			}

			if ($has_self_closer && $is_single_element) {
				$pre_attribute_ws = ' ';
			}

			$element = '<' . $element . $pre_attribute_ws . $attributes . '>';
			if (!empty($elementqueue)) {
				$elementqueue .= $element;
				$element = '';
			}
		}
		$newtext .= substr($text, 0, $i) . $element;
		$text = substr($text, $i + $l);
	}

	$newtext .= $elementqueue;
	$newtext .= $text;

	while ($x = array_pop($elementstack)) {
		$newtext .= '</' . $x . '>';
	}
	$newtext = str_replace('< !--', '<!--', $newtext);
	$newtext = str_replace('<    !--', '< !--', $newtext);

	return $newtext;
}

?>