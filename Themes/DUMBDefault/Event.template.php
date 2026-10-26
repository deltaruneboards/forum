<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2025 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1.5
 */
 
/*
    copied from Profile
    top box for notfiying the user
*/
function template_event_above()
{
	global $context;
	
	echo "topper";
	
	// If the profile was update successfully, let the user know this.
	if (!empty($context['profile_updated']))
		echo '
			<div class="infobox">
				', $context['profile_updated'], '
			</div>';
}

function template_event_below() {}

function template_candybox() {
    global $context, $txt;
    
    echo '
            <div id="statistics" class="main_section">
                <div class="cat_bar">
                    <h3 class="catbg">', $context["page_title"] . '</h3>
                </div>
                
                <div class="roundframe">
                    ' . $context["page_label"] . '
                    
                    <div class="cat_bar">
                        <h4 class="catbg">' . $txt["dumb_event_halloweenlb"] . '</h4>
                    </div>
                    
                    <dl class="stats">
                    ';
                    
                foreach ($context["candyscores"] as $user => $data) {
                    echo '
                        <dt>' . $user . '</dt>
                        <dd class="statsbar generic_bar righttext">
                            <div class="bar" style="width: ' . $data["perc"] . '%;"></div><span>' . $data["label"] . '</span>
                        </dd>
                    ';
                }
                
                echo ' </div>
            </div>
    ';
}
 
?>
