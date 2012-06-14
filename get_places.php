<?php



	$db = mysql_connect('localhost', 'root', 'lolwut32') or die('Database Error');
	mysql_select_db('earth') or die ('Database Error');
	$query = "SELECT name, lat, lng, link, reddit FROM places";
	//Making sure it's an int will sanitize it - this wont
	//pass if someone passes in a string
	if(isset($_GET['days_ago']) && intval($_GET['days_ago']) != 0) {
		$ms_ago = 86400000 * intval($_GET['days_ago']);
		$query = $query . " WHERE time > " . (time() - $ms_ago);
	}
	
	$result = mysql_query($query);
	$results = array();
	while($row = mysql_fetch_array($result)) {
		$data = array(
			'title'   => $row[0],
			'lat'    => $row[1],
			'lng'    => $row[2],
			'image'  => $row[3],
			'reddit' => $row[4]);
		array_push($results, $data);
	}

	echo json_encode($results);





	mysql_close($db);


?>
