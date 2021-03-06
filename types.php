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

	$query = "SELECT * FROM type t ORDER BY t.t_type";
	$res = mysql_query($query);
	$data = array();
	while($row = mysql_fetch_row($res)) {
		$data[]=$row;
	}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Model Train Inventory</title>
<link type="text/css" href="css/layout.css" rel="stylesheet" />
<link type="text/css" href="css/prr/jquery-ui-1.8.14.custom.css" rel="stylesheet" />	
<link type="text/css" href="css/jquery.alerts.css" rel="stylesheet" />	

<script type="text/javascript" src="js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/jquery.jeditable.mini.js"></script>
<script type="text/javascript" src="js/jquery.alerts.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.editable.js"></script>
<script type="text/javascript">  
	$(function(){
		$('input:submit,button').button();
		oTable = $('#type_table').dataTable({
			"bFilter": false,
			"bJQueryUI": true,
			"iDisplayLength" : 25,
			"sDom": '<"H"lfr>t<"F"ip>',
			"bSort": true,
			"bProcessing": true,
			"bAutoWidth": false,
			"oLanguage": {
				"sInfo": "_START_ to _END_ of _TOTAL_",
			},
			"aoColumns": [
			{ "bSearchable": false, "bSortable": false }
			]			
		}).makeEditable({
			sAddURL: "add.php",
			sSelectedRowClass: "",
			oAddNewRowButtonOptions: {	label: "Add new type",
										icons: {primary:'ui-icon-plus'}},
			oAddNewRowFormOptions: {	title: 'Add new type',
										width: 500,
										modal: true},
			oAddNewRowCancelButtonOptions: { 
										name: "action",
										value: "cancel-add",
										icons: { primary: 'ui-icon-close' }},
			oAddNewRowOkButtonOptions: {
										icons: {primary:'ui-icon-check'},
										name:"action",
										value:"add-new"},		
			fnShowError: function (message, action) {
				switch (action) {
					case "update":
						jAlert(message, "Update failed");
						break;
					case "delete":
						jAlert(message, "Delete failed");
						break;
					case "add":
						break;
				}
			},
			fnStartProcessingMode: function () {
				$("#processing_message").dialog();
			},
			fnEndProcessingMode: function () {
				$("#processing_message").dialog("close");
			}
		});		
		jQuery.validator.messages.required = "";		
		$("#formAddNewRow").validate({
				rules: {
					type: "required"
				},
				onkeyup: false,
				onclick: false,
				messages: { },
				invalidHandler: function(e, validator) {
					var errors = validator.numberOfInvalids();
					if (errors) {
						var message = errors == 1
							? 'You missed 1 field. It has been highlighted below'
							: 'You missed ' + errors + ' fields.  They have been highlighted below';
						$("div.error span").html(message);
						$("div.error").show();
					} else {
						$("div.error").hide();
					}
				}	
		});		
});
</script>
<style type="text/css">
	.css_right {float:right}
</style>
</head>
<body>
<?php
include('menu.php');
?>
	<div id="content">
		<button id="btnAddNewRow">Add</button><br /><br />
		<div id="processing_message" style="display:none" title="Processing">Please wait while your request is being processed...</div>
		<form id="formAddNewRow" action="#">
			<br />		
			<div class="error" style="display:none;">
			  <img src="css/images/important.gif" alt="Warning!" width="24" height="24" style="float:left; margin: -5px 10px 0px 0px; " />
			  <span></span>.<br clear="all" />
			</div>			
			<label for="type">Type:</label>&nbsp;&nbsp;
			<input name="type" id="type" type="text" value="" size="15" maxlength="50" />
			<br />
			<input type="hidden" name="ref" value="type" />
		</form>	
		<div id="table_box">
		<table class="list datatable" id="type_table">
			<thead>
				<tr>
					<th>Type</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$fill = 0;
				foreach($data as $temp_row) {
					if($fill) {
						$tr = 'tr class="odd"';
					} else {
						$tr = 'tr class="even"';
					}
					echo '<'.$tr.' id="'.$temp_row[0].'"><td class="read_only">'.$temp_row[1].'</td></tr>';
					$fill = !$fill; 
				}
			?>
			</tbody>					
		</table>
		</div>
	</div>
<?php include('footer.php'); ?>
</body>
</html>		