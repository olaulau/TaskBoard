<?php
require_once __DIR__ . '/vendor/autoload.php';

$f3 = \Base::instance();
$db = new DB\SQL('sqlite:./api/taskboard.db');

$sql = '
	SELECT l.name AS étape, title AS titre, description, due_date as délai
	FROM item i
	INNER JOIN lane l ON i.lane_id = l.id';
$data = $db->exec($sql);

$data2 = [];
foreach ($data as $id => $row) {
	if (!empty ($row['délai'])) {
		$date = DateTime::createFromFormat('m/d/Y', $row['délai']);
		$real_date = $date->format('Y-m-d');
		unset($row['délai']);
    	if (!isset ($data2[$real_date])) {
    		$data2[$real_date] = [];
    	}
    	$data2[$real_date][] = $row;
	}
}
ksort($data2);

$months = [
    'en' => [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'Jully',
        'August',
        'September',
        'October',
        'November',
        'December',
    ],
    'fr' => [
        'Janvier',
        'Février',
        'Mars',
        'Avril',
        'Mai',
        'Juin',
        'Juillet',
        'Août',
        'Septembre',
        'Novembre',
        'Décembre',
    ]
];

$days_of_week = [
    'en' => [
        'Sunday',
        'Monday',
        'Thuesday',
        'Wednesday',
        'Thusday',
        'Friday',
        'Saturday',
    ],
    'fr' => [
        'Dimanche',
        'Lundi',
        'Mardi',
        'Mercredi',
        'Jeudi',
        'Vendredi',
        'Samedi',
    ]
];

?>

<html>
	<head>
		<title>Planning par ordre de délai</title>
		 <link rel="stylesheet" href="lib/css/bootstrap.min.css">
	</head>
	<body>
		<h1>Planning par ordre de délai</h1>
		<table class="table table-bordered">
			<thead>
			<tr>
				<th>étape</th>
				<th>titre</th>
				<th>description</th>
			</tr>
			</thead>
			<?php
			foreach ($data2 as $date => $data) {
			    $date = DateTime::createFromFormat('Y-m-d', $date);
			    $date = $date->format('l d F Y');
			    $date = str_replace($months['en'], $months['fr'], $date);
			    $date = str_replace($days_of_week['en'], $days_of_week['fr'], $date);
				?>
				<tr><td colspan="3"><h3><?= $date ?></h3></td></tr>
				<?php
				foreach ($data as $row) {
				?>
				<tr>
					<td><?= $row['étape'] ?></td>
					<td><?= $row['titre'] ?></td>
					<td><?= $row['description'] ?></td>
				</tr>
				<?php
				}
			}
			
			?>
		</table>
	</body>
</html>
