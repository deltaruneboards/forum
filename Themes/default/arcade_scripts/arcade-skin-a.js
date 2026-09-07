/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
function empty()
{
    if (document.search && document.search.name.value == '')
    {
        alert('Nothing Entered To Search!');
        return false;
    }
}

function popup(path,w,h)
{
	arcadepopup=window.open(path,'name','height='+ h +',width='+ w +', left=200, top=200, resizable = 0');
	if (window.focus)
		arcadepopup.focus();
}
