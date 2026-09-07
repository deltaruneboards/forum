<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

global $modSettings;

$txt['Shop_integration_arcade'] = 'SMF Arcade';
$txt['Shop_integration_arcade_score'] = (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' for submitting score';
$txt['Shop_integration_arcade_score_desc'] = 'The user will receive points after they submit a score/play a game.';
$txt['Shop_integration_arcade_personal_best'] = (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' for beating their personal best';
$txt['Shop_integration_arcade_personal_best_desc'] = 'The user will receive points for beating their personal record in a game, or playing it for the first time.';
$txt['Shop_integration_arcade_new_champion'] = (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' for becoming the new champions in a game';
$txt['Shop_integration_arcade_new_champion_desc'] = 'The user will receive points for becoming the new champion in a game.';