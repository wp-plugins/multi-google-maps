/**
 * Plugin Name: Multi Google Maps
 * Plugin URI : http://wordpress.org/extend/plugins/multi-google-maps/
 * Description: This plugin supports to insert Multi Google Map V.3 Objects into your post.
 * Version    : 0.4.3
 * Author     : Siripol Noikajana
 * Author URI : http://wordpress.org/extend/plugins/multi-google-maps/
 * License    : GPL2
 */

function removeMap(id)
{
	obj		  = document.getElementById(id);
	parentObj = obj.parentNode;
	parentObj.removeChild(obj); 
	
	if(document.getElementsByName('gmpObj').length < 1)
	{
		parentObj.innerHTML = ''+
			'<div name="gmpObj" id="gmpObj_1"> ' +
			'	<table> ' +
			'		<tr style="vertical-align: top;"> ' +
			'			<td style="width: 100px;"> ' +
			'				Name ' +
			'			</td> ' +
			'			<td style="width: 30px;">:</td> ' +
			'			<td> ' +
			'				<input type="text" style="width: 400px;" value="" name="gmp_marker_data_1" id="gmp_marker_data_1" sourceindex="628">  ' +
			'			</td> ' +
			'		</tr> ' +
			'		<tr style="vertical-align: top;"> ' +
			'			<td> ' +
			'				Description ' +
			'			</td> ' +
			'			<td>:</td> ' +
			'			<td> ' +
			'				<textarea style="width: 400px;" name="gmp_description_data_1" id="gmp_description_data_1" sourceindex="633"></textarea> ' +
			'			</td> ' +
			'		</tr> ' +
			'		<tr style="vertical-align: top;"> ' +
			'			<td> ' +
			'				Address ' +
			'			</td> ' +
			'			<td>:</td> ' +
			'			<td> ' +
			'				<textarea style="width: 400px; height: 100px;" name="gmp_address_data_1" id="gmp_address_data_1" sourceindex="638"></textarea> ' +
			'			</td> ' +
			'		</tr> ' +
			'		<tr style="vertical-align: top;"> ' +
			'			<td> ' +
			'				Width ' +
			'			</td> ' +
			'			<td>:</td> ' +
			'			<td> ' +
			'				<input type="text" style="width: 400px;" value="" name="gmp_width_data_1" id="gmp_width_data_1" sourceindex="628">  ' +
			'			</td> ' +
			'		</tr> ';
			'		<tr style="vertical-align: top;"> ' +
			'			<td> ' +
			'				Height ' +
			'			</td> ' +
			'			<td>:</td> ' +
			'			<td> ' +
			'				<input type="text" style="width: 400px;" name="gmp_height_1" id="gmp_height_1" sourceindex="633">' +
			'			</td> ' +
			'		</tr> ';
			'		</table> ' +
			'  <div style="text-align: right;"> '+
            '    <input type="button" value="Add this Map into Post" onclick="send_to_editor(&quot;[GMP-Map]&quot;);" sourceindex="641"> '+
            '    <input type="button" value="Delete this Map" onclick="removeMap(\'gmpObj_1\');" sourceindex="642"> '+
            '  </div> '+
			'</div> ';
	}
}

function addNewMap()
{
	objs	    = document.getElementsByName('gmpObj');
	obj         = objs[objs.length - 1];
	parentObj   = obj.parentNode;
	newObj      = document.createElement('div');
	newObj.name = 'gmpObj'

	newItemID   = Math.floor(Math.random()*1000)

	while(document.getElementById('gmp_marker_' + newItemID))
	{
		newItemID = Math.floor(Math.random()*1000);
	}

	newObj.id   = 'gmpObj_' + newItemID;
	newObj.setAttribute('name', 'gmpObj');
	parentObj.insertBefore(newObj, obj.nextSibling);

	newObj.innerHTML = ''+
		'	<table> ' +
		'		<tr style="vertical-align: top;"> ' +
		'			<td style="width: 100px;"> ' +
		'				Name ' +
		'			</td> ' +
		'			<td style="width: 30px;">:</td> ' +
		'			<td> ' +
		'				<input type="text" style="width: 400px;" value="" name="gmp_marker_data_' + newItemID + '" id="gmp_marker_data_' + newItemID + '" sourceindex="628">  ' +
		'			</td> ' +
		'		</tr> ' +
		'		<tr style="vertical-align: top;"> ' +
		'			<td> ' +
		'				Description ' +
		'			</td> ' +
		'			<td>:</td> ' +
		'			<td> ' +
		'				<textarea style="width: 400px;" name="gmp_description_data_' + newItemID + '" id="gmp_description_data_' + newItemID + '" sourceindex="633"></textarea> ' +
		'			</td> ' +
		'		</tr> ' +
		'		<tr style="vertical-align: top;"> ' +
		'			<td> ' +
		'				Address ' +
		'			</td> ' +
		'			<td>:</td> ' +
		'			<td> ' +
		'				<textarea style="width: 400px; height: 100px;" name="gmp_address_data_' + newItemID + '" id="gmp_address_data_' + newItemID + '" sourceindex="638"></textarea> ' +
		'			</td> ' +
		'		</tr> ' + 
		'		<tr style="vertical-align: top;"> ' +
		'			<td> ' +
		'				Width ' +
		'			</td> ' +
		'			<td>:</td> ' +
		'			<td> ' +
		'				<input type="text" style="width: 400px;" value="" name="gmp_width_data_' + newItemID + '" id="gmp_width_data_' + newItemID + '" sourceindex="628">  ' +
		'			</td> ' +
		'		</tr> '  +
		'		<tr style="vertical-align: top;"> ' +
		'			<td> ' +
		'				Height ' +
		'			</td> ' +
		'			<td>:</td> ' +
		'			<td> ' +
		'				<input type="text" style="width: 400px;" name="gmp_height_' + newItemID + '" id="gmp_height_' + newItemID + '" sourceindex="633">' +
		'			</td> ' +
		'		</tr> ' +
		'  </table> ' +
		'  <div style="text-align: right;"> '+
		'    <input type="button" value="Add this Map into Post" onclick="send_to_editor(&quot;[GMP-Map]&quot;);" sourceindex="641"> '+
		'    <input type="button" value="Delete this Map" onclick="removeMap(\'gmpObj_' + newItemID + '\');" sourceindex="642"> '+
		'  </div> ';
}