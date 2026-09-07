<?php
/**
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

/*  Above reports list  */
function template_arcade_guide_above()
{
	global $txt, $context, $settings;
	$context['arcade']['guide'] = array();

	echo '
	<div class="centertext" style="padding-bottom: 3em;">
		<h3 class="catbg smalltext">
			<span>', $txt['arcade_admin_guide'], '</span>
		</h3>
	</div>';

}

/*  Reports List  */
function template_arcade_guide()
{
	global $scripturl, $txt, $context, $settings, $sourcedir, $arcadeModSettings;

	echo $context['arcadeHookGuide'];
}

/* Forum copyright */
function template_arcade_guide_below()
{
	/* Add more logo's and breaks as required */
	global $txt;
	//echo '<div style="text-align:center">', $txt['pdl_arcade_copyright'], '<br /></div>';
}
?>