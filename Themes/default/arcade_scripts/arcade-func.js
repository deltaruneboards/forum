/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

if (window.addEventListener)
	window.addEventListener("load", function (){
		arcadeFontAwesome();		
		return false;
	});
else
	window.attachEvent("onload", function (){
		arcadeFontAwesome();		
		return false;
	});

// Add fa classes
function arcadeFontAwesome()
{
	var fazz=0, fazzClass = 0, arcadeFa = document.getElementsByClassName("fa"), faName, faNewName, fazzReg;
	var arcadeCurrentClasses = ["fa-arcade", "fa-arcade_arena", "fa-arcade_stats", "fa-arcade_administrator", "fa-archivetype", "fa-gametype", "fa-score", "fa-fullscreen"];
	var arcadeReplacementClasses = ["fa-gamepad", "fa-shield", "fa-bar-chart", "fa-cogs", "fa-files-o", "fa-save", "fa-line-chart", "fa-arrows"];
	if (arcadeFa) {
		for(fazz=0;fazz<arcadeFa.length;fazz++) {
			faArr = arcadeFa[fazz].className.split(" ");
			for(fazzClass=0;fazzClass<arcadeCurrentClasses.length;fazzClass++) {
				faName = arcadeCurrentClasses[fazzClass].toString();
				faNewName = arcadeReplacementClasses[fazzClass].toString();
				if (faArr.indexOf(faNewName) == -1) {
					arcadeFa[fazz].className = arcadeFa[fazz].className.replace(faName + " ", faNewName + " ");
				}
			}
		}
	}
}

 // Open a new game popup window
function myGamePopupArcade(strURL,strWidth,strHeight,displayType,fullPop)
{
	var fullPop = typeof fullPop == "undefined" ? false : fullPop;
	var strOptions="";
	var WindowObjectReference = null;
	var arcadeFullEnable;
	var displayType = typeof displayType !== "undefined" ? parseInt(displayType) : 0;
	if (displayType < 0 || displayType > 5)
		displayType = 0;
	else
		displayType = isNaN(displayType) ? 0 : displayType;

	if (displayType > 2)
	{
		displayType = displayType-2;
		var changeWidthDims = 1.01;
		var changeHeightDims = 1.07;
	}
	else
	{
		var changeWidthDims = 1;
		var changeHeightDims = 1;
	}

	strWidth = strWidth*changeWidthDims;
	strHeight = strHeight*changeHeightDims;

	var strType = [
		"console",
		"fixed",
		"elastic",
		"adjust"
	];

	if (strType[displayType]=="fixed")
		strOptions = "status,height=" + strHeight + ",width=" + strWidth;
	else if (strType[displayType]=="elastic")
		strOptions = "toolbar,menubar,scrollbars,resizable,location,height=" + strHeight + ",width=" + strWidth;
	else if (strType[displayType]=="adjust")
		strOptions = "status,resizable,height=" + strHeight + ",width=" + strWidth;
	else
		strOptions = "resizable,height=" + strHeight + ",width=" + strWidth;

	var topY = window.top.outerHeight / 2 + window.top.screenY - ( strHeight / 2);
    var leftX = window.top.outerWidth / 2 + window.top.screenX - ( strWidth / 2);
	strOptions += ",top=" + topY + ",left=" + leftX;
	if (!fullPop)
		WindowObjectReference = window.open(strURL + ";full=0", "newWin", strOptions);
	else
		WindowObjectReference = window.open(strURL + ";full=1", "newWin", strOptions);
	if (window.focus)
		WindowObjectReference.focus();

	if (!fullPop)
		WindowObjectReference.resizeTo(strWidth*1.035, strHeight*1.28);
	else
	{
		WindowObjectReference.resizeTo(screen.width, screen.height);
	}
}

function getUrlVarsArcade()
{
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
	return vars;
}

function convertToDateArcade(variableEpoch)
{
	var sourceEpoch = variableEpoch;
	sourceEpoch = parseInt(sourceEpoch);
	if (isNaN(sourceEpoch)) {
		var arcadeDateDisplay = 'Invalid Timestamp';
	}
	else {
		if (sourceEpoch <= 9999999999) {
			sourceEpoch *= 1000;
		}
		var arcadeDateDisplay = new Date(sourceEpoch).toUTCString();
	}
	return arcadeDateDisplay;
}

function writeArcadeCookie(name,value,days)
{
	var date, expires;
	if (days)
	{
		date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		expires = "; expires=" + date.toGMTString();
	}
	else
		expires = "";

	if (location.protocol !== 'https:')
		document.cookie = name + "=" + value + expires + ";SameSite=Lax;secure;path=/";
	else
		document.cookie = name + "=" + value + expires + ";SameSite=Lax;path=/";
}

function readArcadeCookie(name)
{
	var i, c, ca, nameEQ = name + "=";
	ca = document.cookie.split(";");
	for(i=0;i < ca.length;i++)
	{
		c = ca[i];
		while (c.charAt(0)==" ")
			c = c.substring(1,c.length);

		if (c.indexOf(nameEQ) == 0)
			return c.substring(nameEQ.length,c.length);
	}

	return "";
}

function submitArcadeSkin()
{
	document.getElementById("arcadeSkin").onchange = function() {
		document.forms["admin_form_wrapper"].submit();
	};
}

function arcadefadeout()
{
	var arcFade = document.getElementById("arcade_fadeout");
	if (arcFade) {
		arcFade.style = "opacity: 0;font-style: oblique;";
	}
}

function addSmfArcadeEvent(evnt, elemx, func)
{
	var detectArcadeBrowser = detectMyArcadeIE();

	if (elemx.addEventListener)
		elemx.addEventListener(evnt, func, false);
	else if (elemx.attachEvent)
		elemx.attachEvent('on'+evnt, func);
	else
		elemx.onload = func();
}

function detectMyArcadeIE()
{
	var undef,
	v = 3,
	div = document.createElement("div"),
	all = div.getElementsByTagName("i");

	while (
		div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->',
		all[0]
	);

	return v > 4 ? v : undef;
}

function alternateArcadeImg(imgId)
{
    document.getElementById(imgId).src = smf_default_theme_url + '/images/arc_icons/game.gif'
}

function throwArcadeErr(arcmsg)
{
    // throw new Error(arcmsg);
	console.log(arcmsg);
}

function arcadeOnmouseOverEvent(gameMouseOver, linkType)
{
	if (linkType == 1) {
		gameMouseOver.style = "display: inline;cursor: pointer;text-decoration: underline;";
	}
	else {
		gameMouseOver.style = "display: inline;cursor: pointer;";
	}
}

function arcadeOnmouseOutEvent(gameMouseOver, linkType)
{
	if (linkType == 1) {
		gameMouseOver.style = "display: inline;cursor: initial;text-decoration: initial;";
	}
	else {
		gameMouseOver.style = "display: inline;cursor: initial;";
	}
}

function arcadePopWindowEvent(postGamePopId,strWidth,strHeight,displayType,fullPop)
{
	var postGameId = document.getElementById('arcadePostPopup' + postGamePopId);
	var strUrl = postGameId.href;
	return myGamePopupArcade(strUrl,strWidth,strHeight,displayType,fullPop);
}