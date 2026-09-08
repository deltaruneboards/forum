<?php
/**
 * Cloudflare Turnstile for SMF by MobileCS
 * based on hCaptcha by vbgamer45
 * @license https://choosealicense.com/licenses/bsd-3-clause/ BSD-3-Clause
**/

// Turnstile for SMF
$txt['turnstile_configure'] = 'Cloudflare Turnstile Verification System';
$txt['turnstile_configure_desc'] = '<span class="smalltext">Don\'t have keys for Turnstile? <a href="https://www.cloudflare.com/application-services/products/turnstile/" target="_blank"><span style="text-decoration: underline">Get them here</span></a>.';
$txt['turnstile_enabled'] = 'Use Turnstile Verification System';
$txt['turnstile_enable_desc'] = '(enter your keys below)';

$txt['turnstile_public_key'] = 'Site Key';
$txt['turnstile_public_key_desc'] = 'This will be set in the HTML code your site serves to users.';
$txt['turnstile_private_key'] = 'Secret Key';
$txt['turnstile_private_key_desc'] = 'This is for communication between your site and Cloudflare. Keep it secret.';

$txt['turnstile_theme'] = 'Turnstile Theme';
$txt['turnstile_theme_desc'] = 'Choose which theme you would like to use.';
$txt['turnstile_theme_light'] = 'Light';
$txt['turnstile_theme_dark'] = 'Dark';

$txt['turnstile_widget_size'] = 'Widget Size';
$txt['turnstile_widget_size_desc'] = 'Choose which widget size you would like to use.';
$txt['turnstile_widget_size_normal'] = 'Normal';
$txt['turnstile_widget_size_compact'] = 'Compact';

$txt['error_wrong_turnstile_code'] = 'Verification failed. Please verify you are human.';
$txt['error_need_turnstile_code'] = 'Something went wrong. Please try again.';