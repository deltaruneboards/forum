/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

var adsLoader = [], requestAds, registerAdserver, adsEnabled, createElement, addScript;
requestAds = function (avoid) {
	return '';
}
addScript = function (a, b, c) {
	return '';
}
registerAdserver = function (avoid) {
	return '';
}
createElement = function (avoid) {
	return '';
}
adsEnabled = function () {
	return 0;
}

var ajax2 = function(task,post,callback) {
	var arcadeSmfGetPostParams = arcadeSmfGetAllPostParams(post);
	var arcadeSmfGameName = typeof(arcadeSmfGetPostParams.gname) === "undefined" ? "" : arcadeSmfGetPostParams.gname;
	var arcadeSmfGameScore = typeof(arcadeSmfGetPostParams.gscore) === "undefined" ? "" : arcadeSmfGetPostParams.gscore;
	submitSmfArcadeScoreCode(arcadeSmfGameName, arcadeSmfGameScore);
}
var scorepost = function(href,inputs) {
	for (var k in inputs) {
		if (k == "gname")
			var arcadeSmfGameName = inputs[k];
		else
			var arcadeSmfGameScore = inputs[k];
    }

	submitSmfArcadeScoreCode(arcadeSmfGameName, arcadeSmfGameScore);
}

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
	if (window.location.href.includes("pop=1;full=0")) {
		if (document.getElementsByTagName("BODY")[0]) {
			setTimeout(function() {
				var arcadePopupWindowFull = document.getElementsByTagName("BODY")[0];
				var arcadePopupBody = arcadePopupWindowFull.innerHTML;
				if (arcadePopupBody.includes("machine4fun.min.js") && arcadePopupBody.includes("<canvas")) {
					window.resizeTo(document.getElementById("gameObj").style.width, document.getElementById("gameObj").style.height);
					arcadePopupWindowFull.style = "width: 100%;height: 100%;overflow: hidden !important;z-index: 998;left: 0;top: 0;position: fixed;";
					document.getElementById("gameObjDiv").style = "width: 100vw;height: 100vh;overflow: hidden !important;z-index: 999;left: 0;top: 0;position: fixed;";
					if (document.getElementsByClassName("gamepop2")) {
						document.getElementsByClassName("gamepop2")[0].style.overflowX = "hidden !important";
						document.getElementsByClassName("gamepop2")[0].style.overflowY = "hidden !important";
					}
				}
				else
					document.getElementById("gameObj").style = "width: 100%;height: 100%;overflow: hidden;z-index: 99;";
			}, 2000);
		}
	}
	else {
		var gameObjectTag = document.getElementsByTagName("BODY")[0].getElementsByTagName("OBJECT")[0];
		if (gameObjectTag) {
			var gameObjectContents = gameObjectTag.contentDocument.children || gameObjectTag.contentWindow.document.children;
			var arcadeCheckMachine4Fun = gameObjectContents[0] ? 1 : -1;
			if (arcadeCheckMachine4Fun > -1) {
				setTimeout(function() {
					var machine4funCheck = false, gameBodyChange = gameObjectContents[0].children;
					for (let i = 0; i < gameObjectContents[0].children.length; i++) {
						if (gameObjectContents[0].children[i].tagName.toUpperCase() == "HEAD") {
							if (gameObjectContents[0].children[i].innerHTML && gameObjectContents[0].children[i].innerHTML.includes("<title>machine4fun</title>")) {
								machine4funCheck = true;
								gameObjectContents[0].children[i].style = "width: 100%;height: 100%;overflow: hidden !important;z-index: -1;";
							}

						}
						if (gameObjectContents[0].children[i].tagName.toUpperCase() == "BODY" && machine4funCheck == true) {
							var XgameObjectContents = gameObjectContents[0].children[i].childNodes;
							for (let z =0;z < XgameObjectContents.length; z++) {
								if (XgameObjectContents[z].tagName == "DIV" && XgameObjectContents[z].innerHTML.includes("<canvas"))
									XgameObjectContents[z].style = "position: fixed;top: 0px;left: 0px;";
							}
						}
					}
				}, 2000);
			}
		}
		else {
			setTimeout(function() {
				document.getElementsByTagName("BODY")[0].style = "width: 100%;height: 100%;overflow: hidden;z-index: 99;";
			}, 2000);
		}
	}
}

if (window.addEventListener)
	window.addEventListener("load", arcadeGameAfterLoad, false);
else if (window.attachEvent)
	window.attachEvent("onload", arcadeGameAfterLoad);
else
	window.onload = arcadeGameAfterLoad();