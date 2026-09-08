/**
 * @package Curve2 Color Changer
 * @author SMF Customization Team (www.simplemachines.org)
 * @license BSD 3-clause
 * @version 1.0
 */
window.addEventListener("DOMContentLoaded", function()
{
	// Do we have any palettes to output ?
	if(txt_cc_palettes)
	{
		document.getElementById('cc_reset_all').parentNode.parentNode.parentNode.innerHTML += '<div class="clear windowbg">' + txt_cc_palettes + ':<div id="color_palettes"></div></div>';
		var oColorPalettes = document.getElementById('color_palettes');
		var cpContent = '';

		for(var palette in color_palettes)
		{
			cpContent += '<fieldset class="cc_palette windowbg" onclick="return applyColorPalette(\''+palette+'\')"><legend>&nbsp;' + palette + '&nbsp;</legend>';
			for(color in color_palettes[palette])
			{
				if(typeof color_palettes[palette][color] === "boolean")
					continue;

				cpContent += '<span class="cc_p_color' + (!color_palettes[palette][color] ? ' cc_empty_color' : '') + '" style="background: ' + (!color_palettes[palette][color] ? 'transparent' : color_palettes[palette][color]) + ';"></span>'
			}
			cpContent += '</fieldset>';
		}

		oColorPalettes.innerHTML = cpContent;
	}
});

function applyColorPalette(oPalette)
{
	var oCcInputs = document.querySelectorAll('input[name^="options[cc_"]');
	var oColorEquiv = '';
	for(var input in oCcInputs)
	{
		if(!oCcInputs[input].id)
			continue;

		oColorEquiv = color_palettes[oPalette][oCcInputs[input].id.replace('options_', '').replace('cc_', '')];

		if(!oColorEquiv && oCcInputs[input].type != 'checkbox')
		{
			oCcInputs[input].type = 'text';
			oCcInputs[input].value = '';
		}
		else if(oCcInputs[input].type == 'checkbox')
		{
			oCcInputs[input].checked = oColorEquiv ? 'checked' : '';
		}
		else
		{
			if(oColorEquiv.length <= 7)
				oCcInputs[input].type = 'color';
			else
				oCcInputs[input].type = 'text';

			oCcInputs[input].value = oColorEquiv;
		}
	}
}

// TODO?
function copyPalette()
{
	var oCcInputs = document.querySelectorAll('input[id^="options_cc_"][type="text"], input[id^="options_cc_"][type="color"]');
	var oPalette = {};
	var oColor;
	for(var input in oCcInputs)
	{
		if(!oCcInputs[input].id)
			continue;

		oColor = oCcInputs[input].id.replace('options_cc_', '');
		oValue = oCcInputs[input].value;
		oPalette[oColor] = oValue;
	}

	return oPalette;
}