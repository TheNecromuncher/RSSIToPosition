<!-------------------------  index.php  -------------------------

This is the most important file for Safe Route, and is what gets
called in the mobile app to show the main user interace.

----------------------------------------------------------------->


<!DOCTYPE html>

<!-- HTML -->
<html lang="en">
	
	<!-- Head -->
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="description" content="Safe Route provides mobile navigation during a fire evacuation at UNF in Building 4 on the first floor" />
		<meta name="author" content="Christian Hayes" />

		<title>Safe Route</title>

		<link rel="icon" type="image/png" href="wwwroot/Images/icon.png" />
		<link rel="apple-touch-icon" href="wwwroot/Images/apple-touch-icon.png" />
		<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css" />
		<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
		<link rel="stylesheet" type="text/css" href="wwwroot/css/site.css" />
	</head>
	<!-- END Head -->

	<!-- Body -->
	<body>


		<!-- Navigation Speech -->
		<form id="tap-screen">
		    <input class="display-none" type="text" class="txt">
		    <select class="display-none"></select>
		    <div class="controls">
		    	<button id="play-button" type="submit"></button>
		    </div>
	    </form>
	    <script src="wwwroot/js/web-speech-api.js"></script>
	    <script>
	    	alert("Tap anywhere on screen to hear navigation assistence.");
	    	var speak_btn = document.getElementById("play-button");
	    	speak_btn.style.width = window.innerWidth + "px";
	    	speak_btn.style.height = window.innerHeight + "px";
	    </script>
	    <!-- END Navigation Speech -->



	    <!-- Navigation Assistance -->
	    <div id="nav-assistance">
	    	<p><i id="direction-icon" class="material-icons">north</i><span id="nav-assistance-text">Continue on path</span></p>
	    </div>
	    <!-- END Navigation Assistance -->





	    <!-- Error Not In Range -->
	    <table id="error-not-in-range">
	    	<tr>
	    		<td id="error-not-in-range-text"></td>
	    	</tr>
	    </table>
	    <!-- END Error Not In Range -->




		<!-- Navigation Dot -->
		<div id="nav-dot">
			<span id="blue-dot"></span>
			<span id="white-dot"></span>
			<span id="nav-triangle"></span>
			<span id="light-dot"></span>
		</div>
		<!-- END Navigation Dot -->




		<!-- Map Rotation -->
		<div id="map-rotation">

			<!-- Script -->
			<script>
				document.getElementById("map-rotation").style.transformOrigin = window.innerWidth/2 + "px " + window.innerHeight/2 +  "px";
			</script>
			<!-- END Script -->


			<!-- Place Icon (Exit) -->
			<div id="exit-icon">
				<i id="place-icon" class="material-icons">place</i>
				<i id="place-circle" class="material-icons">stop_circle</i>
			</div>
			<!-- END Place Icon (Exit) -->


			<!-- Routing Path Canvas -->
			<canvas id="routing-path-canvas"></canvas>
			<!-- END Routing Path Canvas -->
			

			<!-- First Floor Layout -->
			<div id="layout-frame">
				<img id="first-floor" alt="First Floor Layout" src="wwwroot/Floorplans/first-floor-edited.jpg" />
			</div>
			<!-- END First Floor Layout -->

		</div>
		<!-- END Map Rotation -->




		<!-- Requires -->
		<?php
			require "/home/data/public_html/routes-and-nodes.php";
			require "/home/data/public_html/routing-table.php";
		?>
		<!-- END Requires -->




		<!-- Scripts -->
		<script type="text/javascript" src="wwwroot/js/dijkstra-graph.js"></script>
		<script type="text/javascript" src="wwwroot/js/graph-conversions.js"></script>
		<script type="text/javascript" src="wwwroot/js/routing-table.js"></script>
                <script type="text/javascript" src="wwwroot/js/jiang-algorithm.js"></script>
		<script type="text/javascript">

			// PHP to JavaScript variable conversions for database tables
			var routes_and_nodes = <?= json_encode($routes_and_nodes); ?>;
			var exits = <?= json_encode($exits); ?>;
			var simple_routes_and_nodes = <?= json_encode($simple_routes_and_nodes); ?>;
			var simple_exits = <?= json_encode($simple_exits); ?>;

			var stairwells = <?= json_encode($stairwells); ?>;

			var original_routes_and_nodes = copyArray(routes_and_nodes);
			var original_simple_routes_and_nodes = copyArray(simple_routes_and_nodes);

			var nodes = <?= json_encode($nodes); ?>;
			var simple_nodes = <?= json_encode($simple_nodes); ?>;

			var original_nodes = copyArray(nodes);
			var original_simple_nodes = copyArray(simple_nodes);

			var num_routes = <?= json_encode($num_routes); ?>;
			var num_simple_routes = <?= json_encode($num_simple_routes); ?>;
			var num_nodes = <?= json_encode($num_nodes); ?>;
			var num_simple_nodes = <?= json_encode($num_simple_nodes); ?>;

			var original_num_routes = copyArray(num_routes);
			var original_num_simple_routes = copyArray(num_simple_routes);
			var original_num_nodes = copyArray(num_nodes);
			var original_num_simple_nodes = copyArray(num_simple_nodes);

			var routing_table = <?= json_encode($routing_table); ?>;
			var simple_routing_table = <?= json_encode($simple_routing_table); ?>;

			var original_routing_table = copyArray(routing_table);
			var original_simple_routing_table = copyArray(simple_routing_table);

			var beacon_names = <?= json_encode($beacon_names); ?>;

			var rssi_fingerprinting = <?= json_encode($rssi_fingerprinting); ?>;

			/* Copy Array Function */
			/* Returns a copy of the input array */
			function copyArray(array) {
				return JSON.parse(JSON.stringify(array));
			}

			/* Original Arrays Function */
			/* Converts arrays back into the original arrays before they were modified */
			function originalArrays() {
				simple_nodes = copyArray(original_simple_nodes);
				simple_routes_and_nodes = copyArray(original_simple_routes_and_nodes);
				simple_routing_table = copyArray(original_simple_routing_table);
				num_simple_nodes = copyArray(original_num_simple_nodes);
				num_simple_routes = copyArray(original_num_simple_routes);
				nodes = copyArray(original_nodes);
				routes_and_nodes = copyArray(original_routes_and_nodes);
				routing_table = copyArray(original_routing_table);
				num_nodes = copyArray(original_num_nodes);
				num_routes = copyArray(original_num_routes);
			}

			// Used for testing purposes
			var simple_routing = false;
			var routing_simulation_1 = false;
			var routing_simulation_2 = false;
		</script>
		<script>
			var routes_;
			var nodes_;
			var g;
			var t;
			var route_number_graph;
			var source;
			var exits_;
			var take_path_angles = [];
			var distance_to_closest_exit = 10000;

			/* Create Graph Function */
			/* Creates a graph that will be used by Dijkstra's algorithm */
			function create_graph() {
				routes_ = num_simple_routes;
				nodes_ = num_simple_nodes;
				if (!simple_routing) {
					routes_ = num_routes;
					nodes_ = num_nodes;
				}
				g = new Graph(nodes_);
				t = simple_routing_table;
				if (!simple_routing) {
					t = routing_table;
				}
				t = simplify(t);
				if (simple_routing) {
					route_number_graph = routes_and_nodes_to_graph(simple_routes_and_nodes);
				}
				else {
					route_number_graph = routes_and_nodes_to_graph(routes_and_nodes);
				}
				g.graph = final_graph(route_number_graph, t);
			}
			
			/* Calculate Closest Exit Function */
			/* Determines which exit node is closest to the input start node */
			function calc_closest_exit(start_node) {
				source = parseInt(start_node);
				dist_from_source = g.dijkstra(source);
				if (simple_routing) {
					exits_ = get_exits(dist_from_source, simple_exits);
				}
				else {
					exits_ = get_exits(dist_from_source, exits);
				}
				var closest_exit = exits_[0][0];
				var closest_exit_distance = exits_[0][1].toFixed(2);
				distance_to_closest_exit = closest_exit_distance;
				var take_path = g.getPath(exits_[0][0]);
				take_path_angles.length = 0;
				take_path_angles = getPathAngles(take_path);
				var angle = take_path_angles[0];
				var get_directions = getDirections(angle, distance_to_closest_exit);
  				var text = get_directions[0];
  				var dir_icon = get_directions[1];
				var nav_assistance_text = document.querySelector('#nav-assistance-text');
				var direction_icon = document.querySelector('#direction-icon');
				nav_assistance_text.innerHTML = text;
				direction_icon.innerHTML = dir_icon;
				getRoutingPath(take_path, closest_exit);
			}
		</script>
		<!-- END Scripts -->




		<!-- Scripts -->
		<script type="text/javascript" src="wwwroot/js/ips.js"></script>
		<script type="text/javascript" src="wwwroot/js/knn-predictive-algorithm.js"></script>
                <script type="text/javascript" src="wwwroot/js/jiang-algorithm.js"></script>
		<script>
			var layoutFrame = document.getElementById("layout-frame");
			var firstFloor = document.getElementById("first-floor");
			var navDot = document.getElementById("nav-dot");
			var blueDot = document.getElementById("blue-dot");
			var whiteDot = document.getElementById("white-dot");
			var navTriangle = document.getElementById("nav-triangle");
			var lightDot = document.getElementById("light-dot");
			var placeIcon = document.getElementById("place-icon");
			var placeCircle = document.getElementById("place-circle");
			var routingPathCanvas = document.getElementById("routing-path-canvas");
			var errorNotInRange = document.getElementById("error-not-in-range");
			var errorNotInRangeText = document.getElementById("error-not-in-range-text");
			var width = window.innerWidth;
			var height = window.innerHeight;
			var centerX = width/2 - 25;
			var centerY = height/2 - 25;
			var mapCenterX;
			var mapCenterY;

			navDot.style.left = centerX + "px";
			navDot.style.top = centerY + "px";

			errorNotInRangeText.style.width = width + "px";
			errorNotInRangeText.style.height = height + "px";

			layoutFrame.style.width = Math.sqrt(width*width + height*height) + "px";
			layoutFrame.style.height = Math.sqrt(width*width + height*height) + "px";

			firstFloor.style.width = "1920px";

			/* Set Current Position Function */
			/* Positions the background floor plan map on the screen such that the input x and y postion is in the center of the screen. */
			function setCurrentPos(x, y) {
				mapCenterX = x;
				mapCenterY = y;
				var m_x = -7.515625;
				var m_y = 7.480916;
				var b_x = 926.3125;
				var b_y = -692.480916;
				var posX = m_x*x + b_x;
				var posY = m_y*y + b_y;
				posX += -1*(1920/2 - width/2);
				posY += -1*(944/2 - height/2);
				firstFloor.style.left = posX + "px";
				firstFloor.style.top = posY + "px";
			}

			/* Get Feet To Pixels Function */
			/* Converts input number of feet to number of pixels */
			function getFeetToPixels(feet) {
				var x = 0;
				var m_x = -7.515625;
				var b_x = 926.3125;
				var convX = m_x*x + b_x;
				convX += -1*(1920/2 - width/2);

				var x2 = feet;
				var m_x2 = -7.515625;
				var b_x2 = 926.3125;
				var convX2 = m_x2*x2 + b_x2;
				convX2 += -1*(1920/2 - width/2);

				var pixels = Math.abs(convX2 - convX);
				return pixels;
			}

			/* Get Fingerprinting Input Function */
			/* Converts the input beacons array to a slightly different array that can be compared to the fingerprinting data */
			function getFingerprintingInput(beacons) {
				var fingerprinting_input = [null, null];
				var rssi;
				var name;
				var beacon_rssi_vals = [];
				var index;
				for (var beacon of beacons) {
					rssi = beacon[3];
					name = beacon[6];
					beacon_rssi_vals.push(name, rssi);
				}
				for (var name of beacon_names) {
					rssi = -1000;
					index = beacon_rssi_vals.indexOf(name);
					if (index != -1) {
						rssi = beacon_rssi_vals[index+1];
					}
					fingerprinting_input.push(rssi);
				}
				return fingerprinting_input;
			}

			/* Get Beacons Function */
			/* Gets beacon data for beacons that are in range of the user */
			function getBeacons(device) {
				var xhttp;
				xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var beacons = JSON.parse(this.responseText);
						beacons.sort(function(a,b) {
							return a[3] - b[3];
						});
						if (beacons.length > 3) {
							//beacons = beacons.slice(0, 3);
						}
						//console.log(beacons);
						var input_rssi = getFingerprintingInput(beacons);
						var threshold;
						var k;
						var fingerprinting;
						var knn_position;
						var pos;
                                                
						if (beacons.length > 2){
							pos = getWeightedPosition(beacons);
						}
                                                /* 
						if (beacons.length > 2) {

							// KNN Predicting Position 
							threshold = -85;
							k = determine_K();
							fingerprinting = rssi_fingerprinting.slice(1);
							fingerprinting = filter_rssi_fingerprinting(fingerprinting, threshold);
							input_rssi = filter_input_rssi(input_rssi, threshold);
							knn_position = predict_classification(fingerprinting, input_rssi, k);
							//console.log("\nKNN Position: " + knn_position + "\n\n");
							pos = knn_position;
					         }
                                                */
						else {
							pos = getPosition(beacons);
						}
						 


						
						//console.log("\nPosition: " + pos + "\n\n\n\n\n\n\n\n\n");
						
						// No simulations (normal)
						if (!routing_simulation_1 && !routing_simulation_2) {
							if (pos[0] == "Error") {
								document.getElementById("direction-icon").style.display = "none";
								document.getElementById("nav-assistance-text").innerHTML = pos[1];
								document.getElementById("error-not-in-range-text").innerHTML = pos[1];
								document.getElementById("error-not-in-range").style.display = "block";
							}
							else {
								document.getElementById("direction-icon").style.display = "inline";
								document.getElementById("error-not-in-range-text").innerHTML = "";
								document.getElementById("error-not-in-range").style.display = "none";
								pos = [parseFloat(pos[0]), parseFloat(pos[1])];
								updateLocation(pos);
							}
						}

						// Simulation 1
						else if (routing_simulation_1) {
							document.getElementById("direction-icon").style.display = "inline";
							document.getElementById("error-not-in-range-text").innerHTML = "";
							document.getElementById("error-not-in-range").style.display = "none";
							var temp_pos = [46, 84];
							updateLocation(temp_pos);
							var x_int;
							var y_int;
							var inc = 1;
							setInterval(function() {
								getOrientation(device);
								if (temp_pos[1] < 117 && temp_pos[0] == 46) {
									x_int = 0;
									y_int = inc;
								}
								else if (temp_pos[1] == 117 && temp_pos[0] < 195) {
									x_int = inc;
									y_int = 0;
								}
								else if (temp_pos[1] > 84 && temp_pos[0] == 195) {
									x_int = 0;
									y_int = -1*inc;
								}
								else if (temp_pos[1] == 84 && temp_pos[0] > 46) {
									x_int = -1*inc;
									y_int = 0;
								}
								else {
									x_int = 0;
									y_int = 0;
								}
								temp_pos[0] += x_int;
								temp_pos[1] += y_int;
								updateLocation(temp_pos);
							}, 200);
						}

						// Simulation 2
						else {
							document.getElementById("direction-icon").style.display = "inline";
							document.getElementById("error-not-in-range-text").innerHTML = "";
							document.getElementById("error-not-in-range").style.display = "none";
							var temp_pos = [160, 105];
							updateLocation(temp_pos);
							var x_int;
							var y_int;
							var inc = 0.5;
							setInterval(function() {
								getOrientation(device);
								if (temp_pos[1] > 84 && temp_pos[0] == 160) {
									x_int = 0;
									y_int = -1*inc;
								}
								else if (temp_pos[1] == 84 && temp_pos[0] < 195) {
									x_int = inc;
									y_int = 0;
								}
								else if (temp_pos[1] < 115 && temp_pos[0] == 195) {
									x_int = 0;
									y_int = inc;
								}
								else if (temp_pos[1] < 131 && temp_pos[0] < 200) {
									x_int = 0.20*inc;
									y_int = inc;
								}
								else {
									x_int = 0;
									y_int = 0;
								}
								temp_pos[0] += x_int;
								temp_pos[1] += y_int;
								updateLocation(temp_pos);
							}, 100);
						}
					}
				};
				xhttp.open("GET", "./get-beacons.php?device=" + device, true);
				xhttp.send();
			}

			var user_device = <?= json_encode($_GET["device"]); ?>;

			// Number of milliseconds to update the user's location on the screen
			var updateLocationInterval = 1000;

			// Number of milliseconds to update the user's orientation on the screen
			var updateOrientationInterval = 50;

			if (routing_simulation_1 || routing_simulation_2) {
				user_device = "device484052";
			}
			else {
				setInterval(function() {
					getBeacons(user_device);
				}, updateLocationInterval);
				setInterval(function() {
					getOrientation(user_device);
				}, updateOrientationInterval);
			}
			getBeacons(user_device);
			getOrientation(user_device);


			/* Get Orientation Function */
			/* Gets the user's current orientation from the database */
			function getOrientation(device) {
				var xhttp;
				xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						var orientation = parseInt(JSON.parse(this.responseText));
						//console.log("\n\n\nOrientation: " + orientation + "\n\n\n");
						updateOrientation(orientation);
					}
				};
				xhttp.open("GET", "./get-orientation.php?device=" + device, true);
				xhttp.send();
			}

			/* Update Orientation Function */
			/* Updates the user's current orientation on the screen by rotating the map accordingly */
			function updateOrientation(orientation) {
				orientation *= -1;
				orientation += 77;
				document.getElementById("map-rotation").style.transform = "rotate(" + orientation + "deg)";
				document.getElementById("exit-icon").style.transform = "rotate(" + (-1*orientation) + "deg)";
			}

			/* Update Location Function */
			/* Updates the user's location on the screen */
			function updateLocation(pos) {
				var x = pos[0].toFixed(6);
				var y = pos[1].toFixed(6);
				var closest_path_position = getClosestPathPosition([x, y]);
				var needNewNode = closest_path_position[4];
				if (needNewNode) {
					pos = closest_path_position[0];
					var route_number = closest_path_position[1];
					var node_a = closest_path_position[2];
					var node_b = closest_path_position[3];
					addNodeBetween(pos, route_number, node_a, node_b);
				}
				else {
					create_graph();
				}
				x = pos[0].toFixed(2);
				y = pos[1].toFixed(2);
				setCurrentPos(x, y);
				var closest_node = getClosestNode(x, y);
				calc_closest_exit(closest_node);
			}

			/* Get Closest Node Function */
			/* Returns the node that is closest to the input x and y position */
			function getClosestNode(x, y) {
				var node;
				var _nodes;
				if (simple_routing) {
					node = simple_nodes[0][0];
					_nodes = simple_nodes;
				}
				else {
					node = nodes[0][0];
					_nodes = nodes;
				}
				var pCurr = [x, y];
				var pNode = [];
				var minDist = Number.MAX_VALUE;
				var dist = 0;
				for (var node_ of _nodes) {
					pNode = [node_[1], node_[2]];
					dist = distance(pCurr, pNode);
					if (dist < minDist) {
						minDist = dist;
						node = node_[0];
					}
				}
				return node;
			}

			/* Distance Function */
			/* Returns the distance between two input positions */
			function distance(p1, p2) {
				var x1 = p1[0];
				var y1 = p1[1];
				var x2 = p2[0];
				var y2 = p2[1];
				var xDiff = x2 - x1;
				var yDiff = y2 - y1;
				var dist = Math.sqrt(xDiff*xDiff + yDiff*yDiff);
				return dist;
			}

			/* Get Routing Path Function */
			/* Displays the user's safest evacuation route on the screen */
			function getRoutingPath(path_nodes, closest_exit) {
				var node_positions = simple_nodes;
				if (!simple_routing) {
					node_positions = nodes;
				}
				routingPathCanvas.width = window.innerWidth*2;
				routingPathCanvas.height = window.innerHeight*2;
				routingPathCanvas.style.left = -1*window.innerWidth/2 + "px";
				routingPathCanvas.style.top = -1*window.innerHeight/2 + "px";
				var adjustment_x = window.innerWidth/2;
				var adjustment_y = window.innerHeight/2;


				var pathBorder = routingPathCanvas.getContext("2d");
				pathBorder.lineWidth = 11;
				pathBorder.strokeStyle = "#195fcf";
				pathBorder.globalAlpha = 1.0;
				pathBorder.beginPath();
				var currX = width;
				var currY = height;
				pathBorder.moveTo(currX, currY);
				var node_pos;
				var node_x_pos;
				var node_y_pos;
				var node_x_adj;
				var node_y_adj;
				var exit_x;
				var exit_y;
				var canvas_pos;
				for (var node_ of path_nodes) {
					node_pos = node_positions[node_];
					node_x_pos = node_pos[1];
					node_y_pos = node_pos[2];
					canvas_pos = getCanvasPos(node_x_pos, node_y_pos);
					node_x_adj = canvas_pos[0];
					node_y_adj = canvas_pos[1];
					if (node_ == closest_exit) {
						exit_x = node_x_adj;
						exit_y = node_y_adj;
					}
					pathBorder.lineTo(node_x_adj + adjustment_x, node_y_adj + adjustment_y);
				}
				pathBorder.stroke();



				var path = routingPathCanvas.getContext("2d");
				path.lineWidth = 7;
				path.strokeStyle = "#00a5ff";
				path.globalAlpha = 1.0;
				path.beginPath();
				var currX = width;
				var currY = height;
				path.moveTo(currX, currY);
				var node_pos;
				var node_x_pos;
				var node_y_pos;
				var node_x_adj;
				var node_y_adj;
				var exit_x;
				var exit_y;
				var canvas_pos;
				for (var node_ of path_nodes) {
					node_pos = node_positions[node_];
					node_x_pos = node_pos[1];
					node_y_pos = node_pos[2];
					canvas_pos = getCanvasPos(node_x_pos, node_y_pos);
					node_x_adj = canvas_pos[0];
					node_y_adj = canvas_pos[1];
					if (node_ == closest_exit) {
						exit_x = node_x_adj;
						exit_y = node_y_adj;
					}
					path.lineTo(node_x_adj + adjustment_x, node_y_adj + adjustment_y);
				}
				path.stroke();


				placeIcon.style.left = (exit_x-23.5) + "px";
				placeIcon.style.top = (exit_y-36) + "px";
				placeCircle.style.left = (exit_x-9) + "px";
				placeCircle.style.top = (exit_y-26) + "px";


				document.getElementById("exit-icon").style.transformOrigin = (exit_x) + "px " + (exit_y) +  "px";
			}

			/* Get Canvas Position Function */
			/* Returns the position of the drawing canvas, which is what is used to draw the evacuation route */
			function getCanvasPos(map_x, map_y) {
				var canvas_pos = [];
				var m_x = getFeetToPixels(1);
				var m_y = -1*m_x;
				var b_x = width/2 - mapCenterX*m_x;
				var b_y = height/2 - mapCenterY*m_y;
				var x = m_x*map_x + b_x;
				var y = m_y*map_y + b_y;
				canvas_pos = [x, y];
				return canvas_pos;
			}

			/* Get Closest Path Position Function */
			/* Determines which route the user is closest to */
			/* Returns an array containing the position on the route the user is closest to, route number, end nodes, and if a new node should be inserted */
			function getClosestPathPosition(curr_pos) {
				originalArrays();
				var routes_arr = [];
				if (simple_routing) {
					routes_arr = simple_routes_and_nodes;
				}
				else {
					routes_arr = routes_and_nodes;
				}
				var path = [];
				var point = [];
				var first_path = routes_arr[0];
				var first_closest_point = closestPointOnPath(curr_pos, first_path[1], first_path[2]);
				var path_pos = first_closest_point[0];
				var route_number = first_path[0];
				var node_a = first_path[1];
				var node_b = first_path[2];
				var d = first_closest_point[1];
				var needNewNode = first_closest_point[2];
				for (var i=1; i<routes_arr.length; i++) {
					path = routes_arr[i];
					point = closestPointOnPath(curr_pos, path[1], path[2]);
					if (point[1] < d) {
						path_pos = point[0];
						route_number = path[0];
						node_a = path[1];
						node_b = path[2];
						d = point[1];
						needNewNode = point[2];
					}
				}
				return [path_pos, route_number, node_a, node_b, needNewNode];
			}

			/* Closest Point On Path Function */
			/* Determines which point on a route a specific point is closest to */
			function closestPointOnPath(p, node_a, node_b) {
				var closest_point = [];
				var d = 0;
				var nodes_arr = [];
				if (simple_routing) {
					nodes_arr = simple_nodes;
				}
				else {
					nodes_arr = nodes;
				}
				var p_x = p[0];
				var p_y = p[1];
				var a_x = nodes_arr[node_a][1];
				var a_y = nodes_arr[node_a][2];
				var b_x = nodes_arr[node_b][1];
				var b_y = nodes_arr[node_b][2];
				if (b_x == a_x) {
					b_x = a_x + 0.000001;
				}
				var path_slope = (b_y - a_y)/(b_x - a_x);
				if (path_slope == 0) {
					path_slope = 0.0000001;
				}
				var perp_slope = -1/path_slope;
				var m1 = path_slope;
				var b1 = a_y - m1*a_x;
				var m2 = perp_slope;
				var b2 = p_y - m2*p_x;
				var intersection = twoLinesIntersection(m1, b1, m2, b2);
				var int_x = intersection[0];
				var int_y = intersection[1];
				var perpendicular = true;
				var needNewNode = false;
				var path_x_lower;
				var path_x_higher;
				if (a_x < b_x) {
					path_x_lower = a_x;
					path_x_higher = b_x;
				}
				else {
					path_x_lower = b_x;
					path_x_higher = a_x;
				}
				if (int_x < path_x_lower || int_x > path_x_higher) {
					perpendicular = false;
				}
				if (perpendicular) {
					closest_point = [int_x, int_y];
					d = distance(p, closest_point);
					needNewNode = true;
				}
				else {
					var d_a = distance(p, [a_x, a_y]);
					var d_b = distance(p, [b_x, b_y]);
					if (d_a < d_b) {
						closest_point = node_a;
						d = d_a;
					}
					else {
						closest_point = node_b;
						d = d_b;
					}
					needNewNode = false;
				}
				return [closest_point, d, needNewNode];
			}

			/* Two Lines Intersect Function */
			/* Returns the x and y position where two lines intersect */
			/* The input lines are given in slope-intercept form */
			function twoLinesIntersection(m1, b1, m2, b2) {
				if (m1 == m2) {
					m1 = m2 + 0.000001;
				}
				var x = (b2 - b1)/(m1 - m2);
				var y = m1*x + b1;
				return [x, y];
			}

			/* Add Node Between Function */
			/* Inserts a new node on a route between the two end nodes */
			function addNodeBetween(p, route, node_a, node_b) {
				var p_x = p[0].toFixed(2);
				var p_y = p[1].toFixed(2);
				var len1;
				var len2;
				var x1;
				var y1;
				var x2;
				var y2;
				var d;
				var nodes_arr = [];
				if (simple_routing) {
					nodes_arr = simple_nodes;
				}
				else {
					nodes_arr = nodes;
				}
				if (simple_routing) {
					len1 = simple_nodes.length;
					len2 = simple_routes_and_nodes.length;
				}
				else {
					len1 = nodes.length;
					len2 = routes_and_nodes.length;
				}
				if (simple_routing) {
					simple_nodes.push([len1, p_x, p_y]);
					simple_routes_and_nodes[route-1][2] = len1;
					simple_routes_and_nodes.push([len2+1, node_b, len1]);
					x1 = nodes_arr[node_a][1];
					y1 = nodes_arr[node_a][2];
					x2 = nodes_arr[len1][1];
					y2 = nodes_arr[len1][2];
					d = distance([x1, y1], [x2, y2]);
					simple_routing_table[route-1][1] = d.toFixed(2);
					x1 = nodes_arr[node_b][1];
					y1 = nodes_arr[node_b][2];
					x2 = nodes_arr[len1][1];
					y2 = nodes_arr[len1][2];
					d = distance([x1, y1], [x2, y2]);
					simple_routing_table.push([len2-1, d.toFixed(2)]);
					num_simple_nodes++;
					num_simple_routes++;
				}
				else {
					nodes.push([len1, p_x, p_y]);
					routes_and_nodes[route-1][2] = len1;
					routes_and_nodes.push([len2+1, node_b, len1]);
					x1 = nodes_arr[node_a][1];
					y1 = nodes_arr[node_a][2];
					x2 = nodes_arr[len1][1];
					y2 = nodes_arr[len1][2];
					d = distance([x1, y1], [x2, y2]);
					routing_table[route][1] = d.toFixed(2);
					x1 = nodes_arr[node_b][1];
					y1 = nodes_arr[node_b][2];
					x2 = nodes_arr[len1][1];
					y2 = nodes_arr[len1][2];
					d = distance([x1, y1], [x2, y2]);
					routing_table.push([len2+1, d.toFixed(2)]);
					num_nodes++;
					num_routes++;
				}
				create_graph();
			}

			/* Get Path Angles Function */
			/* Returns an array of angles between each pair of consecutive routes along the user's evacuation path */
			function getPathAngles(path_nodes) {
				var path_angles = [];
				var len = path_nodes.length;
				var node_1;
				var node_2;
				var node_3;
				var angle;
				if (path_nodes.length >= 3) {
					for (var i=1; i<len-1; i++) {
						node_1 = path_nodes[i-1];
						node_2 = path_nodes[i];
						node_3 = path_nodes[i+1];
						angle = parseFloat(getPathAngle(node_1, node_2, node_3));
						path_angles.push(angle);
					}
				}
				else {
					path_angles = [0];
				}
				return path_angles;
			}

			/* Get Path Angle Function */
			/* Returns the angle that the user will have to turn when two consecutive routes intersect at a node */
			/* The two consecutive routes are specified by three input nodes that connect the two routes */
			function getPathAngle(node_1, node_2, node_3) {
				var angle = 0;
				var x_1 = 0;
				var y_1 = 0;
				var x_2 = 0;
				var y_2 = 0;
				var x_3 = 0;
				var y_3 = 0;
				var path_1_x = 0;
				var path_1_y = 0;
				var path_2_x = 0;
				var path_2_y = 0;
				var path_1_angle = 0;
				var path_2_angle = 0;
				var nodes_arr = [];
				if (simple_routing) {
					nodes_arr = simple_nodes;
				}
				else {
					nodes_arr = nodes;
				}
				x_1 = nodes_arr[node_1][1];
				y_1 = nodes_arr[node_1][2];
				x_2 = nodes_arr[node_2][1];
				y_2 = nodes_arr[node_2][2];
				x_3 = nodes_arr[node_3][1];
				y_3 = nodes_arr[node_3][2];
				path_1_x = (x_2 - x_1).toFixed(4);
				path_1_y = (y_2 - y_1).toFixed(4);
				path_2_x = (x_3 - x_2).toFixed(4);
				path_2_y = (y_3 - y_2).toFixed(4);
				if (distance([0, 0], [path_1_x, path_1_y]) < 1) {
					return 0.01;
				}
				if (path_1_x == 0) {
					path_1_x = 0.00001;
				}
				if (path_2_x == 0) {
					path_2_x = 0.00001;
				}
				path_1_angle = parseFloat(rad_to_deg(Math.atan(path_1_y/path_1_x)).toFixed(2));
				path_2_angle = parseFloat(rad_to_deg(Math.atan(path_2_y/path_2_x)).toFixed(2));
				if (path_1_x < 0) {
					path_1_angle += 180;
				}
				if (path_2_x < 0) {
					path_2_angle += 180;
				}
				if (path_1_angle > 180) {
					path_1_angle -= 360;
				}
				else if (path_1_angle < -180) {
					path_1_angle += 360;
				}
				if (path_2_angle > 180) {
					path_2_angle -= 360;
				}
				else if (path_2_angle < -180) {
					path_2_angle += 360;
				}
				angle = path_2_angle - path_1_angle;
				if (angle > 180) {
					angle -= 360;
				}
				else if (angle < -180) {
					angle += 360;
				}
				return angle.toFixed(2);
			}

			/* Radians to Degrees Function */
			/* Converts input angle in radians to angle in degrees */
			function rad_to_deg(rad) {
				var pi = Math.PI;
				var deg = (rad*180/pi);
				return deg;
			}
			

		</script>
		<!-- END Scripts -->

	</body>
	<!-- END Body -->

</html>
<!-- END HTML -->












