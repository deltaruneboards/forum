/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
function arcadeInfoScroll(divId, divClass, content)
{
	return '<div id="'+divId+'" class="'+divClass+'" style="position: relative; overflow: hidden;"><div class="innerDiv" style="position: absolute; width: 100%" id="'+divId+'1">'+content[0]+'</div><div class="innerDiv" style="position: absolute; width: 100%; visibility: hidden" id="'+divId+'2">'+content[1]+'</div></div>';
}

function arcadeIe4(fwidth, fheight)
{
		return '<div style="border:0px solid black;width:'+fwidth+';height:'+fheight+'" id="fscroller"></div>';
}

function arcadeNewsFader(items)
{
	var forcontent = new Array();
	for(i=0; i<items; i++)
	{
		forcontent[i] = getElementById("arcadeNews" + i).innerHTML;
	}
	return forcontent;
}

function arcadeNewsDiv()
{
	var arcadeNewsDiv = new Array();
	arcadeNewsDiv[0] = '<div style="padding: 5px;text-align: center;">';
	arcadeNewsDiv[1] = '</div>';
	return arcadeNewsDiv;
}