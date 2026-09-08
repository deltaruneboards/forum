<?php
/**
 * Cloudflare Turnstile for SMF by MobileCS
 * based on hCaptcha by vbgamer45
 * @license https://choosealicense.com/licenses/bsd-3-clause/ BSD-3-Clause
**/

function load_turnstile()
{
    global $context, $modSettings;

	// load the language file
	loadLanguage('Turnstile');

    if ( !empty($modSettings['turnstile_enabled']) )
    {
		// load the css file
        loadTemplate(false, 'turnstile');

		// add the javascript file in the the <head> section - moved to install21.xml so it's only loaded on pages that need it
        // $context['html_headers'] .= "\n" . '	<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }
}
