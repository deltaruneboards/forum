<?php

/**
 * Cloudflare Turnstile for SMF by MobileCS
 * based on hCaptcha by vbgamer45
 * @license https://choosealicense.com/licenses/bsd-3-clause/ BSD-3-Clause
**/

if (!defined('SMF'))
    die('Hacking attempt...');


function turnstile_integrate_spam_settings(&$config_vars)
{
    global $sourcedir, $modSettings, $txt;
    $config_vars[] = array('title',  'turnstile_configure');
    $config_vars[] = array('desc',   'turnstile_configure_desc', 'class' => 'windowbg');
    $config_vars[] = array('check',  'turnstile_enabled', 'subtext' => $txt['turnstile_enable_desc']);
    $config_vars[] = array('text',   'turnstile_public_key', 'subtext' => $txt['turnstile_public_key_desc']);
    $config_vars[] = array('text',   'turnstile_private_key', 'subtext' => $txt['turnstile_private_key_desc']);
    $config_vars[] = array('select', 'turnstile_theme', array('light' => $txt['turnstile_theme_light'], 'dark' => $txt['turnstile_theme_dark']), 'subtext' => $txt['turnstile_theme_desc']);
	$config_vars[] = array('select', 'turnstile_widget_size', array('normal' => $txt['turnstile_widget_size_normal'], 'compact' => $txt['turnstile_widget_size_compact']), 'subtext' => $txt['turnstile_widget_size_desc']);
}

function turnstile_integrate_create_control_verification_pre(&$verificationOptions, $do_test)
{
    global $modSettings, $context;
    $verificationOptions['can_turnstile'] = 0;

    if ($modSettings['turnstile_enabled'] == 1 && !empty($modSettings['turnstile_public_key']) && !empty($modSettings['turnstile_private_key']))
    {
        $verificationOptions['can_recaptcha'] = 0;
        $verificationOptions['show_visual'] = 0;
        $verificationOptions['can_turnstile'] = 1;
        $context['controls']['verification'][$verificationOptions['id']]['can_recaptcha'] = 0;
        $context['controls']['verification'][$verificationOptions['id']]['show_visual'] = 0;
        $context['controls']['verification'][$verificationOptions['id']]['can_turnstile'] = 1;
    }
}

function turnstile_integrate_create_control_verification_test($thisVerification, &$verification_errors)
{
    global $modSettings, $sourcedir;

    if ($thisVerification['can_turnstile'] == 1)
    {
		$verification_errors = array_diff($verification_errors, ["wrong_verification_code"]);
		$verification_errors = array_values($verification_errors);

         // Verify the captcha
         if (isset($_REQUEST["cf-turnstile-response"]))
         {
             require_once($sourcedir . '/Subs-Package.php');
             $response = fetch_web_data('https://challenges.cloudflare.com/turnstile/v0/siteverify', 'secret=' . $modSettings['turnstile_private_key'] . '&response=' . $_REQUEST["cf-turnstile-response"] . '&remoteip=' . $_SERVER["REMOTE_ADDR"]);
             $response = json_decode($response, true);

             if (true != $response["success"])
			 {
				$verification_errors[] = 'wrong_turnstile_code';
			 }
         }
         else
		 {
             $verification_errors[] = 'need_turnstile_code';
		 }
     }
}

function turnstile_integrate_post_errors(&$post_errors, &$minor_errors, $form_message, $form_subject)
{
	global $modSettings;

	if ($modSettings['turnstile_enabled'] == 1)
	{
		if (is_array($post_errors) && in_array("wrong_turnstile_code", $post_errors))
		{
			if (in_array('need_qr_verification', $post_errors))
			{
				$post_errors = array_values(array_diff($post_errors, ["need_qr_verification"]));
			}
		}
	}
}
