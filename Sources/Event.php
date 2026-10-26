<?php
// DUMBie extension

# TODO make this more modular for multiple Events
# im kinda rushing here to get this done for halloween 2026

function EventMain() {
    
    $subActions = array(
        'candybox' => 'candyBox'
    );
    
    call_helper($subActions[$_REQUEST['sa']]);
}

function candyBox() {
    global $context, $scripturl, $smcFunc, $user_info, $txt;
    
    loadLanguage("Event");
    
    // attemtping to add a new candy?
    if (isset($_GET["candy"], $user_info["id"])) {
        if (validateToken("trick-treat-candy", "get", false)) {
            // make this somehow get the event string from a modsetting
            // again im rusing through this
            $smcFunc["db_query"]('', '
                INSERT INTO {db_prefix}eventscore
                VALUES ({int:user_id}, \'halloween2026\', 1)
                ON DUPLICATE KEY UPDATE score = score + 1',
                array(
                    "user_id" => $user_info["id"]
                )
            );
            $context["profile_updated"] = $txt["dumb_event_success"];
        }
        else $context["profile_updated"] = $txt["dumb_event_fail"];
    }
    
    if (isset($user_info["id"])) {
        $requestDUMBIE = $smcFunc["db_query"]('', '
            SELECT score FROM {db_prefix}eventscore
            WHERE event = \'halloween2026\'
            AND id_member = {int:user_id}',
            array(
                "user_id" => $user_info["id"]
            )
        );
        $val = $smcFunc["db_fetch_row"]($requestDUMBIE)[0];
        $smcFunc["db_free_result"]($requestDUMBIE);
        $context["page_label"] = sprintf($txt["dumb_event_halloweenself"], $val);
    }
    else {
        $context["page_label"] = $txt["dumb_event_halloweenguest"];
    }
    
    $requestDUMBIE = $smcFunc["db_query"]("", "
        SELECT mbr.real_name, scr.id_member, scr.score
        FROM {db_prefix}eventscore scr
        INNER JOIN {db_prefix}members mbr ON mbr.id_member = scr.id_member
        WHERE event = 'halloween2026'
        ORDER BY score DESC",
        array(
            "user_id" => $user_info["id"]
        ));
    
    $winnertally = -1;
    $candytally = array();
    while ($row = $smcFunc["db_fetch_assoc"]($requestDUMBIE)) {
        $lnk = '<a href="?action=profile;u=' . $row["id_member"] . '">' . $row["real_name"] . '</a>';
        $candytally[$lnk] = array("label" => $row["score"]);
        
        if ($winnertally == -1) {
            $winnertally = $row["score"];
            $candytally[$lnk]["perc"] = 100;
        }
        else {
            $candytally[$lnk]["perc"] = floor(($row["score"] / $winnertally) * 100);
        }
    }
    
    $smcFunc["db_free_result"]($requestDUMBIE);
    
    loadTemplate('Event');
    $context["template_layers"][] = "event";
    $context['sub_template'] = 'candybox';
    $context["page_title"] = $txt["dumb_event_halloweentitle"];
    
    $context['linktree'][] = array(
		'url' => $scripturl . '?action=event&sa=candybox',
		'name' => $txt['dumb_event_halloweentitle']
	);
    
    $context["candyscores"] = array(
        "foo" => array("perc" => 100, "label" => 6),
        "bar" => array("perc" => 50, "label" => 3)
    );
    
    $context["candyscores"] = $candytally;
}

// called near the very very beginning of the construction chain
function global_event_preload() {
    global $context;
    
    // for whatever reason using $context here is finicky
    // so we're going straight to the source (session)
    
        
    if (rand(0, 10) == 0)
        createToken("trick-treat-candy", 'get');
        
    if (isset($_SESSION["token"]["get-trick-treat-candy"]))
       	// we're doing the randomize here so we can also generate a token
       	// so the economy doesnt break again by hammering the script :>
       	loadJavaScriptFile('trickRtreat.js', ['minimize' => false]);
}

// called from the top of template_body_above
function event_precontent() {
    global $scripturl, $context;

    if (!isset($_SESSION["token"]["get-trick-treat-candy"]))
            return;
    
    // we're just going to make the base nodes here
    // idc anymore token verification just doesnt seem to work from js
    // js is still used to reparent and reposition it
    echo '<a href="' . $scripturl . '?action=event&sa=candybox&' .
            $_SESSION["token"]["get-trick-treat-candy"][0] . "=" .
            $_SESSION["token"]["get-trick-treat-candy"][3] .
            '&candy=get" id="trickCandy">
        <img src="/assets/special/candy-' . rand(1, 3) . 'png" style="z-index=5"></a>';
}

#global_event_preload();

?>
