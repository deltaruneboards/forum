/*
 * SMF Arcade
 *
 * @package SMF Arcade
 * @version 2.7
 * @license https://web-develop.ca/index.php?page=arcade_license_BSD2 BSD 2
 */
function secondsToTime(secs) { // we will use this function to convert seconds in normal time format
    var hr = Math.floor(secs / 3600);
    var min = Math.floor((secs - (hr * 3600))/60);
    var sec = Math.floor(secs - (hr * 3600) -  (min * 60));

    if (hr < 10) {hr = "0" + hr; }
    if (min < 10) {min = "0" + min;}
    if (sec < 10) {sec = "0" + sec;}
    if (hr) {hr = "00";}
    return hr + ':' + min + ':' + sec;
};

function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
};

function fileSelected() {

    // hide different warnings
    document.getElementById('upload_response').style.display = 'none';
    document.getElementById('error').style.display = 'none';
    document.getElementById('error2').style.display = 'none';
    document.getElementById('abort').style.display = 'none';
    document.getElementById('warnsize').style.display = 'none';

    // filter for zip/tar/gz/rar files
    var oFile = document.getElementById('upload_file').files[0];
	var fileName = oFile.name.toLowerCase();
	var zFile = document.getElementById('upload_file').files;
	var zFileType = [];
	for(i=0; i<zFile.length; i++)
	{
		// some browsers ignore the input accept attribute therefore this may be necessary...
		zFileType[i] = [zFile[i].name.substr(zFile[i].name.length - 4), zFile[i].name.substr(zFile[i].name.length - 3)];
		if (zFileType[i][0] != '.zip' && zFileType[i][0] != '.tar' && zFileType[i][1] != '.gz' && zFileType[i][0] != '.rar' && zFileType[i][0] != '.ZIP' && zFileType[i][0] != '.TAR' && zFileType[i][1] != '.GZ' && zFileType[i][0] != '.RAR') {
				console.log(uploadError1);
				document.getElementById('upload_file').value = '';
				document.getElementById('error').style.display = 'block';
				return;
		}

		 // little test for filesize
		if (zFile[i].size > iMaxFilesize) {
			document.getElementById('warnsize').style.display = 'block';
			return;
		}
	}
}

function startUploading(uploadUrl) {
    // cleanup all temp states
    iPreviousBytesLoaded = 0;
    document.getElementById('upload_response').style.display = 'none';
    document.getElementById('error').style.display = 'none';
    document.getElementById('error2').style.display = 'none';
    document.getElementById('abort').style.display = 'none';
    document.getElementById('warnsize').style.display = 'none';
    document.getElementById('progress_percent').innerHTML = '';
    var oProgress = document.getElementById('progress');
    oProgress.style.display = 'block';
    oProgress.style.width = '0px';

	var vFD = new FormData(document.getElementById('upload_form'));
	var oXHR = new XMLHttpRequest();
	oXHR.upload.addEventListener('progress', uploadProgress, false);
	oXHR.addEventListener('load', uploadFinish, false);
	oXHR.addEventListener('error', uploadError, false);
	oXHR.addEventListener('abort', uploadAbort, false);
	oXHR.open('POST', uploadUrl);
	oXHR.responseType = "blob";
	oXHR.send(vFD);

	// set inner timer
	oTimer = setInterval(doInnerUpdates, 300);
}

function analyze_data(blob)
{
    var myReader = new FileReader();
    myReader.readAsArrayBuffer(blob);

    myReader.addEventListener("loadend", function(e)
    {
        var buffer = e.srcElement.result;
    });
}

function doInnerUpdates() { // we will use this function to display upload speed
    var iCB = iBytesUploaded;
    var iDiff = iCB - iPreviousBytesLoaded;

    // if nothing new loaded - exit
    if (iDiff == 0)
        return;

    iPreviousBytesLoaded = iCB;
    iDiff = iDiff * 2;
    var iBytesRem = iBytesTotal - iPreviousBytesLoaded;
    var secondsRemaining = iBytesRem / iDiff;

    // update speed info
    var iSpeed = iDiff.toString() + 'B/s';
    if (iDiff > 1024 * 1024) {
        iSpeed = (Math.round(iDiff * 100/(1024*1024))/100).toString() + 'MB/s';
    } else if (iDiff > 1024) {
        iSpeed =  (Math.round(iDiff * 100/1024)/100).toString() + 'KB/s';
    }

    document.getElementById('speed').innerHTML = iSpeed;
    document.getElementById('remaining').innerHTML = '| ' + secondsToTime(secondsRemaining);
}

function uploadProgress(e) { // upload process in progress
    if (e.lengthComputable) {
        iBytesUploaded = e.loaded;
        iBytesTotal = e.total;
        var iPercentComplete = Math.round(e.loaded * 100 / e.total);
        var iBytesTransfered = bytesToSize(iBytesUploaded);

        document.getElementById('progress_percent').innerHTML = iPercentComplete.toString() + '%';
        document.getElementById('progress').style.width = (iPercentComplete).toString() + '%';
        document.getElementById('b_transfered').innerHTML = iBytesTransfered;
        if (iPercentComplete == 100) {
            var oUploadResponse = document.getElementById('upload_response');
			var zFile = document.getElementById('upload_file').files;
			var zFileNames;
			for(i=0; i<zFile.length; i++)
				zFileNames = i != 0 ? zFileNames + ', ' + zFile[i].name : zFile[i].name;

            oUploadResponse.innerHTML = '<h1>' + uploadMessage1.replace("%s", zFileNames.toLowerCase()) + '</h1>';
            oUploadResponse.style.display = 'block';
        }
    } else {
        document.getElementById('progress').innerHTML = uploadMessage2;
    }
}

function uploadFinish(e) { // upload successfully finished
    var oUploadResponse = document.getElementById('upload_response');
    oUploadResponse.innerHTML = e.target.responseText;
    oUploadResponse.style.display = 'block';

    document.getElementById('progress_percent').innerHTML = '100%';
    document.getElementById('progress').style.width = '100%';
    document.getElementById('filesize').innerHTML = sResultFileSize;
    document.getElementById('remaining').innerHTML = '| 00:00:00';

    clearInterval(oTimer);
}

function uploadError(e) { // upload error
    document.getElementById('error2').style.display = 'block';
    clearInterval(oTimer);
}

function uploadAbort(e) { // upload abort
    document.getElementById('abort').style.display = 'block';
    clearInterval(oTimer);
}