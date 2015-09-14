<script type="text/javascript">

$(document).ready( function() {

	/*********************
	 * Activate Report Type Dropdown *
	 *********************/
	$("#report_picker").on('change', function() {
	    updateReport();
	});


    $(".datepicker").on('changeDate', function() {
        updateReport();
    });

    // Export report to CSV
    $("button").on('click', null, null, 
        function() {
            var report_type = ($("#report_picker").val() != -1 ? $("#report_picker").val() : null);
            // If date pickers are blank, use extremes of UNIX epoch.
            var start_date = $("#timespan_start_date").val() == "" ? "12/13/1901" : $("#timespan_start_date").val();
            var end_date = $("#timespan_end_date").val() == "" ? "01/18/2038" : $("#timespan_end_date").val();

            if (report_type != null && start_date != null && end_date != null) {
                window.location.href = "index.php?module=slc&action=ExportCSV&report_type=" + report_type + "&start_date=" + start_date + "&end_date=" + end_date;
            }
        }
    );
});

function updateReport() {
        var report_type = ($("#report_picker").val() != -1 ? $("#report_picker").val() : null);
        // If date pickers are blank, use extremes of UNIX epoch
        var start_date_string = $("#timespan_start_date").val() == "" ? '12/13/1901' : $("#timespan_start_date").val();
        var end_date_string = $("#timespan_end_date").val() == "" ? '01/18/2038' : $("#timespan_end_date").val();
        var start_date = new Date(start_date_string);
        var end_date = new Date(end_date_string);

		//if (report_type != null && start_date_string != null && end_date_string != null && start_date <= end_date) {
		if (report_type != null && start_date <= end_date) {
            $("#report-area").html("");
            $("#report-area").css('color', 'black');
			$.post(
					'index.php?module=slc&action=GETReport',
					{"report_type":report_type, "start_date":start_date_string, "end_date":end_date_string},
					// Callback function
					function(data) {
						//alert(data);
						data = jQuery.parseJSON(data);
						
                        if (data.report_html == "") {
                            $("#report-area").append("There are no records for that time period.");
                        } else {
						    $("#report-area").append("Report for: "+$("#report_picker option:selected").text());
                            // For reports that can be exported via CSV, show a button for CSV export
                            if (report_type == "landlordtenant" || report_type == "conditionbylandlord" || report_type == "followupappts") {
                                $("#report-area").append("&nbsp;&nbsp;<button name='csv_button' type='button'>Export to CSV</button>");
                            }
						    $("#report-area").append("<br /><br />"+data.report_html);
                        }
					});
        } else {
            $("#report-area").html("");
            $("#report-area").css('color', 'red');
            if (report_type == null) {
                $("#report-area").append("Please select a report.<br />");
            }
            if (start_date > end_date) {
                $("#report-area").append("The start date can not be after the end date.<br />");
            }
        }
}

// http://fixed-header-using-jquery.blogspot.com/
function fnAdjustTable (){

	var colCount=$('#firstTr>td').length; //get total number of column

	var m=0;
	var n=0;
	var brow='mozilla';
	jQuery.each(jQuery.browser, function(i, val) {
	if(val==true){

	brow=i.toString();
	}
	});
	$('.tableHeader').each(function(i){
	if(m<colCount){

	if(brow=='mozilla'){
	$('#firstTd').css("width",$('.tableFirstCol').innerWidth());//for adjusting first td

	$(this).css('width',$('#table_div td:eq('+m+')').width());//for assigning width to table Header div
	}
	else if(brow=='msie'){
	$('#firstTd').css("width",$('.tableFirstCol').width());

	$(this).css('width',$('#table_div td:eq('+m+')').width()-2);//In IE there is difference of 2 px
	}
	else if(brow=='safari'){
	$('#firstTd').css("width",$('.tableFirstCol').width());

	$(this).css('width',$('#table_div td:eq('+m+')').width());
	}
	else{
	$('#firstTd').css("width",$('.tableFirstCol').width());

	$(this).css('width',$('#table_div td:eq('+m+')').innerWidth());
	}
	}
	m++;
	});

	$('.tableFirstCol').each(function(i){
	if(brow=='mozilla'){
	$(this).css('height',$('#table_div td:eq('+colCount*n+')').outerHeight());//for providing height using scrollable table column height
	}else if(brow=='msie'){

	$(this).css('height',$('#table_div td:eq('+colCount*n+')').innerHeight()-2);
	}else{
	$(this).css('height',$('#table_div td:eq('+colCount*n+')').height());
	}
	n++;
	});



	}

	//function to support scrolling of title and first column
	function fnScroll(){

	$('#divHeader').scrollLeft($('#table_div').scrollLeft());
	$('#firstcol').scrollTop($('#table_div').scrollTop());


	}
</script>
