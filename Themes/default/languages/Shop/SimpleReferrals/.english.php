<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

global $modSettings;

$txt['Shop_integration_simple_referrals'] = 'Simple Referrals';
$txt['Shop_integration_simple_referrals_setting'] = (!empty($modSettings['Shop_credits_suffix']) ? $modSettings['Shop_credits_suffix'] : 'Credits') . ' for each new referral';
$txt['Shop_integration_simple_referrals_setting_desc'] = 'Users will receive credits upon referring a new member to the forum.';