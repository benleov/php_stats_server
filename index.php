<?php

		include_once 'db_functions.php';
		
		$db = get_db();

		// TODO: 
		// Move all the select code out of here and into db_functions.
		// Fix month "off by one" date issue.
		
		// calendar date field picker - must set the value of the field to
		// something that strtotime(...) can parse. Sending "dd m Y" which I beleive can and is converting properly
		// see http://php.net/manual/en/function.strtotime.php
		
		// slider - the value that is returned from this should be the interval in milliseconds - 
		// at moment converting from string to miliseconds 
		// set intervals in slider based on how much time has passed between first and last entry?
		// NOTES:
		
		// example group by
		// http://localhost/index.php?tag=1&group=100
                // 
		// CAREFUL: time is in milliseconds! Setting too low can be very slow! 
                // (by default php times out after 30 seconds)
		
		// displaying all entries has been moved to entries.php
		
		// should each user be placed in a separate database or collection?
		
		$collection = $db->entries;
		$tag_results = $db->command(array("distinct" => "entries", "key" => "tag")); // TODO: move to db_functions
		$selected_tag = filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_STRING);
		$group_interval_name = filter_input(INPUT_GET, 'group', FILTER_SANITIZE_STRING);
		
		if (!empty($group_interval_name)) {
			if ($group_interval_name == 'minute') {
				$group_interval = 60000;
				}
			elseif ($group_interval_name == 'hour') {
				$group_interval = 3600000; 
			}
			elseif ($group_interval_name == 'day') {
				$group_interval = 86400000; 
			}
			elseif ($group_interval_name == 'week') {
				$group_interval = 604800000;
			} elseif ($group_interval_name == 'month') {
				$group_interval = 2.62974383 * pow(10,9); 
			}		
			elseif ($group_interval_name == 'year') {
				$group_interval = 3.1556926  * pow(10,10); 
			} 
		}	
		if(empty($selected_tag)) {
			$cursor = $collection->find(array('user' => '1'));	// only user 1
		} else {
			$cursor = $collection->find(array('user' => '1', 'tag' => $selected_tag));	// only user 1
		}
		
		$cursor -> sort(array('time' => 1)); // asc
?>

<html>
	<head>
		<link type="text/css" href="css/ui-darkness/jquery-ui-1.8.19.custom.css" rel="stylesheet" />
		<link type="text/css" href="css/styles.css" rel="stylesheet" />
		<!-- So can use HTML5 tags: -->
		<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->					
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
		
		<script src="http://code.highcharts.com/highcharts.js"></script>
		<!-- <script src="http://code.highcharts.com/modules/exporting.js"></script> -->	

		<!--<script src="js/highcharts.js" type="text/javascript"></script> -->
		<script type="text/javascript" src="js/themes/gray.js"></script>
		<script type="text/javascript" src="js/jqueryui/js/jquery-ui-1.8.19.custom.min.js"></script>
	</head>
	<body>
	<div class="wrapper">
		<section id="new_stat">
			<h1>New Stat</h1>
			<form action='process.php' method='post'>
				<h2>tag</h2>
				<input type="text" name="tag" id="input_tag" />
				<h2>date</h2>
				<input id="datepicker" type="text" name="date" value="<?php echo date('d-m-Y' . ' ' . 'H:m:s' ); ?>"/>
				<h2>value</h2>
				<input type="text" name="value" />
				<input type="hidden" name="user_key" value="1" />
				<input type="hidden" name="selected_tag" value="<?php echo $selected_tag; ?>" />
				<input type="submit" />
			</form>
		</section>	
		<section id="tag_cloud">
			<h1>Tag Cloud</h1>
			<?php
				foreach ($tag_results['values'] as $tag) {
					print "<input type='button' class='tag_cloud_input' value='" . $tag . "'>";
					print "</input>";
				}
			?>			
		</section>
		<section id="import">
		<h1>Import</h1>
		<a href="import.php">Import from csv</a>
		</section>
		
		<section id="graph">	
			<h1>Graph</h1>
			
			<p>Select the tag you want to display on the graph
				<?php
					print "<select id='select_graph_tag'>";
					foreach ($tag_results['values'] as $tag) {
						print "<option>";
						print $tag;
						print "</option>";
					}
					print "<h2>Select tag</h2>";
					// TODO:
					print "</select>";
					
				?>
			</p>	
			<div id="container" style="width: 100%; height: 400px"></div>
			<div id="slider"></div>	
			<h2 id="increment">
			<?php if(empty ($group_interval_name)) { ?>
			Slide to Graph by Time Interval
			<?php } else { 
				echo "Grouped by " . $group_interval_name; 
			}	?>
			</h2>
		</section>
	</div>	
<script>
	var chart1; // globally available
	$(document).ready(function() {
	
		// auto-popuplate field with selected tag from tag cloud
		$(".tag_cloud_input").click(function () {
			$("#input_tag").val($(this).val());
		});
		//stretch width of tag cloud when it gets too big
		if ($('#tag_cloud').height() > $('#new_stat').height()) {
			$('#tag_cloud').width($('#tag_cloud').width() + ($('#tag_cloud').height()-$('#new_stat').height()));
		}		
		
		/** resubmits the page which selects the specified graph */
		$("#select_graph_tag").val('<?=$selected_tag?>'); 
		$("#select_graph_tag").change(function () {
		  var str = "";
		  
		  $("select option:selected").each(function () {
				str += $(this).text() + " ";
			});
		window.location="index.php?tag=" + str;
	});
	
	/** create the chart */
	chart1 = new Highcharts.Chart({
		chart: {
			renderTo: 'container',
			type: 'spline',
			
			events: {
				load: requestData(null)
				// set intially (i.e if graph is not blank)
		
				// http://api.jquery.com/jQuery.getJSON/
				// 
				// [{"user":"1","tag":"my_tag","value":8,"time":"0.00000000 1335613421","id":"4f9bd7ed00bc646007000001"}]
				// 
				//alert('here');
				//var x = (new Date()).getTime(), // current time
				//y = Math.random();
				//series.addPoint([x, y], true, true);
				// http://localhost/json/latest_entries.php?user_key=1&tag=beers&date=-3%20hour
			}
		},
		 
		 title: {
			text: '<?php echo $selected_tag; ?>'
		 },
		 
		xAxis: {
			type: 'datetime',
			tickPixelInterval: 150,
			/*
			dateTimeLabelFormats: { // don't display the dummy year
				month: '%e. %b',
				year: '%b'
			}*/

		},
		
		yAxis: {
			title: {
				text: 'Value'
			},
			min: 0
		},
		legend: {
			enabled: false
		},

		exporting: {
			enabled: false
		},
		
		series: [{
			name: 'Time',
			
			data: [
			<?php
			$last_time = null;
			
			if(empty($group_interval) || $group_interval == 0) {
				// no interval specified, just print stats as is
				foreach ($cursor as $obj) {
					$last_time = $obj['time']->sec;
					// convert to javascript UTC time
					$formatted = date('Y,m,d,H,i,s', $last_time);
					print '[Date.UTC(' . $formatted . '),' . $obj['value'] . '],';
				}
				
				// save the last date for ajax requests
				$last_time = date('Y-m-d H:i:s', $last_time);
				
			} else {
				// TODO: user_key is statically set
				$results =  sum('1', $selected_tag, $group_interval, true);
				$times = array_keys($results);
				
				foreach($times as $curr) {
					$sum = $results[$curr];
					print '[Date.UTC(' . $curr . '),' . $sum . '],';
				}
			}
		?>
			 ]
		}]
	});
	
	
	
	function requestData(from_time) {
		var inital_time="<?php echo $last_time; ?>";
		console.log( "requesting data");
		
		if(from_time == null) {
			from_time = inital_time;
		}
		
		if(from_time !== null) {
			// TODO: static user_key
			$.getJSON('json/latest_entries.php?user_key=1&tag=<?php echo$selected_tag; ?>&date=' + from_time, function(data) {
			
				// http://localhost/json/latest_entries.php?user_key=1&tag=my_tag&date=-3%20hour
				var series = chart1.series[0];
				var last = null;
			
				$.each(data, function(key, val) {
					// convert to javascript UTC time
					time = val.time.replace(/:/g, ",").replace(/ /g, ",").replace(/-/g, ",");
					//shift = series.data.length > 20; // shift if the series is longer than 20
					console.log(" value " + val.value);
					split = time.split(",");
					series.addPoint([Date.UTC(split[0], split[1], split[2], split[3], split[4], split[5]), val.value], true, true);
					last = val.time;
					
				});
		
				if(last != null) {	
					from_time = last;
				} else {
					console.log("No new data received.");
				}
				
				// reschedule next request
				setTimeout(function(){requestData(from_time)}, 10000); 
				
			});
			
		}	
		
	}
	
		// Slider
		$('#slider').slider({
			max: 20,
			min: 0,
			step: 2
		});
		//get value that slide to and stop at
		var increments = ["minute","hour","day","week","month","year"];
		var match, index, entry;
		
		<?php if (!empty($group_interval_name)) { ?>
		for (index = 0; index < increments.length; ++index) {
			entry = increments[index];
			
			if (entry == '<?=$group_interval_name?>') {
				match = index;
				break;
			}
		}
		<?php } else {?>
			match = 0;
		<?php } ?>
		$( "#slider" ).slider({
			max: increments.length - 1,
			step: 1,
			value: match,
			slide: function(event, ui) {
			$("#increment").text(increments[ui.value]);
			},
			stop: function(event, ui) { //get value that stop at 
				var group = increments[ui.value];			
				var str = $("select option:selected").val();
				window.location="index.php?tag="+str+"&group="+group; //refresh page and append stop value
			}
		});
		//datepicker
		$(function() {
			$( "#datepicker" ).datetimepicker({
				dateFormat: "dd-mm-yy",
				altFormat: "dd m Y",
				showSecond: true,
				timeFormat: 'hh:mm:ss'
			});
		});
   });	
</script>		
	</body>
</html>