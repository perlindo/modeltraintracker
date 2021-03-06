<?php
/*
TO DO
1. Duplicate checking
*/
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
<title>Model Train Inventory</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<link type="text/css" href="css/layout.css" rel="stylesheet" />
<link type="text/css" href="css/jquery-ui-1.8.13.custom.css" rel="stylesheet" />	
<link type="text/css" href="css/jquery.alerts.css" rel="stylesheet" />	

<script type="text/javascript" src="js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.13.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/jquery.jeditable.mini.js"></script>
<script type="text/javascript" src="js/jquery.alerts.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.editable.js"></script>

<script type="text/javascript"> 
	function fnFormatDetails ( oTable, nTr ) {
		var aData = oTable.fnGetData( nTr );
		if(aData[6] !== 'unk') {
			var pn = aData[6].replace(/^[ 0]/g,'');
		} else {
			var pn = '';
		}
		var sOut = '<div class="innerDetails">'+
						'<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;"  id="innerDetails">'+
							'<tr><td>Scale:</td><td>'+aData[8]+'</td></tr>'+
							'<tr><td>Manufacturer:</td><td>'+aData[1]+'</td></tr>'+
							'<tr><td>Roadname & number:</td><td>'+aData[2]+' '+ aData[3]+'</td></tr>'+
							'<tr><td>Part number:</td><td>'+aData[6]+'</td></tr>'+
							'<tr><td>Description:</td><td>'+aData[5]+'</td></tr>'+
							'<tr><td>Est. Value:</td><td> $'+aData[7]+'</td></tr>'+
							//'<tr><td>Walthers Info:</td><td><a href="http://www.walthers.com/exec/search?category=&scale=&manu='+aData[1]+'&item='+pn+'&keywords=&words=restrict&instock=Q&showdisc=Y&split=30&Submit=Search" target="new">Click here</a></td></tr>'+
						'</table>'+
					'</div>';
		return sOut;
	}

	var oTable, oSettings;
	
	$(function(){
		var clickedRowId, oldRow, edit_errors, nTr, addMRVar;
		var anOpen = [];
		var sImageUrl = "css/images/";
		var sSource = "edit.php";
		var member_id = '<?= $_SESSION['SESS_MEMBER_ID'] ?>';
		
		$('input:submit,button').button(); 
				
		oTable = $('#item_table').dataTable({
			"bFilter": true,
			"bJQueryUI": true,
			"iDisplayLength": 25,
			"sPaginationType": "full_numbers",
			"sDom": '<"H"lfr>t<"F"ip>',
			"bSort": true,
			"bProcessing": true,
			"bServerSide":true,
			"sAjaxSource": "edit.php",
			"fnServerData": function ( sSource, aoData, fnCallback ) {
						aoData.push( { "name": "member_id", "value": member_id } );
						aoData.push( { "name": "ref", "value": "get_all" } );
						$.ajax( {
							"dataType": 'json', 
							"type": "POST", 
							"url": sSource, 
							"data": aoData, 
							"success": fnCallback
						} );
					},
			"bAutoWidth": false,
			"oLanguage": {
				"sSearch": "Search:" 
			},
			"aoColumns": [
			{ "bSortable": false, "sClass": "control center read_only", "mDataProp": null, "sDefaultContent":'<img src="'+sImageUrl+'details_open.png'+'">' },
            { "bSearchable": true, "bSortable": true, "sClass": "read_only" },
			{ "bSearchable": true, "bSortable": true, "sClass": "read_only" },
			{ "bSearchable": true, "bSortable": false, "sClass": "read_only" },
			{ "bSearchable": true, "bSortable": true, "sClass": "read_only" },
			{ "bSearchable": true, "bSortable": false, "sClass": "read_only" },
			{ "bSearchable": false, "bSortable": false, "bVisible":false, "sClass": "read_only" },
			{ "bSearchable": false, "bSortable": false, "bVisible":false, "sClass": "read_only" },
			{ "bSearchable": false, "bSortable": false, "bVisible":false, "sClass": "read_only" },
			]			
		}).makeEditable({
		   	sDeleteURL: "delete.php",
			oDeleteParameters: {  ref: "items" },
			sAddURL: "add.php",
			oAddNewRowButtonOptions: {	label: "Add new item",
										icons: {primary:'ui-icon-plus'}},
			oDeleteRowButtonOptions: {	label: "Remove selected item", 
										icons: {primary:'ui-icon-trash'}},
			oAddNewRowFormOptions: {	title: 'Add a new item',
										width: 500,
										modal: true,
										open: function() {
											$(this).load('form.php');
										}},
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
			},
			fnOnDeleting: function (tr, id, fnDeleteRow) {
				jConfirm('Please confirm that you want to delete this item entry?', 'Confirm Delete', function (r) {
					if (r) {
						fnDeleteRow(id);
						clickedRowId = 0;
						$('#btnEditRow').button( "option", "disabled", true );						
					}
				});
				return false;
			},
			fnOnAdded: function(status)	{ 	
					oTable.fnDraw();
				}			
		}).fnFilterOnReturn();	
		
		$('#innerDetails').live('click',function() {
			return false;
		});
		
       $('#item_table td.control').live( 'click', function () {
			oldRow = nTr;
			if(oldRow) {
				$(oldRow).removeClass('row_selected'); 
				$('div.innerDetails', oldRow[0]).slideUp( function () {
						oldRow.childNodes[0].innerHTML = '<img src="'+sImageUrl+'details_open.png">';
						oTable.fnClose( oldRow );
						anOpen.shift();
				});
			}
			nTr = this.parentNode;
			var i = $.inArray( nTr, anOpen );
			
			if(nTr !== oldRow || i === -1) {
				$('img', this).attr( 'src', sImageUrl+"details_close.png" );
				var nDetailsRow = oTable.fnOpen( nTr, fnFormatDetails(oTable, nTr), 'details' );
				$('div.innerDetails', nDetailsRow).slideDown();
				$(nTr).addClass('row_selected');
				anOpen.push( nTr );
				clickedRowId = $(nTr).attr('id');
				$('#btnEditRow').button( "option", "disabled", false );
			} else {
				$('img', this).attr( 'src', sImageUrl+"details_open.png" );
				$('div.innerDetails', $(nTr).next()[0]).slideUp( function () {
					$(nTr).removeClass('row_selected');
					clickedRowId = 0;
					anOpen.shift();
					oTable.fnClose( nTr );
					$('#btnEditRow').button( "option", "disabled", true );
				  } );
			}
			return false;
		} );	
		
		$("#item_table tr.even,tr.odd").live('click',function () {
			if ($(this).hasClass("row_selected") && $(this).attr('id') !== clickedRowId) {
				$('#btnEditRow').button( "option", "disabled", false );
				clickedRowId = $(this).attr('id');
				$(anOpen).each( function() {
					var tempRow = this;
					$(tempRow).removeClass('row_selected'); 
					$('div.innerDetails', tempRow[0]).slideUp( function () {
							tempRow.childNodes[0].innerHTML = '<img src="'+sImageUrl+'details_open.png">';
							oTable.fnClose( tempRow );
							anOpen.shift();
					});
				});
			} else if($(this).attr('id') === clickedRowId) {
				$('#btnEditRow').button( "option", "disabled", true );
				$(this).removeClass("row_selected");
				var tempRow = this;
				$('div.innerDetails', tempRow[0]).slideUp( function () {
					tempRow.childNodes[0].innerHTML = '<img src="'+sImageUrl+'details_open.png">';
					oTable.fnClose( tempRow );
					anOpen.shift();
				});				
				clickedRowId = 0;
			} else {
				$('#btnEditRow').button( "option", "disabled", true );
				clickedRowId = 0;
			}
			return false;
		});	
		
		$('#btnEditRow').button({icons: {primary:'ui-icon-pencil'},
						 disabled: true
						}).click(function() {
							var desc, scale, man, roadname, value, type, id, roadnumber, partnumber;
							$.ajax({
								type: "POST",
								dataType: "json",
								data: ({id : clickedRowId, ref : "items", action : "fetch" }),
								context: "#edit_form",
								url: "edit.php", 
								success: function(data) {
									desc = data.i_description;
									scale = data.i_scale;
									man = data.i_manufacturer;
									roadname = data.i_roadname;
									value = data.i_value;
									type = data.i_type;
									id = data.i_index;
									partnumber = data.i_partnumber;
									roadnumber = data.i_roadnumber;
								}
							});
							$("#edit_form").load('form.php', function() {
								$("#edit_form").dialog({
									title: "Edit item",
									width: 500,
									modal: true,
									open: function() {
										$("#description").val(desc);
										$("#scale").val(scale);
										$("#manufacturer").val(man);
										$("#value").val(value);
										$("#type").val(type);
										$("#item_id").val(id);
										$("#roadname").val(roadname);
										if(partnumber == 'unk') {
											$("#partnumbercbx").attr('checked','checked');
											$("#partnumber").val("");
											$("#partnumber").attr("disabled", true);
										} else {
											$("#partnumber").val(partnumber);
											$("#partnumber").attr("disabled", false);
										}
										if(roadnumber == 'n/a') {
											$("#roadnumbercbx").attr('checked','checked');
											$("#roadnumber").val("");
											$("#roadnumber").attr("disabled", true);
										} else {
											$("#roadnumber").val(roadnumber);
										}										
									},
									buttons: {
										"Edit Item": function() {
											if($("#edit_form").valid()) {
												$("#edit_form").submit();
												$( this ).dialog( "close" );
											}
										},
										Cancel: function() {
											$( this ).dialog( "close" );
										}
									},
									close: function() {
										$(':text',"#edit_form").val("");
										$("#partnumber").attr("disabled", false);
										$("#roadnumber").attr("disabled", false);
										$(':input',"#edit_form").removeAttr('checked').removeAttr('selected');
									}
								});
							});
						});		

		jQuery.validator.messages.required = "";
		$("#edit_form").validate({
						rules: {
							scale: "required",
							manufacturer: "required",
							roadname: "required",
							type: "required",
							description: "required",
							value: "required",
							partnumber: { required: "#partnumbercbx:unchecked" },
							roadnumber: { required: "#roadnumbercbx:unchecked" }
						},
						onkeyup: false,
						onclick: false,
						messages: { },
						invalidHandler: function(e, validator) {
							edit_errors = validator.numberOfInvalids();
							if (edit_errors) {
								var message = edit_errors == 1
									? 'You missed 1 field. It has been highlighted below'
									: 'You missed ' + edit_errors + ' fields.  They have been highlighted below';
								$("div.error span").html(message);
								$("div.error").show();
							} else {
								$("div.error").hide();
							}
						}, 
						submitHandler: function(form) {
											var partnumber_ed, roadnumber_ed;
											$("#roadnumbercbx").is(":checked") ? roadnumber_ed = "n/a" : roadnumber_ed = roadnumber.val();
											$("#partnumbercbx").is(":checked") ? partnumber_ed = "unk" : partnumber_ed = partnumber.val();
											$.ajax({
												type: "POST",
												url: "edit.php",
												context: document.body,
												data:({id : $("#item_id").val(),description : $("#description").val(),scale : $("#scale").val(), manufacturer: $("#manufacturer").val(), roadname:$("#roadname").val(), value : $("#value").val(), type : $("#type").val(), partnumber : partnumber_ed, roadnumber : roadnumber_ed, ref : "items", action : "update" }),
												success: function(){
													oTable.fnDraw();
												}
											});							
						}
		});	
				
		$("#formAddNewRow").validate({
				rules: {
					scale: "required",
					manufacturer: "required",
					roadname: "required",
					type: "required",
					description: "required",
					value: "required",
					partnumber: { required: "#partnumbercbx:unchecked" },
					roadnumber: { required: "#roadnumbercbx:unchecked" }
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
		 
		$('#btnSave').button({icons: {primary:'ui-icon-disk'},
						 disabled: false
						}).click(function() {
							oSettings = oTable.fnSettings();
							var searchData = [];
							var aCols = [];
							var iCols = oSettings.aoColumns.length;
							
							for (i=0; i< iCols; i++) {
							  aCols.push(oSettings.aoColumns[i].sName);
							  searchData.push( { "name": "bSearchable_"+i, "value": oSettings.aoColumns[i].bSearchable } );
							  searchData.push( { "name": "bSortable_"+i,  "value": oSettings.aoColumns[i].bSortable } );
							}
							var sCols = aCols.join(',');
							var sSrch = $('.dataTables_filter input').val();

							if ( oSettings.oFeatures.bSort !== false )
							{
								var iFixed = oSettings.aaSortingFixed !== null ? oSettings.aaSortingFixed.length : 0;
								var iUser = oSettings.aaSorting.length;
								searchData.push( { "name": "iSortingCols",   "value": iFixed+iUser } );
								for ( i=0 ; i<iFixed ; i++ ) {
									searchData.push( { "name": "iSortCol_"+i,  "value": oSettings.aaSortingFixed[i][0] } );
									searchData.push( { "name": "sSortDir_"+i,  "value": oSettings.aaSortingFixed[i][1] } );
								}
								
								for ( i=0 ; i<iUser ; i++ )	{
									searchData.push( { "name": "iSortCol_"+(i+iFixed),  "value": oSettings.aaSorting[i][0] } );
									searchData.push( { "name": "sSortDir_"+(i+iFixed),  "value": oSettings.aaSorting[i][1] } );
								}
							}							
							
							searchData.push({"name":"sColumns","value":sCols});
							searchData.push({"name":"iColumns","value":iCols});
							searchData.push({"name":"sSearch","value":sSrch});
							searchData.push({"name":"iDisplayStart","value":0});
							searchData.push({"name":"member_id","value":<?= $_SESSION['SESS_MEMBER_ID'] ?>});
							
							$.ajax({
							  url: "item_print.php",
							  type: "POST", 		  
							  data: searchData,
							  success: function(data, textStatus, jqXHR) {
								window.location = data;
								$.ajax({
									url: "delete.php",
									type: "POST",
									data: ({filename: $.trim(data), ref: "del_file" })
								});
							  }
							});						
		});	
		
		$('#addMRForm').validate({
			rules: { newentry: 'required' },
			onkeyup: false,
			onclick: false,
			messages: {},
			invalidHandler: function(e, validator) {
				var errors = validator.numberOfInvalids();
				if (errors) {
					var message = 'You must enter a value';
					$("div.addMRerror span").html(message);
					$("div.addMRerror").show();
				} else {
					$("div.addMRerror").hide();
				}
			},
			submitHandler: function(form) {
				if(addMRVar == "manufacturer") {
					var addMRData = {ref:"manufacturer", manufacturer:$("#newentry").val()};
				} else {
					var addMRData = {ref:"roadname", roadname:$("#newentry").val()};
				}
				$.ajax({
					type: "POST",
					url: "add.php",
					data: (addMRData),
					success: function(data) {
						if(addMRVar == "manufacturer") {
							$("#manufacturer").append('<option selected="selected" value='+data+'>'+$("#newentry").val()+'</option>');
							$("#manufacturer").val(data);
						} else {
							$("#roadname").append('<option selected="selected" value='+data+'>'+$("#newentry").val()+'</option>');
							$("#roadname").val(data);
						}
					}
				});
			}
		});
		
		$('#dialog-add-MR').dialog({
			autoOpen: false,
			width: 500,
			modal: true,
			buttons: {
				"Add": function() {
					if($("#addMRForm").valid()) {
						$("#addMRForm").submit();
						$( this ).dialog( "close" );
					}
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {

			}			
		});
		
		$("input@[name='selector']").change(function(){
			if ($("input[@name='selector']:checked").val() == 'manufacturer') {
				addMRVar = "manufacturer";
				$("#togglediv").html("New Manufacturer: ");
			} else if ($("input[@name='selector']:checked").val() == 'roadname') {
				addMRVar = "roadname";
				$("#togglediv").html("New Roadname: ");
			}
		});
		
		$('#partnumbercbx').live('click', function() {
			$("#partnumber").val("");
			$("#partnumber").attr("disabled", this.checked);
			$("#partnumber").is(":disabled") ? $("#partnumber").removeClass("error") : '';
		});
		$('#roadnumbercbx').live('click', function() {
			$("#roadnumber").attr("disabled", this.checked);
			$("#roadnumber").val("");
			$("#roadnumber").is(":disabled") ? $("#roadnumber").removeClass("error") : '';
		});	
		$('#addManPopup,#addRoadPopup').live('click', function() {
			$('#dialog-add-MR').dialog("open");
			return false;
		}).live('hover',function() {
			 $(this).css('cursor','pointer');
			 }, function() {
			 $(this).css('cursor','auto');
		});			
	});
	
	$.fn.dataTableExt.oApi.fnFilterOnReturn = function (oSettings) {
		/*
		* Usage:       $('#example').dataTable().fnFilterOnReturn();
		* Author:      Jon Ranes (www.mvccms.com)
		* License:     GPL v2 or BSD 3 point style
		* Contact:     jranes /AT\ mvccms.com
		*/
		var _that = this;

		this.each(function (i) {
			$.fn.dataTableExt.iApiIndex = i;
			var $this = this;
			var anControl = $('input', _that.fnSettings().aanFeatures.f);
			anControl.unbind('keyup').bind('keypress', function (e) {
				if (e.which == 13) {
					$.fn.dataTableExt.iApiIndex = i;
					_that.fnFilter(anControl.val());
				}
			});
			return this;
		});
		return this;
	}
</script>
<style type="text/css">
	.css_right {float:right}
	div.innerDetails { display: none }	
	div.addPopup { color: #0000ff; text-decoration: underline; }
</style>
</head>
<body>
<?php
include('menu.php');
?>
	<div id="content">
		<button id="btnAddNewRow">Add</button>&nbsp;<button id="btnEditRow">Edit Selected item</button>&nbsp;<button id="btnSave">Save current view</button><button id="btnDeleteRow" style="float:right">Delete</button><br /><br />
		<div id="processing_message" style="display:none" title="Processing">Please wait while your request is being processed...</div>
		<form id="formAddNewRow" action="#">
		</form>	
		<form id="edit_form" action="#">
		</form>		
		<div id="dialog-add-MR">
			<div class="addMRerror" style="display:none;">
			  <img src="css/images/important.gif" alt="Warning!" width="24" height="24" style="float:left; margin: -5px 10px 0px 0px; " />
			  <span></span>.<br clear="all" />
			</div>			
			<form id="addMRForm" action="#">
				Are you adding a 
				<input type="radio" name="selector" id="selector" value="manufacturer" />Manufacturer or <input type="radio" name="selector" id="selector" value="roadname" />Roadname? <br /><br />
				<div id="togglediv" style="display:inline;"></div> <input type="text" name="newentry" id="newentry" value="" size="30" maxlength="50" />
			</form>
		</div>		
		
		<table class="list datatable" id="item_table">
			<thead>
				<tr>
					<th></th>
					<th>Manufacturer</th>
					<th>Roadname</th>
					<th>Road No.</th>
					<th>Type</th>
					<th>Description</th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr>
			<td colspan="9" class="dataTables_empty">Loading data from server</td>
		</tr>
			</tbody>					
		</table>
	</div>
<?php include('footer.php'); ?>

</body>
</html>