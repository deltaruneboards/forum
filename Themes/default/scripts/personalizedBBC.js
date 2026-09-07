// Version 1.94; personalizedBBC.js

function pbbc_hide(target) {
	document.getElementById(target).style.display = 'none';
	document.getElementById(target).style.position = 'relative';
	document.getElementById(target).style.overflow = 'hidden';
}

function pbbc_show(target) {
	document.getElementById(target).style.display = 'block';
	document.getElementById(target).style.position = 'relative';
	document.getElementById(target).style.border = '1px solid';
	document.getElementById(target).style.overflow = 'hidden';
	document.getElementById(target).style.paddings = '2px';
}

function pbbc_test(type_value) {
	if (type_value == 0)
	{
		document.getElementById('personalizedBBC_test_content').style.display = 'inline';
		document.getElementById('personalizedBBC_test_option1').style.display = 'none';
		document.getElementById('personalizedBBC_test_option2').style.display = 'none';
		document.getElementById('personalizedBBC_test_option_num').style.display = 'none';
		document.getElementById('personalizedBBC_test_option_num1').style.display = 'none';
	}
	else if (type_value == 1)
	{
		document.getElementById('personalizedBBC_test_content').style.display = 'inline';
		document.getElementById('personalizedBBC_test_option1').style.display = 'inline';
		document.getElementById('personalizedBBC_test_option2').style.display = 'none';
		document.getElementById('personalizedBBC_test_option_num').style.display = 'inline';
		document.getElementById('personalizedBBC_test_option_num1').style.display = 'none';
	}
	else if (type_value == 2)
	{
		document.getElementById('personalizedBBC_test_content').style.display = 'inline';
		document.getElementById('personalizedBBC_test_option1').style.display = 'inline';
		document.getElementById('personalizedBBC_test_option2').style.display = 'inline';
		document.getElementById('personalizedBBC_test_option_num').style.display = 'none';
		document.getElementById('personalizedBBC_test_option_num1').style.display = 'inline';
	}
	else
	{
		document.getElementById('personalizedBBC_test_content').style.display = 'none';
		document.getElementById('personalizedBBC_test_option1').style.display = 'none';
		document.getElementById('personalizedBBC_test_option2').style.display = 'none';
		document.getElementById('personalizedBBC_test_option_num').style.display = 'none';
		document.getElementById('personalizedBBC_test_option_num1').style.display = 'none';
	}
}