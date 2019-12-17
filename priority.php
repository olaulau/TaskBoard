<?php
require_once __DIR__ . '/vendor/autoload.php';

$f3 = \Base::instance();
$db = new DB\SQL('sqlite:./api/taskboard.db');

$sql = '
	SELECT		l.name AS étape, title AS titre, description, due_date as délai
	FROM		item i
	INNER JOIN	lane l ON i.lane_id = l.id
	ORDER BY	due_date ASC, l.position ASC
';
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
        'July',
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
        'Octobre',
        'Novembre',
        'Décembre',
    ]
];

$days_of_week = [
    'en' => [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
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
		<meta http-equiv="refresh" content="60" >
		<link rel="stylesheet" href="lib/css/bootstrap.min.css">
		<style>
            .table > thead > tr > td.danger, .table > tbody > tr > td.danger, .table > tfoot > tr > td.danger, .table > thead > tr > th.danger, .table > tbody > tr > th.danger, .table > tfoot > tr > th.danger, .table > thead > tr.danger > td, .table > tbody > tr.danger > td, .table > tfoot > tr.danger > td, .table > thead > tr.danger > th, .table > tbody > tr.danger > th, .table > tfoot > tr.danger > th {
                background-color: #ff6666;
            }
            .table > thead > tr > td.warning, .table > tbody > tr > td.warning, .table > tfoot > tr > td.warning, .table > thead > tr > th.warning, .table > tbody > tr > th.warning, .table > tfoot > tr > th.warning, .table > thead > tr.warning > td, .table > tbody > tr.warning > td, .table > tfoot > tr.warning > td, .table > thead > tr.warning > th, .table > tbody > tr.warning > th, .table > tfoot > tr.warning > th {
                background-color: #f2e18c;
            }
            
            .table > thead > tr > td.success, .table > tbody > tr > td.success, .table > tfoot > tr > td.success, .table > thead > tr > th.success, .table > tbody > tr > th.success, .table > tfoot > tr > th.success, .table > thead > tr.success > td, .table > tbody > tr.success > td, .table > tfoot > tr.success > td, .table > thead > tr.success > th, .table > tbody > tr.success > th, .table > tfoot > tr.success > th {
                background-color: #b3dba3;
            }
            h2 {
                font-weight: bold;
                text-align: center;
            }
        </style> 
	</head>
	<body>
		<h1>Planning par ordre de délai</h1>
		<table class="table table-bordered">
			<thead>
			<tr>
				<th class="col-md-2">étape</th>
				<th class="col-md-4">titre</th>
				<th class="col-md-6">description</th>
			</tr>
			</thead>
			<?php
			$today = new DateTime();
			$today_found = false;
			foreach ($data2 as $date => $data) {
			    $date = DateTime::createFromFormat('Y-m-d', $date);
			    $diff = $today->diff($date);
			    $date_class = 'success';
			    if ($diff->invert === 1) {
			        $date_class = 'danger';
			    }
			    elseif ($diff->format("%a") === "0") {
			        $date_class = 'danger';
			    }
			    elseif ($diff->format("%a") <= 7) {
			        $date_class = 'warning';
			    }
			    else {
			        $date_class = 'success';
			    }
			    
			    $date = $date->format('l d F Y');
			    $date = str_replace($months['en'], $months['fr'], $date);
			    $date = str_replace($days_of_week['en'], $days_of_week['fr'], $date);
			    
			    if ((!$today_found && $diff->format("%R") === "+") || ($diff->format("%a") === "0")) {
					$today_found = true;
			        ?>
			        <tr id="today" class="<?= $date_class ?>"><td colspan="3"><h2><?= $date ?> (aujourd'hui)</h2></td></tr>
			        <?php
			    }
			    else {
			    	$s = $diff->format("%a") > 1 ? 's' : '';
			    	$qualificatif = $diff->format("%R") === "+" ? "restant$s" : "de retard";
			        ?>
			        <tr id="" class="<?= $date_class ?>"><td colspan="3"><h3><?= $date ?> (<?= $diff->format("%a") ?> jour<?=$s?> <?=$qualificatif?>)</h3></td></tr>
			        <?php
			    }
				?>
				<?php
				foreach ($data as $row) {
    				?>
    				<tr>
    					<td><?= $row['étape'] ?></td>
    					<td><?= $row['titre'] ?></td>
    					<td><?= nl2br(trim(str_replace("\n\n", "\n", $row['description']))) ?></td>
    				</tr>
    				<?php
				}
			}
			
			?>
		</table>
	</body>
</html>
