<?php
$response = json_decode($list, true);
echo '<h2>'.$field.'</h2>';
if(!empty($response['items'])) {
	echo '<table class="table"><tr><th>Value</th><th>Text</th></tr>';
	foreach($response['items'] as $item) {
		if(!empty($item['children'])) {
			echo '<tr><td><b>'.$item['id'].'</b></td><td><b>'.$item['text'].'</b></td></tr>';
		} else {
			echo '<tr><td>'.$item['id'].'</td><td>'.$item['text'].'</td></tr>';
		}
	}
	echo '</table>';
} else {
	echo '<h3>No option available</h3>';
}

?>	
	