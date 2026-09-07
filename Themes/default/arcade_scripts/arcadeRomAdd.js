function arcadeSimulate(elementx, eventName)
{
    var options = arcadeExtend(arcadeDefaultOptions, arguments[2] || {});
    var oEvent, eventType = null;

    for (var name in arcadeEventMatchers)
    {
        if (arcadeEventMatchers[name].test(eventName)) { eventType = name; break; }
    }

    if (!eventType)
        throw new SyntaxError("Only HTMLEvents and MouseEvents interfaces are supported");

    if (document.createEvent)
    {
        oEvent = document.createEvent(eventType);
        if (eventType == "HTMLEvents")
        {
            oEvent.initEvent(eventName, options.bubbles, options.cancelable);
        }
        else
        {
            oEvent.initMouseEvent(eventName, options.bubbles, options.cancelable, document.defaultView,
            options.button, options.pointerX, options.pointerY, options.pointerX, options.pointerY,
            options.ctrlKey, options.altKey, options.shiftKey, options.metaKey, options.button, elementx);
        }
		if (elementx)
			elementx.dispatchEvent(oEvent);
    }
    else
    {
        options.clientX = options.pointerX;
        options.clientY = options.pointerY;
        var evt = document.createEventObject();
        oEvent = arcadeExtend(evt, options);
        elementx.fireEvent("on" + eventName, oEvent);
    }
    return elementx;
}

function arcadeExtend(destination, source) {
    for (var property in source)
      destination[property] = source[property];
    return destination;
};

var arcadeEventMatchers = {
    "HTMLEvents": /^(?:load|unload|abort|error|select|change|submit|reset|focus|blur|resize|scroll)$/,
    "MouseEvents": /^(?:click|dblclick|mouse(?:down|up|over|move|out))$/
};
var arcadeDefaultOptions = {
    pointerX: 0,
    pointerY: 0,
    button: 0,
    ctrlKey: false,
    altKey: false,
    shiftKey: false,
    metaKey: false,
    bubbles: true,
    cancelable: true
};

var ajax2 = function(task,post,callback) {
	var arcadeSmfGetPostParams = arcadeSmfGetAllPostParams(post);
	var arcadeSmfGameName = typeof(arcadeSmfGetPostParams.gname) === "undefined" ? "" : arcadeSmfGetPostParams.gname;
	var arcadeSmfGameScore = typeof(arcadeSmfGetPostParams.gscore) === "undefined" ? "" : arcadeSmfGetPostParams.gscore;
	submitSmfArcadeScoreCode(arcadeSmfGameName, arcadeSmfGameScore);
};
var scorepost = function(href,inputs) {
	for (var k in inputs) {
		if (k == "gname")
			var arcadeSmfGameName = inputs[k];
		else
			var arcadeSmfGameScore = inputs[k];
    }

	submitSmfArcadeScoreCode(arcadeSmfGameName, arcadeSmfGameScore);
};

function submitSmfArcadeScoreCode(arcadeSmfGameName, arcadeSmfGameScore)
{
	// IBP save system
	var siteUrlArray = [];
	var smfDetect = parent.document.getElementById("game_name") ? parent.document.getElementById("game_name").value : (document.getElementById("game_name") ? document.getElementById("game_name").value : "");
	var gscore = arcadeSmfGameScore;
	var gname = arcadeSmfGameName;
	var siteUrl = parent.window.location.href;
	var n = siteUrl.lastIndexOf("/");
	var newUrl = siteUrl.slice(0, n) + "/index.php?act=Arcade&do=newscore";
	if (smfDetect == "")
		var post_data = {"gname":gname, "gscore":gscore};
	else
	{
		var sessid = parent.document.getElementById("gameSmfToken") ? parent.document.getElementById("gameSmfToken").value : "";
		var post_data = {"gname":gname, "gscore":gscore, "savetype":"html52", "score":gscore, "gamesessid":sessid};
	}
	//send data using Ajax
	var strData = [];
	for(var p in post_data){
	   if (post_data.hasOwnProperty(p)) {
		   strData.push(encodeURIComponent(p) + "=" + encodeURIComponent(post_data[p]));
	   }
	}
	var strDataSend = strData.join("&");
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(smfDetect) {
		if (this.readyState == 4 && this.status == 200) {
			if (smfDetect == "")
				setTimeout(function(){ parent.window.location = siteUrl.split("#")[0]; }, 100);
			else
				setTimeout(function(){ parent.window.location = siteUrl.split("#")[0] + ";sa=highscore;#commentform3"; }, 100);
		}
	};
	xhttp.open("POST", newUrl, true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send(strDataSend);
}

function arcadeSmfGetAllPostParams(postData) {
	var queryString = postData;
	var arcadeSmfObj = {};
	if (queryString) {
		queryString = queryString.split("#")[0];
		var arr = queryString.split("&");
		for (var i = 0; i < arr.length; i++) {
			var a = arr[i].split("=");
			var paramName = a[0];
			var paramValue = typeof (a[1]) === "undefined" ? "" : a[1];
			if (typeof paramValue === "string")
				paramValue = paramValue;
			if (!arcadeSmfObj[paramName]) {
				arcadeSmfObj[paramName] = paramValue;
			} else if (arcadeSmfObj[paramName] && typeof arcadeSmfObj[paramName] === "string"){
				arcadeSmfObj[paramName] = [arcadeSmfObj[paramName]];
				arcadeSmfObj[paramName].push(paramValue);
			} else {
				arcadeSmfObj[paramName].push(paramValue);
			}
		}
	}

	return arcadeSmfObj;
}

function arcadeGameAfterLoad() {
	var mycanvas = document.getElementById("game");
	if (mycanvas) {
		mycanvas.width = window.innerWidth;
		mycanvas.height = window.innerHeight;
	}
}

if (window.addEventListener)
	window.addEventListener("load", arcadeGameAfterLoad, false);
else if (window.attachEvent)
	window.attachEvent("onload", arcadeGameAfterLoad);