<?php
/**
 * @package DiceRoller
 * @author Arantor
 * @license MIT
 */

/**
 * Does dice magic.
 */
class DiceRoller
{
	public static function integrate_bbc_buttons(&$bbc_tags, &$editor_tag_map)
	{
		global $txt, $settings;
		loadLanguage('DiceRoller/DiceRoller');

		$new_tags = [];

		foreach ($bbc_tags as $row_id => $row)
		{
			$remake_row = [];
			foreach ($row as $item_id => $item)
			{
				$remake_row[] = $item;
				if (!empty($item) && !empty($item['code']) && $item['code'] == 'floatright')
				{
					$remake_row[] = [];
					$remake_row[] = [
						'code' => 'roll',
						'description' => $txt['bbc_code_roll'],
						'before' => '[roll]',
						'after' => '[/roll]',
					];
				}
			}
			$new_tags[] = $remake_row;
		}
		$bbc_tags = $new_tags;

		addInlineCss('.sceditor-button.sceditor-button-roll div { background:url("' . $settings['default_theme_url'] . '/diceimages/dice.png"); }');
	}

	public static function integrate_bbc_codes(&$codes, &$no_autolink_tags)
	{
		$codes[] = [
			'tag' => 'dicerolled',
			'before' => '<div class="dicerolled">',
			'after' => '</div>',
		];
	}

	public static function integrate_preparsecode(&$message, $previewing)
	{
		global $txt, $settings;
		loadLanguage('DiceRoller/DiceRoller');

		$image = $settings['default_theme_url'] . '/images/icons/warn.png';

		// Remove anyone who posted with the [dicerolled] tag, bah, ye cheating riffraff!
		$message = preg_replace('~\[/dicerolled\]~i', '', $message);
		$message = preg_replace('~\[dicerolled[^\]]*\]~i', "[img]{$image}[/img] [color=red][b]{$txt['dice_cheater']}[/b][/color]\n", $message);

		// roll them dices!
		$message = preg_replace_callback('~\[roll\]([^\[]*)\[/roll\]~i', 'DiceRoller::dice_roll', $message);
	}

	public static function dice_roll($string) {
		global $txt;

		loadLanguage('DiceRoller/DiceRoller');

		$output = '[dicerolled]';
		$count = preg_match_all('~(\{([^}]+)\})?\s*([0-9]+)d([0-9]+)([\+\-][0-9]+)?(\^[0-9]+)?(v[0-9]+)?(\&lt;[0-9]+)?(\&gt;[0-9]+)?(x[0-9]+)?~i', $string[1], $thisdiceroll);
		if ($count)
		{
			for ($i = 0; $i < $count; $i++)
			{
				$iterationtotal = (!empty($thisdiceroll[10][$i])) ? (int) substr($thisdiceroll[10][$i], 1) : 1;
				if ($iterationtotal > 20) $iterationtotal = 20;
				
				for ($iterations = 0; $iterations < $iterationtotal; $iterations++) {
					$qty_dice = (int) $thisdiceroll[3][$i];
					if ($qty_dice == 0)
						$qty_dice = 1;
					
					if ($qty_dice > 20)
						$qty_dice = 20;
					
					$type_dice = (int) $thisdiceroll[4][$i];
					if ($type_dice < 2)
						$type_dice = 2;
					
					if ($type_dice > 1000)
						$type_dice = 1000;
			
					$output .= (empty($thisdiceroll[2][$i]) ? $txt['dice_rolled'] : $thisdiceroll[2][$i] ) . ' ' . $qty_dice . 'd' . $type_dice . $thisdiceroll[5][$i] . $thisdiceroll[6][$i] . $thisdiceroll[7][$i] . $thisdiceroll[8][$i] . $thisdiceroll[9][$i] . ' : ';
					$comma = false;
					
					$total = 0;
					for ($j = 0; $j < $qty_dice; $j++)
					{
						$roll = mt_rand(1, $type_dice);
						
						if ($comma)
							$output .= ', ';
						
						// ^n syntax, requires roll to be above n else reroll figure
						$lower = (!empty($thisdiceroll[6][$i])) ? (int) substr($thisdiceroll[6][$i], 1) : 0;
						
						// vn syntax, requires roll to be below n else reroll figure
						$upper = (!empty($thisdiceroll[7][$i])) ? (int) substr($thisdiceroll[7][$i], 1) : 1 + $type_dice;

						// switch if the wrong way around
						if ($upper < $lower)
						{
							$t = $lower;
							$lower = $upper;
							$upper = $t;
						}
						
						// sanity checks
						if ($lower >= $type_dice)
						{
							$output .= sprintf($txt['dice_invalid_lower'], $lower, $thisdiceroll[4][$i]);
							break;
						}

						if ($upper - $lower < 2)
						{
							$output .= sprintf($txt['dice_invalid_both'], $lower, $upper);
							break;
						}
						
						// reroll if necessary
						while(($roll <= $lower) || ($roll >= $upper))
						{
							$output .= '[s]' . $roll . '[/s] ';
							$roll = mt_rand(1, $thisdiceroll[4][$i]);
						}

						$valid = true;
						if (!empty($thisdiceroll[8][$i]))
						{
							// now let's see if there are any we should drop from < and > syntax
							$lower = (int) substr($thisdiceroll[8][$i], 4);			
							if ($roll >= $lower)
							{
								$output .= '[s]' . $roll . '[/s]';
								$valid = false;
							}
						}
						
						if ($valid && !empty($thisdiceroll[9][$i]))
						{
							$upper = (int) substr($thisdiceroll[9][$i], 4);
							if ($roll <= $upper)
							{
								$output .= '[s]' . $roll . '[/s]';
								$valid = false;
							}
						}
						
						if ($valid)
						{
							$output .= $roll;
							$total += $roll;
						}
						$comma = true;
					}
					
					$mod = (int) $thisdiceroll[5][$i];
					if ($mod != 0)
					{
						$output .= ($mod > 0) ? ' + ' . $mod : ' - ' . abs($mod);
						$total += $mod;
					}
					
					$output .= ', ' . $txt['dice_total'] . ' ' . $total;
					
					if ($i < ($count - 1))
						$output .= '<br />';

					if ($iterations < $iterationtotal - 1)
						$output .= '<br />';
				}
			}

		} else {
			$output .= '\'' . $string[1] . '\' ' . $txt['dice_not_valid'];
		}
		$output .= '[/dicerolled]';
		return $output;
	}
}
