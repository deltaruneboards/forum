/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */

var ajax_wait = false;
var ajaxCallBack, arcadeHasFlash;
if (typeof smf_avatars_url === 'undefined')
	var smf_avatars_url;
try {
	var checkArcadeFlashObject = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');

	if (checkArcadeFlashObject) {
		arcadeHasFlash = true;
	}
} catch (e) {
	if (navigator.mimeTypes && navigator.mimeTypes['application/x-shockwave-flash'] != undefined &&	navigator.mimeTypes['application/x-shockwave-flash'].enabledPlugin) {
		arcadeHasFlash = true;
	}
}
if (arcadeHasFlash) {
	document.cookie = "smfArcadeSetFlashCookie=shockwave";
	console.log("flash detected");
}
else {
	document.cookie = "smfArcadeSetFlashCookie=ruffle";
	console.log("flash not detected");
}
function arcadeAjaxSend(url, values, callback)
{
	// Wait a second
	if (ajax_wait)
	{
		setTimeout(arcadeAjaxSend(url, values, callback), 1000);

		return;
	}

	ajax_indicator(true);
	ajaxCallBack = callback;
	ajax_wait = true;

	arcadeSendXMLDocument(url, values, onAjaxDone);
}

function arcadeSendXMLDocument(sUrl, sContent, funcCallback)
{
	if (!window.XMLHttpRequest)
		return false;

	var oSendDoc = new window.XMLHttpRequest();
	var oCaller = this;
	if (typeof(funcCallback) != 'undefined')
	{
		oSendDoc.onreadystatechange = function () {
			if (oSendDoc.readyState != 4)
				return;

			if (oSendDoc.responseXML != null && oSendDoc.status == 200)
				funcCallback.call(oCaller, oSendDoc.responseXML);
			else
				funcCallback.call(oCaller, false);
		};
	}
	oSendDoc.open('POST', sUrl, true);
	if ('setRequestHeader' in oSendDoc)
		oSendDoc.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		oSendDoc.setRequestHeader("Access-Control-Allow-Origin", "*");
	oSendDoc.send(sContent);

	return true;
}

function arcadeHtmlDoc(url, values, callback, extra)
{
	if (!window.XMLHttpRequest)
		return false;

	var htmlDoc = new window.XMLHttpRequest();
	if (typeof(callback) != "undefined")
	{
		htmlDoc.onreadystatechange = function ()
		{
			if (htmlDoc.readyState != 4)
				return;

			if (htmlDoc.responseText != null && htmlDoc.status == 200)
				callback(htmlDoc.responseText, extra);
			else
				callback(false, extra);
		};
	}
	htmlDoc.open('POST', url, true);
	if (typeof(htmlDoc.setRequestHeader) != "undefined")
		htmlDoc.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		htmlDoc.setRequestHeader("Access-Control-Allow-Origin", "*");
	htmlDoc.send(values);

	return true;
}

function onAjaxDone(XMLDoc)
{
	ajax_indicator(false);
	ajax_wait = false;

	// Call the callback
	ajaxCallBack(XMLDoc);
}

// Rating
var rate_url = smf_scripturl + "?action=" + arcadeDetectGameAction + ";sa=rate;xml";

function arcade_rate(rating, game)
{
	var post = new Array();

	waiting = true;
	post[0] = "game=" + parseInt(game);
	post[1] = "rate=" + parseInt(rating);

	arcadeAjaxSend(rate_url, post.join("&"), onArcadeRate);
}

function onArcadeRate(XMLDoc)
{
	var rating = XMLDoc.getElementsByTagName("rating")[0].firstChild.nodeValue;
	var i = 0;

	for (i = 1; i <= 5; i++)
	{
		if (i <= rating)
			document.getElementById('imgrate' + i).src = smf_default_theme_url + '/images/arc_icons/arcade_star.gif';
		else
			document.getElementById('imgrate' + i).src = smf_default_theme_url + '/images/arc_icons/arcade_star2.gif';
	}
}

// Favorite
var imgfav = '';
var favorite_url = smf_scripturl + "?action=" + arcadeDetectGameAction + ";sa=favorite;xml"

function arcade_favorite(game)
{
	var post = new Array();

	waiting = true;

	post[0] = "game=" + parseInt(game);
	imgfav = 'favgame' + parseInt(game);

	ajax_indicator(true);

	arcadeAjaxSend(favorite_url, post.join("&"), onArcadeFavorite);
}

function onArcadeFavorite(XMLDoc)
{
	if (parseInt(XMLDoc.getElementsByTagName("state")[0].firstChild.nodeValue) == 0)
		document.getElementById(imgfav).src = smf_default_theme_url + '/images/arc_icons/star4.gif';

	else
		document.getElementById(imgfav).src = smf_default_theme_url + '/images/arc_icons/star3.gif';
}

// Comment
var editing = false, score, game, editscore = 0, newerScoreComment = [], comment_url = smf_scripturl + "?action=" + arcadeDetectGameAction + ";sa=highscore;xml;#commentform3";
function arcadeCommentEdit(score, game, save)
{
	var divComment = "comment" + parseInt(score);
	var divEdit = "edit" + parseInt(score);
	var editLink = "editlink" + parseInt(score);
	var textbox = "c"  + parseInt(score);
	editscore = score;
	for (var key in sessionStorage) {
		if (key.indexOf('arcadeHighScore') !== -1)
			sessionStorage.removeItem(key);
	}
	newerScoreComment[score] = prompt(localStorage.getItem("arcadeEnterComment"));
	arcadeCommentSave(score, game, newerScoreComment[score]);
	editing = true;

	return true;
}

function arcadeCommentSave(score, game, comment)
{
	var post = new Array();
	var textbox = "c"  + parseInt(score);
	sessionStorage.setItem('arcadeHighScoreId' + score, score);
	sessionStorage.setItem('arcadeHighScoreComment' + score, comment.replace(/[^A-Za-z0-9 _.!]/, '_'));
	sessionStorage.setItem('arcadeHighScoreGame' + score, game);
	post[0] = "game=" + game;
	post[1] = "score=" + score;
	post[2] = "csave=1";
	post[3] = "new_comment=" + encodeURIComponent((sessionStorage.getItem('arcadeHighScoreComment' + score).replace(/&#/g, "&#38;#"))).replace(/\+/g, "%2B");

	var newcomment_url = smf_scripturl + "?action=" + arcadeDetectGameAction + ";sa=arcadecscore;time=1;game=" + game + ";score=" + score + ";csave=" + (Math.floor(Math.random() * 9988) + 11) + ";#commentform3";
	arcadeSendComment(newcomment_url, post);
}

function arcadeSendComment(arcadeUrl, arcadeData) {
    var client = new XMLHttpRequest();
    client.open("POST", arcadeUrl, false);
    client.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
    client.send(arcadeData.join("&"));
}

// Search
function gameSuggest(sessionID, textID)
{
	var lastSearch = "", lastDirtySearch = "";
	var textHandle = document.getElementById(textID);
	var suggestDivHandle = document.getElementById('suggest_' + textID);
	var selectedDiv = false;
	var cache = [];
	var displayData = [];
	var maxDisplayQuantity = 15;
	var positionComplete = false;
	var onAddCallback = false;
	this.registerCallback = registerCallback;

	xmlRequestHandle = null;
	function gameSuggestInit()
	{
		if (!window.XMLHttpRequest)
			return false;

		createEventListener(textHandle);
		textHandle.addEventListener('keyup', function(){autoSuggestUpdate();}, false);
		textHandle.addEventListener('change', function(){autoSuggestUpdate();}, false);
		textHandle.addEventListener('blur', function(){autoSuggestHide();}, false);
		textHandle.addEventListener('focus', function(){autoSuggestUpdate();autoSuggestShow();}, false);
		textHandle.addEventListener('input', (event) => {autoSuggestUpdate();});

		return true;
	}

	function registerCallback(callbackType, callbackFunction)
	{
		if (callbackType == 'add')
			onAddCallback = callbackFunction;
	}

	function postitionDiv()
	{
		// Only do it once.
		if (positionComplete)
			return true;

		positionComplete = true;

		// Put the div under the text box.
		var parentPos = smf_itemPos(textHandle);

		suggestDivHandle.style.left = parentPos[0] + 'px';
		suggestDivHandle.style.top = (parentPos[1] + textHandle.offsetHeight) + 'px';
		suggestDivHandle.style.width = textHandle.style.width;

		return true;
	}

	function itemClicked(ev)
	{
		if (!ev)
			ev = window.event;

		if (ev.srcElement)
			curElement = ev.srcElement;
		else if (ev.memberid)
			curElement = ev;
		else
			curElement = this;

		if (document.getElementById('suggest_template_' + textID))
		{

		}

		return true;
	}

	function autoSuggestHide()
	{
		// Delay to allow events to propogate through....
		hideTimer = setTimeout(function()
			{
				suggestDivHandle.style.visibility = 'hidden';
			}, 250
		);
	}

	function autoSuggestShow()
	{
		postitionDiv();

		suggestDivHandle.style.visibility = 'visible';
	}

	function populateDiv(results)
	{
		while (suggestDivHandle.childNodes[0])
		{
			suggestDivHandle.removeChild(suggestDivHandle.childNodes[0]);
		}

		if (typeof(results) == 'undefined')
		{
			displayData = [];
			return false;
		}

		var newDisplayData = [];
		for (i = 0; i < (results.length > maxDisplayQuantity ? maxDisplayQuantity : results.length); i++)
		{
			// Create the sub element
			newDivHandle = document.createElement('div');
			newDivHandle.className = 'game_suggest_item';
			newDivHandle.style.width = textHandle.style.width;

			newGamelink = document.createElement('a');
			newGamelink.href = smf_prepareScriptUrl(smf_scripturl) + 'action=' + (results[i]['action']) + ';sa=play;game=' + (results[i]['id']) + '';
			newGamelink.innerHTML = results[i]['name'];

			newDivHandle.appendChild(newGamelink);

			suggestDivHandle.appendChild(newDivHandle);

			newDisplayData[i] = newDivHandle;
		}

		displayData = newDisplayData;

		return true;
	}

	function onSuggestionReceived(oXMLDoc)
	{
		var i = 0;
		cache = [];
		$(oXMLDoc).find("game").each(function () {
			cache[i] = new Array(2);
			cache[i]['id'] =  $(this).attr('id');
			cache[i]['name'] = $(this).first().text();
			cache[i]['action'] = $(this).attr('action');
			i++;
		});

		populateDiv(cache);

		if (i == 0)
			autoSuggestHide();
		else
			autoSuggestShow();

		return true;
	}

	function autoSuggestUpdate()
	{
		if (isEmptyText(textHandle))
		{
			autoSuggestHide();

			return true;
		}

		if (textHandle.value == lastDirtySearch)
			return true;
		lastDirtySearch = textHandle.value;

		var searchString = textHandle.value;

		realLastSearch = lastSearch;
		lastSearch = searchString;

		if (searchString == "" || searchString.length < 3)
			return true;
		else if (searchString.substr(0, realLastSearch.length) == realLastSearch)
		{
			// Instead of hitting the server again, just narrow down the results...
			var newcache = [], j = 0;
			var lowercaseSearch = searchString.toLowerCase();
			for (var k = 0; k < cache.length; k++)
			{
				if (cache[k]['name'].substr(0, searchString.length).toLowerCase() == lowercaseSearch)
				{
					newcache[j++] = cache[k];
				}
			}

			cache = [];
			if (newcache.length != 0)
			{
				cache = newcache;
				// Repopulate.
				populateDiv(cache);

				// Check it can be seen.
				autoSuggestShow();

				return true;
			}
		}

		if (xmlRequestHandle != null && typeof(xmlRequestHandle) == "object") {
			xmlRequestHandle.abort();
		}

		searchString = searchString.php_to8bit().php_urlencode();
		let arcadeDetectAction = window.location.href.indexOf("action=retro_arch") > -1 ? "retro_arch" : "arcade";

		// Get the document.
		//xmlRequestHandle = getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=' + arcadeDetectAction + ';sa=suggest;name=' + searchString + ';xml;time=' + (new Date().getTime()), onSuggestionReceived);
		xmlRequestHandle = $.ajax({
			type: "GET",
			url: smf_prepareScriptUrl(smf_scripturl) + 'action=' + arcadeDetectAction + ';sa=suggest;name=' + searchString + ';time=' + (new Date().getTime()) + ';xml',
			dataType: "xml",
			success: function(data) {
				onSuggestionReceived(data);
				return true;
			},
			fail: function(jqXHR, textStatus, errorThrown) {
				console.error('AJAX Error:');
				console.error('jqXHR:', jqXHR);
				console.error('textStatus:', textStatus);
				console.error('errorThrown:', errorThrown);
			}
		});
		return true;
	}

	gameSuggestInit();
}

// Select Games for Arcade
function gameSelector(sessionID, textID)
{
	var lastSearch = "", lastDirtySearch = "";
	var textHandle = document.getElementById(textID);
	var suggestDivHandle = document.getElementById('suggest_' + textID);
	var selectedDiv = false;

	var cache = [];
	var displayData = [];
	var maxDisplayQuantity = 15;

	var positionComplete = false;
	var onAddCallback = false;
	var doAutoAdd = false;

	var maxRound = 0;
	var hideTimer = false;

	xmlRequestHandle = null;

	this.registerCallback = registerCallback;
	this.deleteItem = deleteAddedItem;
	this.onSubmit = onElementSubmitted;

	function gameSuggestInit2()
	{
		if (!window.XMLHttpRequest)
			return false;

		createEventListener(textHandle);
		textHandle.addEventListener('keyup', function(){autoSuggestUpdate();}, false);
		textHandle.addEventListener('change', function(){autoSuggestUpdate();}, false);
		textHandle.addEventListener('blur', function(){autoSuggestHide();}, false);
		textHandle.addEventListener('focus', function(){autoSuggestUpdate();autoSuggestShow();}, false);
		textHandle.addEventListener('input', (event) => {autoSuggestUpdate();});

		return true;
	}

	function registerCallback(callbackType, callbackFunction)
	{
		if (callbackType == 'add')
			onAddCallback = callbackFunction;
	}

	function onElementSubmitted()
	{
		return_value = true;
		// Do we have something that matches the current text?
		for (i = 0; i < cache.length; i++)
		{
			if (lastSearch.toLowerCase() == cache[i]['name'].toLowerCase().substr(0, lastSearch.length))
			{
				// Exact match?
				if (lastSearch.length == cache[i]['name'].length)
				{
					// This is the one!
					return_value = {'id': cache[i]['id'], 'gamename': cache[i]['name'], 'action': cache[i]['action']};
					break;
				}

				// If we have two matches don't find anything.
				if (return_value != true)
					return_value = false;
				return_value = {'id': cache[i]['id'], 'gamename': cache[i]['name'], 'action': cache[i]['action']};
			}
		}

		if (return_value == true || return_value == false)
			return return_value;
		else
		{
			addGame(return_value, true);
			return false;
		}
	}

	function postitionDiv()
	{
		// Only do it once.
		if (positionComplete)
			return true;

		positionComplete = true;

		// Put the div under the text box.
		var parentPos = smf_itemPos(textHandle);

		suggestDivHandle.style.left = parentPos[0] + 'px';
		suggestDivHandle.style.top = (parentPos[1] + textHandle.offsetHeight) + 'px';
		suggestDivHandle.style.width = textHandle.style.width;

		return true;
	}

	function itemClicked(ev)
	{
		if (!ev)
			ev = window.event;

		if (ev.srcElement)
			curElement = ev.srcElement;
		else if (ev.memberid)
			curElement = ev;
		else
			curElement = this;

		var curGame = {'id': curElement.gameid, 'gamename': curElement.innerHTML, 'action': curElement.action};
		addGame(curGame);

		return true;
	}

	function removeLastSearchString()
	{
		tempText = textHandle.value.toLowerCase();
		tempSearch = lastSearch.toLowerCase();
		startString = tempText.indexOf(tempSearch);

		if (startString != -1)
		{
			while (startString > 0)
			{
				if (tempText.charAt(startString - 1) == '"' || tempText.charAt(startString - 1) == ',' || tempText.charAt(startString - 1) == ' ')
				{
					startString--;
					if (tempText.charAt(startString - 1) == ',')
						break;
				}
				else
					break;
			}

			textHandle.value = textHandle.value.substr(0, startString);
		}
		else
			textHandle.value = '';
	}

	function addGame(curGame, fromSubmit)
	{
		// Is there a div that we are duplicating and populating?
		if (document.getElementById('suggest_template_' + textID))
		{
			curRound = ++maxRound;

			// What will the new element be called?
			newID = 'suggest_template_' + textID + '_' + curRound;
			// Better not exist?
			while (document.getElementById(newID))
			{
				curRound = ++maxRound;
				newID = 'suggest_template_' + textID + '_' + curRound;
			}

			if (!document.getElementById(newID))
			{
				brotherNode = document.getElementById('suggest_template_' + textID);

				newNode = brotherNode.cloneNode(true);
				brotherNode.parentNode.insertBefore(newNode, brotherNode);
				newNode.id = newID;

				// If it supports remove this will be the javascript.
				deleteCode = 'gameSelector' + textID + '.deleteItem(' + curRound + ');';

				// Parse in any variables.
				newNode.innerHTML = newNode.innerHTML.replace(/::GAME_NAME::/g, curGame.gamename).replace(/::ROUND::/g, curRound).replace(/'*(::|%3A%3A)GAME_ID(::|%3A%3A)'*/g, curGame.id).replace(/'*::DELETE_ROUND_URL::'*/g, deleteCode);

				newNode.style.visibility = 'visible';
				newNode.style.display = '';
			}
		}

		removeLastSearchString();

		if (textHandle.value != '' && fromSubmit)
			doAutoAdd = true;
		else
			doAutoAdd = false;

		// Update the fellow..
		autoSuggestUpdate();
	}

	function deleteAddedItem(round)
	{
		// Remove the div if it exists.
		divID = 'suggest_template_' + textID + '_' + round;
		if (document.getElementById(divID))
		{
			nodeRemove = document.getElementById(divID);
			nodeRemove.parentNode.removeChild(nodeRemove);
		}

		return false;
	}

	function autoSuggestHide()
	{
		// Delay to allow events to propogate through....
		hideTimer = setTimeout(function()
			{
				suggestDivHandle.style.visibility = 'hidden';
			}, 250
		);
	}

	function autoSuggestShow()
	{
		if (hideTimer)
		{
			clearTimeout(hideTimer);
			hideTimer = false;
		}

		postitionDiv();

		suggestDivHandle.style.visibility = 'visible';
	}

	function populateDiv(results)
	{
		while (suggestDivHandle.childNodes[0])
		{
			suggestDivHandle.removeChild(suggestDivHandle.childNodes[0]);
		}

		if (typeof(results) == 'undefined')
		{
			displayData = [];
			return false;
		}

		var newDisplayData = [];
		for (i = 0; i < (results.length > maxDisplayQuantity ? maxDisplayQuantity : results.length); i++)
		{
			// Create the sub element
			newDivHandle = document.createElement('div');
			newDivHandle.className = 'game_suggest_item';
			newDivHandle.style.width = textHandle.style.width;

			createEventListener(newDivHandle);
			newDivHandle.addEventListener('click', itemClicked, false);
			newDivHandle.gameid = results[i]['id'];
			newDivHandle.action = results[i]['action'];
			newDivHandle.innerHTML = results[i]['name'];

			suggestDivHandle.appendChild(newDivHandle);

			newDisplayData[i] = newDivHandle;
		}

		displayData = newDisplayData;

		return true;
	}

	function onSuggestionReceived(oXMLDoc)
	{
		var i = 0;
		cache = [];
		$(oXMLDoc).find("game").each(function () {
			cache[i] = new Array(2);
			cache[i]['id'] =  $(this).attr('id');
			cache[i]['name'] = $(this).first().text();
			cache[i]['action'] = $(this).attr('action');
			i++;
		});

		populateDiv(cache);

		if (i == 0)
			autoSuggestHide();
		else
			autoSuggestShow();

		return true;
	}

	function autoSuggestUpdate()
	{
		if (isEmptyText(textHandle))
		{
			autoSuggestHide();

			return true;
		}

		if (textHandle.value == lastDirtySearch)
			return true;
		lastDirtySearch = textHandle.value;

		var searchString = textHandle.value;

		realLastSearch = lastSearch;
		lastSearch = searchString;

		if (searchString == "" || searchString.length < 3)
			return true;
		else if (searchString.substr(0, realLastSearch.length) == realLastSearch)
		{
			// Instead of hitting the server again, just narrow down the results...
			var newcache = [], j = 0;
			var lowercaseSearch = searchString.toLowerCase();
			for (var k = 0; k < cache.length; k++)
			{
				if (cache[k]['name'].substr(0, searchString.length).toLowerCase() == lowercaseSearch)
				{
					newcache[j++] = cache[k];
				}
			}

			cache = [];
			if (newcache.length != 0)
			{
				cache = newcache;
				// Repopulate.
				populateDiv(cache);

				// Check it can be seen.
				autoSuggestShow();

				return true;
			}
		}

		if (xmlRequestHandle != null && typeof(xmlRequestHandle) == "object") {
			xmlRequestHandle.abort();
		}

		searchString = searchString.php_to8bit().php_urlencode();
		let arcadeDetectAction = window.location.href.indexOf("action=retro_arch") > -1 ? "retro_arch" : "arcade";

		// Get the document.
		// xmlRequestHandle = getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + 'action=' + arcadeDetectAction + ';sa=suggest;name=' + searchString + ';xml;textid=' + textID + ';time=' + (new Date().getTime()), onSuggestionReceived);
		xmlRequestHandle = $.ajax({
			type: "GET",
			url: smf_prepareScriptUrl(smf_scripturl) + 'action=' + arcadeDetectAction + ';sa=suggest;name=' + searchString + ';textid=' + textID + ';time=' + (new Date().getTime()) + ';xml',
			dataType: "xml",
			success: function(data) {
				onSuggestionReceived(data);
				return true;
			},
			fail: function(jqXHR, textStatus, errorThrown) {
				console.error('AJAX Error:');
				console.error('jqXHR:', jqXHR);
				console.error('textStatus:', textStatus);
				console.error('errorThrown:', errorThrown);
			}
		});

		return true;
	}

	gameSuggestInit2();
}

// Floating info box
function arcadeBox(text)
{
	if (document.getElementById('arcadebox_html') && document.getElementById('arcadebox')) {
		document.getElementById('arcadebox_html').innerHTML = text;

		if (document.getElementById('arcadebox').style.display == 'none')
			document.getElementById('arcadebox').style.display = 'block';
		else
			document.getElementById('arcadebox').style.display = 'none';
	}
}

function arcadeBoxMove(evt)
{
	document.getElementById('arcadebox').style.top = evt.clientY + 30 + 'px';
	document.getElementById('arcadebox').style.left = evt.clientX  + 20 + 'px';
}

// Quick action change
function QactionChange()
{
	document.getElementById('qcategory').style.display = 'none';
	document.getElementById('qset').style.display = 'none';

	if (document.getElementById('qaction').value == 'change')
	{
		document.getElementById('qcategory').style.display = '';
		document.getElementById('qset').style.display = '';
	}
	else if (document.getElementById('qaction').value == 'clear_scores')
	{
		document.getElementById('qset').style.display = '';
	}
	else
	{

	}
}

function getRefToDivMod(divID)
{
	var oDoc = document;
	if(document.layers)
	{
		if(oDoc.layers[divID])
			return oDoc.layers[divID];
		else
		{
			for(var x = 0, y; !y && x < oDoc.layers.length; x++)
				y = getRefToDivNest(divID,oDoc.layers[x].document);

			return y;
		}
	}

	if(document.getElementById)
		return oDoc.getElementById(divID);

	if(document.all)
		return oDoc.all[divID];

	return oDoc[divID];
}

function resizeArcadeWinTo(idOfDiv)
{
	var oH = getRefToDivMod(idOfDiv);
	if(!oH)
		return false;

	var oW = oH.clip ? oH.clip.width : oH.offsetWidth;
	var oH = oH.clip ? oH.clip.height : oH.offsetHeight;
	if( !oH )
		return false;

	var x = window; x.resizeTo(oW+30, oH+90);
	var myW = 0, myH = 0, d = x.document.documentElement, b = x.document.body;
	if(x.innerWidth)
	{
		myW = x.innerWidth;
		myH = x.innerHeight;
	}
	else if(d && d.clientWidth)
	{
		myW = d.clientWidth;
		myH = d.clientHeight;
	}
	else if(b && b.clientWidth)
	{
		myW = b.clientWidth;
		myH = b.clientHeight;
	}

	if(window.opera && !document.childNodes)
		myW += 16;

	x.resizeTo(oW+((oW+30)-myW), oH+((oH + 90)-myH));
}