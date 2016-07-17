/**
  * Module Responsible for Drawing our canvas graphs, on both the grid page and the device pages.
  * Author: Isaac Assegai **/	

/** Functions Used for Graphing on the "Device" page.*/

/** Field Variables. */
var lineChart;
var polarChart;
var minPolarChart;
var hourPolarChart;
var dayPolarChart;
var currentLineChart;
var currentPolarChart;
var currentIP;

/** Test Data */
function getMinuteDemoData(){
	var data = [
		[1407428139000,0],
		[1407428149000,25],
		[1407428159000,50],
		[1407428169000,75],
		[1407428179000,100],
		[1407428189000,12],
		[1407428199000,75],
		[1407428209000,175],
		[1407428219000,200],
		[1407428229000,111],
		[1407428239000,135],
		[1407428249000,80],
		[1407428259000,75],
		[1407428269000,325],
		[1407428279000,300],
		[1407428289000,375],
		[1407428299000,400],
		[1407428309000,200],
		[1407428319000,170],
		[1407428329000,600],
		[1407428339000,660],
		[1407428349000,620],
		[1407428359000,550],
		[1407428369000,570],
		[1407428379000,60],
		[1407428389000,62],
		[1407428399000,65],
		[1407428409000,67],
		[1407428419000,70],
		[1407428429000,55],
		[1407428439000,35],
		[1407428449000,25],
		[1407428459000,0],
	];
	return data;
}

/** Test Data */
function getMinuteDemoData2(){
	var data = [
		[1407428189000,12],
		[1407428199000,75],
		[1407428209000,175],
		[1407428219000,200],
		[1407428229000,111],
		[1407428239000,135],
		[1407428249000,80],
		[1407428259000,75],
		[1407428269000,325],
		[1407428279000,300],
		[1407428289000,375],
		[1407428299000,400],
		[1407428309000,200],
		[1407428319000,170],
		[1407428329000,600],
		[1407428339000,660],
		[1407428349000,620],
		[1407428359000,550],
		[1407428369000,570],
		[1407428379000,60],
		[1407428389000,62],
		[1407428399000,65],
		[1407428409000,67],
		[1407428419000,70],
		[1407428429000,55],
		[1407428439000,35],
		[1407428449000,25],
		[1407428459000,300],
		[1407428469000,325],
		[1407428479000,400],
		[1407428489000,425],
		[1407428499000,325],
		[1407428509000,400],
	];
	return data;
}

/** Draws the data to the chart using the specified color, then returns a reference to the chart.*/ 
function getMainDeviceChart(name, color, data){
	var c = document.getElementById(name);
	clearCanvas(c);
	drawLabels(c, color, data);
	drawBackgroundGrid(c, color);
	drawData(c, color, data);		
	return c;
}

/** Clears the Canvas */
function clearCanvas(c){
	var ctx = c.getContext("2d");
	ctx.clearRect(0, 0, c.width, c.height);	
}

/** Draws the Data to the chart as lines. */
function drawData(c, color, data){
	//we'll need the min, max latency and time to calculate positions
	var minMaxLatency = getMinMaxLatency(data);
	var minMaxTime = getMinMaxTime(data);
	if(minMaxLatency[0] == '0' && minMaxLatency[1] == '0'){// All Data is 0
		//draw an empty line at the bottom of the graph.
		drawFlatLine(c, "#ff0000");
	}else{ // Draw the normal graph
		//we only loop to the second last element. The last line will include the last element.
		for(i =0; i < data.length-1; i++){
			//get the points for this line by translating data to pixels.
			var p1 = translateToPixels(data[i], c, minMaxTime, minMaxLatency);
			var p2 = translateToPixels(data[i+1], c, minMaxTime, minMaxLatency);
			drawLine(c, p1[0], p1[1], p2[0], p2[1], 3, "#ffffff");			
		}
	}
}

/** Draws a flat line to the selected canvas graph.
 * @param c The canvas we draw the line to.
 * @param color The color of the line.
 */
function drawFlatLine(c, color){
	var gridWidth = (c.width/25); 
	var gridHeight = c.height/20;
	var widthMinMax = [(gridWidth*2)+7, (gridWidth*24)+7];
	var heightMinMax = [gridHeight*17-10, gridHeight*1-10];
	//var tarX = map(point[0], minMaxTime[0], minMaxTime[1], widthMinMax[0], widthMinMax[1]);
	//var tarY = 0;
	drawLine(c, widthMinMax[0], heightMinMax[0], widthMinMax[1], heightMinMax[0], 3, "#ff0000");
}

/** Translate a (time, latency) point to a (x, y) pixel*/ 
function translateToPixels(point, c, minMaxTime, minMaxLatency){
	if(point[0] == -1){
		point[0] = 0;
	}
	if(point[1] == -1){
		point[1] = 0;
	}
	//first we get the width and height of every section of the grid.
	var gridWidth = (c.width/25); 
	var gridHeight = c.height/20;
	//then we set the min and max of the area we want to draw data into.
	//this excludes the area where labels are located.
	var widthMinMax = [(gridWidth*2)+7, (gridWidth*24)+7];
		//we swap heightMinMax to reverse the graph so lower latencies will be lower on the screen.
	var heightMinMax = [gridHeight*17-10, gridHeight*1-10];
	//then we get the x and y point to translate to by mapping our data to our new parameters.
	var tarX = map(point[0], minMaxTime[0], minMaxTime[1], widthMinMax[0], widthMinMax[1]);
	var tarY = map(point[1], minMaxLatency[0], minMaxLatency[1], heightMinMax[0], heightMinMax[1]);	
	//pack the answer into its own array and return.
	var target = [tarX, tarY];	
	return target;
} 
	 
/** translate x from its old range to its new position in the new range */	 
function map(x, in_min, in_max, out_min, out_max){
	return (x - in_min) * (out_max - out_min) / (in_max - in_min) + out_min;
}
	
/** Draw All Labels for both Axis of Graph. */
function drawLabels(c, color, data){
	labelColor = "#ffffff";
	drawYName(c, labelColor, "LATENCY");
	drawYLabels(c, labelColor, data);
	drawXName(c, labelColor, "TIME");
	drawXLabels(c, labelColor, data);
}
	
/** Draw the background grid.*/
function drawBackgroundGrid(c, color){		
	var gridColor = "#4eb5f7";
	//Decides grid width & height based on how many sections we have in both.
	var gridWidth = c.width/25;
	var gridHeight = c.height/20;
	//draw x horizontal lines.
	for(i = 0; i < 17; i++){
		//we want to draw bottom to top instead of top to bottom, so we subtract the i from total
		j = 17-i;
		drawLine(c, 45, (gridHeight*j)-(gridHeight/2),
		gridWidth*24+7, (gridHeight*j)-(gridHeight/2), 1, gridColor);
	}
	
	//draw Vertical Grid 23 lines
	for(i = 0; i < 23; i++){
		if(i == 10 | i ==11){
			y2 = (gridHeight*17)-10; //make room for the "TIME" label
		}else{
			y2 = (gridHeight*17);	//normal bottom of the line.
		}
		drawLine(c, 55+(gridWidth*i), y2,
					55+(gridWidth*i), (gridHeight*j)-(gridHeight/2), 
					1, gridColor);
	}
}

/* Position and Draw the "Time" label */	
function drawXName(c, color, name){
	var gridHeight = c.height/20;
	var ctx = c.getContext("2d");
	ctx.font = "14px Raavi";
	ctx.fillStyle = color;
	ctx.lineWidth = 1;
	ctx.strokeStyle = color;
	ctx.strokeText(name,(c.width/2)-10,(gridHeight*18)-10);
}

/** Position & Draw An appropriate range of times based on the data.*/	
function drawXLabels(c, color, data){
	// calculate the space between every label based on how many sections are in the grid.
	var gridWidth = c.width/25;
	var ctx = c.getContext("2d");;
	var minMaxTime = getMinMaxTime(data);
	//calculate an average time between each label. Based on the fact there are 23 labels
	var timeGap = (minMaxTime[1] - minMaxTime[0])/23;
	ctx.strokeStyle = color;
	ctx.translate(c.width / 2, c.height / 2);
	ctx.rotate(-Math.PI / 2);
	ctx.translate(-c.width / 2, -c.height / 2);
	ctx.fillStyle = color;
	ctx.font = "12px Raavi";
	//draw 23 labels
	for(i = 0; i < 23; i++){
		j = 23-i;
		var r_timeGap = Math.round(timeGap);
		var itimes_timeGap = (i * r_timeGap);
		var minplus_timeGap = (parseInt(minMaxTime[0]) + itimes_timeGap);
		var thisTime = Math.round(minplus_timeGap);
		var time = new Date(thisTime);
		var timeDisplay = time.getHours()+":"+time.getMinutes()+":"+time.getSeconds();
		ctx.fillText(timeDisplay,90,(gridWidth*i)-30);
	}
	//we do this to rotate the labels, coordinates get translated because of this.
	ctx.translate(c.width / 2, c.height / 2);
	ctx.rotate(Math.PI / 2);
	ctx.translate(-c.width / 2, -c.height / 2);	
	}

/** Draw the Latency Labels. */
function drawYLabels(c, color, data){
	//calculate the difference in height based on 20 total sections
	var minMaxLatency = getMinMaxLatency(data);
	var gridHeight = c.height/20;
	var latencyGap;
	if(minMaxLatency[0] == '0' && minMaxLatency[1] == '0'){ // If all data is 0
		latencyGap = 10;
	}else{
		latencyGap = (minMaxLatency[1] - minMaxLatency[0])/17;
	}
	
	
	var ctx = c.getContext("2d");
	ctx.font = "12px Raavi";
	ctx.fillStyle = color;
	ctx.lineWidth = 1;
	ctx.strokeStyle = color;
	//draw 17 labels
	for(i = 0; i < 17; i++){
		var r_latencyGap = Math.round(latencyGap);
		var itimes_latencyGap = (i * r_latencyGap);
		var minplus_latencyGap = (parseInt(minMaxLatency[0]) + itimes_latencyGap);
		var thisLatency = Math.round(minplus_latencyGap);
		
		j = 17-i;
		ctx.strokeText((thisLatency),21,((gridHeight*j)));
	}	
}	

/** Returns an array containing the earliest and latest time of the data. */
function getMinMaxTime(data){
	if(data.length >= 1){
		var minTime = data[0][0];
		var maxTime = data[0][0];
	}
	for(i = 0; i < data.length; i++){
		//find the higher numbers.
		if(parseInt(data[i][0]) > parseInt(maxTime)){
			maxTime = data[i][0];
		}
		//look for lower numbers.
		if(parseInt(data[i][0]) < parseInt(minTime)){
			minTime = data[i][0];	
		}
	}
	var minMaxTime = [minTime, maxTime];
	return minMaxTime;
}

/** Returns an array with the minimum and maximum latency in the data.*/
function getMinMaxLatency(data){ 
	if(data.length >= 1){
		var minLatency = data[0][1];
		var maxLatency = data[0][1];	
	}
	for(i = 0; i < data.length; i++){
		//look for higher numbers
		var currentData = data[i][1];
		if(parseInt(currentData) > parseInt(maxLatency)){
			maxLatency = currentData;	
		}
		//look for lower numbers
		if(parseInt(currentData) < parseInt(maxLatency)){
			minLatency = currentData;	
		}
	}
	//var minMaxLatency = [minLatency, maxLatency];
	var minMaxLatency = [0, maxLatency];
	return minMaxLatency;
}
	
/** Draw and position the "Latency" label. */
function drawYName(c, color, labelName){
	var ctx = c.getContext("2d");
	ctx.lineWidth = 1;
	ctx.strokeStyle = color;
	ctx.translate(c.width / 2, c.height / 2);
	ctx.rotate(-Math.PI / 2);
	ctx.translate(-c.width / 2, -c.height / 2);
	ctx.fillStyle = color;
	ctx.font = "14px Raavi";
	ctx.strokeText(labelName,c.height/2+80,-75);
	ctx.translate(c.width / 2, c.height / 2);
	ctx.rotate(Math.PI / 2);
	ctx.translate(-c.width / 2, -c.height / 2);	
}

/** Returns the Test data for Polar Charts. */
function getPolarTestData(){
	var polormin = [{
		value: 75,
		color:"#F7464A",
		highlight: "#FF5A5E",
		label: "500+"
	},{
		value: 75,
		color: "#46BFBD",
		highlight: "#5AD3D1",
		label: "300-500"
	},{
		value: 75,
		color: "#FDB45C",
		highlight: "#FFC870",
		label: "200-300"
	},{
		value: 75,
		color: "#949FB1",
		highlight: "#A8B3C5",
		label: "100-200"
	},{
		value: 75,
		color: "#4D5360",
		highlight: "#616774",
		label: "0-100"
	}];
	return polormin;			
}

/** Updates the line graph and polar chart from info retrieved from the device-backend. */
function updateGraph(){
	(function worker() {
		var postData = {ip:currentIP,
						LineChart:currentLineChart,
						PolarChart:currentPolarChart};
		$.ajax({
			type:"POST",
			data : postData,
			url: 'php/device-backend.php', 
			success: function(result,status,xhr) {
				var lineChartData = translateIncomingLineData(result); 
				var mainDeviceChart = getMainDeviceChart(currentLineChart, "#51bbff", lineChartData);	  
				var newData = translateIncomingPolarData(result); 
				updatePolarChart(polarChart, newData);
				setTimeout(updateGraph, 15000);
				//alert("success" + result);
			},
			complete: function(result) {
				// Schedule the next request when the current one's complete
				//alert("complete" + result);
			},
			error: function(xhr,status,error){
				alert("error" + data);
			}
		});
	})();			
}
		
/** Function is executed once at page load to update the graph. Doesn't trip a timeout. */
function quickUpdateGraph(){
	(function worker() {
		var postData = {ip:currentIP,
						LineChart:currentLineChart,
						PolarChart:currentPolarChart};
		$.ajax({
			type:"POST",
			data : postData,
			url: 'php/device-backend.php', 
			success: function(result,status,xhr) {
				var lineChartData = translateIncomingLineData(result); 
				var mainDeviceChart = getMainDeviceChart(currentLineChart, "#51bbff", lineChartData);	  
				var newData = translateIncomingPolarData(result); 
				updatePolarChart(polarChart, newData);
			},
			complete: function() {
				// Schedule the next request when the current one's complete
				//alert("complete" + data);
			},
			error: function(xhr,status,error){
				alert("error" + data);
			}
		});
	})();	
}
		
/** Test function used to change the charts. */
function changeCharts(){
	currentPolarChart = "HourPolar";
	currentLineChart = "HourLine";
	updateGraph();
}

/** Updates the specified polar chart with the specified data. */
function updatePolarChart(apolarChart, newData){
//echo newData;
	for(i = 0; i < newData.length; i++){
		var dataItem = newData[i];
		apolarChart.segments[i].value = dataItem['value'];
		apolarChart.segments[i].color = dataItem['color'];
		apolarChart.segments[i].highlight = dataItem['highlight'];
		apolarChart.segments[i].label = dataItem['label'];
		apolarChart.update();	
	}
}

/** Translate incoming data for a polar chart. */
function translateIncomingPolarData(data){

	var polarsplitData = data.split("-"); //split the line data from the polar data
	var polarData = polarsplitData[1]; //this is the polar data.
	var records = polarData.split(" "); //split off individual records from the polar data
	//get our limit and value settings based on our records.

	var limit1 =records[0].split(":")[0];
	var limit2 =records[1].split(":")[0];
	var limit3 =records[2].split(":")[0];
	var limit4 =records[3].split(":")[0];
	var limit5 =records[4].split(":")[0];
	var val1s = records[0].split(":")[1];
	var val2s = records[1].split(":")[1];
	var val3s = records[2].split(":")[1];
	var val4s = records[3].split(":")[1];
	var val5s = records[4].split(":")[1];
	var val1 = parseFloat(val1s);
	var val2 = parseFloat(val2s);
	var val3 = parseFloat(val3s);
	var val4 = parseFloat(val4s);
	var val5 = parseFloat(val5s);
	var total = val1+val2+val3+val4+val5;
	//calculate the percentage each section will represent.
	val1 = (val1/total)*100;
	val2 = (val2/total)*100;
	val3 = (val3/total)*100;
	val4 = (val4/total)*100;
	val5 = (val5/total)*100;
	//limit the top value to 75% to allow lower values to display.
	if(val1 > 75) val1 = 75;
	if(val2 > 75) val2 = 75;
	if(val3 > 75) val3 = 75;
	if(val4 > 75) val4 = 75;
	if(val5 > 75) val5 = 75;
	if(val1 < 10) val1 = 10+val1*3;
	if(val2 < 10) val2 = 10+val2*3;
	if(val3 < 10) val3 = 10+val3*3;
	if(val4 < 10) val4 = 10+val4*3;
	if(val5 < 10) val5 = 10+val5*3;
	//build the polar data structure from interpreted results. 
	var newPolarData = [{
		value: val1,
		color:"#F7464A",
		highlight: "#FF5A5E",
		label: "0 - "+limit1
	},{
		value: val2,
		color: "#46BFBD",
		highlight: "#5AD3D1",
		label: limit1+"-"+limit2
	},{
	value: val3,
					color: "#FDB45C",
					highlight: "#FFC870",
					label: limit2+"-"+limit3
				},
				{
					value: val4,
					color: "#949FB1",
					highlight: "#A8B3C5",
					label: limit3+"-"+limit4
				},
				{
					value: val5,
					color: "#4D5360",
					highlight: "#616774",
					label: limit4+"-"+limit5
				}
			
			];
			//alert(records[0].split(":")[1]);
			
			return newPolarData;
		}
		
		function translateIncomingLineData(data){
			//alert("data: " +data);
			var splitData = data.split("-");
			var lineData = splitData[0];
			//alert("lineData: " +lineData);
			
			var records = lineData.split(" ");
			var newData = new Array(records.length-1);
			
			for(i = 0; i < records.length-1; i++){
				var intRecord = records[i];
				var record = intRecord.split(":");
				newData[i] = record;
			}
			//alert(newData);
			return newData;
		}
		
		function setupCharts(newIP){
			
					currentPolarChart = "FiveMinutePolar";
					currentLineChart = "FiveMinuteLine";
					currentIP = newIP
					minPolarChart = initializePolarChart(currentPolarChart);
					hourPolarChart = initializePolarChart("HourPolar");
					dayPolarChart = initializePolarChart("DayPolar");
					polarChart = minPolarChart;
					lineChart = initializeLineChart(currentLineChart);	
				}
				
				function initializeLineChart(chart){
					var demoData = getMinuteDemoData();
	   				var mainDeviceChart = getMainDeviceChart(chart, "#51bbff", demoData);
					return mainDeviceChart;
				}
					
				function initializePolarChart(chart){
					var cht = document.getElementById(chart);
					var ctx = cht.getContext('2d');
					var newpolarChart = new Chart(ctx).PolarArea(getPolarTestData(), {});
					return newpolarChart;
				}

	
/** Functions Used for Graphing on the "Grid" page.*/	

	function initializeCanvas(name){
		var c = document.getElementsByClassName(name);
		return c;
	}
	
	function colorCanvas(canvas, color){
		var ctx = canvas.getContext("2d");
		ctx.fillStyle = color;
		ctx.fillRect(0,0,c.width,c.height);
	}
	
	function drawGraph(canvas){
		var numPoints = 14;
		var widthSections = numPoints + 2;
		var heightSections = 115;
		
		//widthMultiplier & //heightMultipler
		var w = canvas.width/widthSections; 
		var h = canvas.height/heightSections;
		
		drawLine(canvas, w*1, h*100, w*2, h*20, 1, "#ffffff");
		drawLine(canvas, w*2, h*20, w*3, h*1, 1, "#ffffff");
		drawLine(canvas, w*3, h*1, w*4, h*20, 1, "#ffffff");
		drawLine(canvas, w*4, h*20, w*5, h*30, 1, "#ffffff");
		drawLine(canvas, w*5, h*30, w*6, h*35, 1, "#ffffff");
		drawLine(canvas, w*6, h*35, w*7, h*25, 1, "#ffffff");
		drawLine(canvas, w*7, h*25, w*8, h*50, 1, "#ffffff");
		drawLine(canvas, w*8, h*50, w*9, h*40, 1, "#ffffff");
		drawLine(canvas, w*9, h*40, w*10, h*45, 1, "#ffffff");
		drawLine(canvas, w*10, h*45, w*11, h*80, 1, "#ffffff");
		drawLine(canvas, w*11, h*80, w*12, h*90, 1, "#ffffff");
		drawLine(canvas, w*12, h*90, w*13, h*95, 1, "#ffffff");
		drawLine(canvas, w*13, h*95, w*14, h*75, 1, "#ffffff");
		
		drawPoint(canvas, w*1, h*100, "#ffffff");
		drawPoint(canvas, w*2, h*20, "#ffffff");
		drawPoint(canvas, w*3, h*1, "#ff0000");
		drawPoint(canvas, w*4, h*20, "#ffffff");
		drawPoint(canvas, w*5, h*30, "#ffffff");
		drawPoint(canvas, w*6, h*35, "#ffffff");
		drawPoint(canvas, w*7, h*25, "#ffffff");
		drawPoint(canvas, w*8, h*50, "#ffffff");
		drawPoint(canvas, w*9, h*40, "#ffffff");
		drawPoint(canvas, w*10, h*45, "#ffffff");
		drawPoint(canvas, w*11, h*80, "#ffffff");
		drawPoint(canvas, w*12, h*90, "#ffffff");
		drawPoint(canvas, w*13, h*95, "#ffffff");
		drawPoint(canvas, w*14, h*75, "#ffffff");
	}
	
	function drawLine(canvas, x1, y1, x2, y2, lineWidth, color){
		y1+=6;
		y2+=6;
		var context = canvas.getContext('2d');
		context.beginPath();
		context.moveTo(x1, y1);
		context.lineTo(x2, y2);
		context.lineWidth = lineWidth;
		// set line color
		context.strokeStyle = color;
		context.stroke();	
	}
	
	function drawPoint(canvas, xPoint, yPoint, color){
		  yPoint += 6;
		  var context = canvas.getContext('2d');
		  var radius = canvas.width/100;
		  context.beginPath();
		  context.arc(xPoint, yPoint, radius, 0, 2 * Math.PI, false);
		  context.fillStyle = color;
		  context.fill();
		  context.lineWidth = 5;
		  context.strokeStyle = color;
		  context.stroke();
	}