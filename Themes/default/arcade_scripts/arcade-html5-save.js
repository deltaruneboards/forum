/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
function saveHtml5GameSmf(newhighscore, gamename)
{
	var gamename = typeof gamename != "undefined" ? gamename : "";
	var gameSessid = parent.document.getElementById("gameSmfToken") ? parent.document.getElementById("gameSmfToken").value : "";
	if (gameSessid != "")
	{
		var siteUrl = parent.document.getElementById("html5smfGameUrl").value;
		var gameFull = parent.document.getElementById("gameSmfFullscreen").value;
		var gameExit = parent.document.getElementById("gameexit").value;
		var gameTime = parent.document.getElementById("smfgametime").value;
		var gameId = parent.document.getElementById("game").value;
		var gname = parent.document.getElementById("game_name").value;
		var gamePop = parent.document.getElementById("popup").value;
		var gameSessid = parent.document.getElementById("gameSmfToken").value;
		var gscore = parseInt(newhighscore);
		var saveType = "html5";
		var n = siteUrl.lastIndexOf("/");
		var newUrl = siteUrl.slice(0, n) + "/index.php?action=arcade;sa=html5Game";
		var post_data = {"game_name":gname, "score":gscore, "smfgametime":gameTime, "game":gameId, "gamesessid":gameSessid, "popup":gamePop, "gameSmfFullscreen":gameFull, "gameexit":gameExit, "html52":saveType};
		//send data using Ajax
		var strData = [];
		for(var p in post_data){
		   if (post_data.hasOwnProperty(p)) {
			   strData.push(encodeURIComponent(p) + "=" + encodeURIComponent(post_data[p]));
		   }
		}
		var strDataSend = strData.join("&");
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				console.log("Saving score for " + gname);
				setTimeout(function(){parent.window.location = gamePop == 1 ? siteUrl.split('#')[0] + "pop=1;sa=highscore;#commentform3" : siteUrl.split('#')[0] + "sa=highscore;#commentform3";}, 3000);
			}
		};
		xhttp.open("POST", newUrl, true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send(strDataSend);
	}
	else
	{
		var gameFrameSrc = typeof window.frameElement.src != "undefined" ? window.frameElement.src : "";
		var gnameQuery = gameFrameSrc.substr(gameFrameSrc.lastIndexOf('/') + 1);
		var gscore = parseInt(newhighscore);
		var gname = gnameQuery != "" ? gnameQuery : gamename;
		var siteUrl = parent.window.location.href;
		var n = siteUrl.lastIndexOf("/");
		var newUrl = siteUrl.slice(0, n) + "/index.php?act=Arcade&do=newscore";
		var post_data = {'gname':gname, 'gscore':gscore};
		//send data using Ajax
		var strData = [];
		for(var p in post_data){
		   if (post_data.hasOwnProperty(p)) {
			   strData.push(encodeURIComponent(p) + "=" + encodeURIComponent(post_data[p]));
		   }
		}
		var strDataSend = strData.join("&");
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				console.log("Saving score for " + gname);
				setTimeout(function(){ parent.window.location = siteUrl.split('#')[0]; }, 3000);
			}
		};
		xhttp.open("POST", newUrl, true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send(strDataSend);
	}

	return false;
}