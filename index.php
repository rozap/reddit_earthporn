<html lang="en">
	<head>
		<link href="bootstrap/style.css" rel="stylesheet">
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script type="text/javascript" src="/bootstrap/js/bootstrap-dropdown.js"></script>
		<script type="text/javascript" src="/bootstrap/js/bootstrap-modal.js"></script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD63j6rFiV7jvifpnbFIywkrJwpR9bVBYw&sensor=false"></script>
		<script type="text/javascript">
			var overlays = new Array();
			var map;
			var markerWindow;

			function about() {
				$('#about_modal').modal({})
			}

			function getContent(obj) {
				return '<div id="content" class="map_popup">' +
				'<h3>'+obj.title+'</h3>'+
				'<a href="'+obj.reddit+'">Reddit Link</a>' +
				'<img width="400" class="earth_porn_image" src="'+obj.image+'">'+
				'</div>';

			}

			function clearOverlays() {
				if (overlays) {
				    for (i in overlays) {
				      overlays[i].marker.setMap(null);
				    }
				}
				overlays.length = 0;
			}

			function getOlder() {
				clearOverlays();
				currentDate++;
				drawMap(currentDate);
			}

			function getNewer() {
				if(currentDate > 0) {
					clearOverlays();
					currentDate--;
					drawMap(currentDate);
				}
			}

			function resetMap() {
				clearOverlays();
				currentDate = 0;
				drawMap(currentDate);

			}

			

			function drawMap(date) {
				$.ajax({
					url : '/get_places.php?days_ago='+date,
					dataType : 'json'
				}).always(function(response) {
					markerWindow = new google.maps.InfoWindow();
					for(p in response) {
						obj = response[p];
						posn = new google.maps.LatLng(obj.lat, obj.lng);
						var marker = new google.maps.Marker({
							position : posn,
							title : obj.title
						});
						marker.setMap(map);
						obj.marker = marker;
						overlays.push(obj);
						
						google.maps.event.addListener(marker, 'click',  (function(marker, p) {
							return function() {
							  markerWindow.setContent(getContent(overlays[p]));
							  markerWindow.open(map, marker);
							}
						      })(marker, p))
					}
				});
			}

			$(document).ready(function() {
				var myOptions = {
				  center: new google.maps.LatLng(0, 0),
				  zoom: 2,
				  mapTypeId: google.maps.MapTypeId.ROADMAP
				};
				map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
				$('#map_canvas').height($('body').height()-$('.navbar').height());
				currentDate = 0;				
				drawMap(currentDate);
				


				$('.dropdown-toggle').dropdown();
			});


		</script>
	</head>

	<body>
		<div class="navbar">
			<div class="navbar-inner">
				<div class="container">
					<a href="/" class="brand">Earthporn Mapper</a>
					<ul class="nav">
					  <li class="dropdown" id="menu1">
					    <a class="dropdown-toggle" data-toggle="dropdown" href="#menu1">
					      Submission Time
					      <b class="caret"></b>
					    </a>
					    <ul class="dropdown-menu">
					      <li><a href="#" onclick="getOlder()">Older</a></li>
					      <li><a href="#" onclick="getNewer()">Newer</a></li>
					      <li class="divider"></li>
					      <li><a href="#" onclick="resetMap()">Reset</a></li>
					    </ul>
					  </li>
					  <li><a href="javascript:voi(0)" onclick="about()">About</a></li>
					</ul>
					
				</div>
			</div>
		</div>
		<div class='row-fluid'>
			<div class="span12" id="map_canvas">
				
			</div>
		</div>

		<div class="modal hide" id="about_modal">
		  <div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal">x</button>
		    <h3>About</h3>
		  </div>
		  <div class="modal-body">
		    <p>This was made by <a href="http://www.hiiamchris.com">Chris</a> because he
			wanted a better way of finding where all the earthporns live.</p>

			<h4>Why isn't my submission here?</h4>
			<p>I wrote this in a couple hours, and it only uses the google geocoding api to get a latitude and longitude
			for the places. Sometimes google can't figure out from the title where a place is, so that place doesn't
			get added to the map. If you want to put together a better solution, then feel free. The github is 
			<a href="https://github.com/chrisd1891/earthporn_mapper">here</a>
		  </div>
		</div>


	</body>


</html>
