<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

global $modSettings;

$txt['Shop_integration_sycho_best_answer'] = 'SMF Best Answer';
$txt['Shop_integration_sycho_best_answer_setting'] = (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' for best answer';
$txt['Shop_integration_sycho_best_answer_setting_desc'] = 'The user whose post is marked as the best answer will receive credits, and only if they are not marking it themselves.';