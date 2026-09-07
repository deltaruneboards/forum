// Version 1.94; personalizedBBC.js
function pbbc_containers(container)
{
	var conts = document.getElementsByTagName(container);
    for(i=0; i < conts.length; i++){
        conts[i].id = conts[i].id || "pbbc_" + container + i;
		conts[i].name = conts[i].name || "pbbc_" + container + i;
    }
}

function pbbcHttpRequest(objectx)
{
	var youtube_idx = objectx.id;
	var youtube_url = objectx.innerHTML || false;
	if (!youtube_url)
		return '';

	var youtube_style = objectx;
	if (objectx.display && objectx.display == 'none')
	{
		youtube_style.width = '560px';
		youtube_style.height = '350px';
		youtube_style.border = '0px';
		youtube_style.display = 'block';
	}

	var youtube_index = pbbc_strpos(youtube_url, 'v=') !== false ? youtube_url.indexOf("v=") + 2 : youtube_url;
	var youtube_id = youtube_url.substr(youtube_index, youtube_url.length);
	var youtube_newurl = "//www.youtube.com/embed/" + youtube_id;
	var youtube_elem = document.createElement('iframe');
	youtube_elem.id = 'youtube_elem';
	objectx.parentNode.insertBefore(youtube_elem, objectx);
	youtube_elem.src = youtube_newurl;
	youtube_elem.style.width = youtube_style.style.width || '560px';
	youtube_elem.style.height = youtube_style.style.height || '350px';
	youtube_elem.style.border = youtube_style.style.border || '0px';
	youtube_elem.style.display = youtube_style.style.display || 'block';
	youtube_elem.style.allowFullscreen = '1';
}

function pbbc_strpos(haystack, needle, offset)
{
	var i = (haystack + '').indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
}

function addPbbcEvent(evnt, elem, func)
{
	if (elem.attachEvent)
      elem.attachEvent("on"+evnt, func);
	else
      elem.addEventListener(evnt, func, false);
}