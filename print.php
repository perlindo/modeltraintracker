<?php
	require_once('auth.php');
	
	require_once('config.php');
	//Connect to mysql server
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if(!$link) {
		die('Failed to connect to server: ' . mysql_error());
	}
	
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db) {
		die("Unable to select database");
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<title>Model Train Inventory</title>
<head>
<link type="text/css" href="css/layout.css" rel="stylesheet" />
<link type="text/css" href="css/prr/jquery-ui-1.8.14.custom.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.validate.min.js"></script>

<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<script type="text/javascript"> 
$(function(){
	$('a').button().width(110);		

	jQuery.validator.messages.required = '';
	$("#printoptions").validate({
			rules: {
				scale: { required: function(element) {
										return !$("#manufacturer").val().length && !$("#roadname").val().length && !$("#type").val().length;
								}},
				manufacturer: {	required: function(element) {
										return !$("#scale").val().length && !$("#roadname").val().length && !$("#type").val().length;
								}},
				type: { required: function(element) {
										return !$("#manufacturer").val().length && !$("#roadname").val().length && !$("#scale").val().length;
								}},
				roadname: { required: function(element) {
										return !$("#manufacturer").val().length && !$("#scale").val().length && !$("#type").val().length;
								}}
			},
			onkeyup: false,
			onclick: false,
			messages: { },
			invalidHandler: function(e, validator) {
				var errors = validator.numberOfInvalids();
				if (errors) {
					var message = 'You must select at least one of the highlighted options.';
					$("div.error span").html(message);
					$("div.error").show();
				} else {
					$("div.error").hide();
				}
			}
	});
	$('#printbutton').button();	
});
</script>
</head>
<body>
<?php 
	include('menu.php'); 
?>
	<div id="content">
		<div class="error" style="display:none;">
			<img src="css/images/important.gif" alt="Warning!" width="24" height="24" style="float:left; margin: -5px 10px 0px 0px; " />
			<span></span>.<br clear="all" />
		</div>
		<form id="printoptions" action="inventory.php" method="post">
			<fieldset class="ui-widget ui-widget-content ui-corner-all">
			<legend class="ui-widget ui-widget-header ui-corner-all">Filters</legend>
				<table>
					<tr>
					<td><label for="complete">Complete inventory</label></td>
					<td><input id="complete" name="complete" type="checkbox" onclick="document.getElementById('manufacturer').disabled=(this.checked)?1:0;document.getElementById('manufacturer').value='';document.getElementById('roadname').disabled=(this.checked)?1:0;document.getElementById('roadname').value='';document.getElementById('type').disabled=(this.checked)?1:0;document.getElementById('type').value='';document.getElementById('scale').disabled=(this.checked)?1:0;document.getElementById('scale').value=''" value="1"/></td>
					</tr>
					<tr>
					<td><label for="scale">Scale:</label></td>
					<td><select name="scale" id="scale">
						<option value="" />
						<?php
							$s_result = mysql_query('SELECT s_id, s_scale FROM scale ORDER BY s_scale');
							while($s_row = mysql_fetch_row($s_result)) {
								echo "<option value=\"$s_row[0]/$s_row[1]\" />";
								echo $s_row[1]; 
							}
						?>
					</select></td>
					</tr>
					<tr>
					<td><label for="manufacturer">Manufacturer:</label></td>
					<td><select name="manufacturer" id="manufacturer">
						<option value="" />
						<?php
							$m_result = mysql_query('SELECT m_index, m_name FROM manufacturer ORDER BY m_name');
							while($m_row = mysql_fetch_row($m_result)) {
								echo "<option value=\"$m_row[0]/$m_row[1]\" />";
								echo $m_row[1]; 
							}
						?>
					</select></td>
					</tr>
					<tr>
					<td><label for="roadname">Roadname:</label></td>
					<td><select name="roadname" id="roadname">
						<option value="" />
						<?php
							$r_result = mysql_query('SELECT r_index, r_roadname FROM roadnames ORDER BY r_roadname');
							while($r_row = mysql_fetch_row($r_result)) {
								echo "<option value=\"$r_row[0]/$r_row[1]\" />";
								echo $r_row[1]; 
							}
						?>				
					</select></td>
					</tr>
					<tr>
					<td><label for="type">Type:</label></td>
					<td><select name="type" id="type">
						<option value="" />
							<?php
								$t_result = mysql_query('SELECT t_index, t_type FROM type ORDER BY t_type');
								while($t_row = mysql_fetch_row($t_result)) {
									echo "<option value=\"$t_row[0]/$t_row[1]\" />";
									echo $t_row[1]; 
								}
							?>				
					</select></td></tr>
				</table>
			</fieldset>
			<br />
			<fieldset class="ui-widget ui-widget-content ui-corner-all">
			<legend class="ui-widget ui-widget-header ui-corner-all">Columns to print</legend>
				<label for="values">Include values</label>
				<input id="values" name="values" type="checkbox"  value="1" />
			</fieldset>	
				<br />
				<input id="printbutton" type="submit" name="print" value="Print Report" />
			</form>
	</div>
<?php include('footer.php'); ?>
</div>	
</body>
</html>	