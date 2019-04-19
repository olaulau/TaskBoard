<?php
require_once __DIR__ . '/vendor/autoload.php';

$f3 = \Base::instance();
$db = new DB\SQL('sqlite:./api/taskboard.db');

$sql = '
	SELECT l.name AS colonne, title AS titre, description, due_date as délai
	FROM item i
	INNER JOIN lane l ON i.lane_id = l.id';
$data = $db->exec($sql);

$data2 = [];
foreach ($data as $id => $row) {
	if (!empty ($row['délai'])) {
		$date = DateTime::createFromFormat('m/d/Y', $row['délai']);
		$row['délai'] = $date->format('d/m/Y');
		$real_date = $date->format('Y-m-d');
		$data[$real_date] = $row;
	}
	unset($data[$id]);
	
	if (!empty ($row['délai'])) {
		unset($row['délai']);
	if (!isset ($data2[$real_date])) {
		$data2[$real_date] = [];
	}
	$data2[$real_date][] = $row;
	}
}
ksort($data);
ksort($data2);

$row = reset($data2);
$row = reset($row);
$titles = array_keys($row);
$nb_titles = count ($titles);

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
				<?php
				foreach ($titles as $title) {
					?>
					<th><?= $title ?></th>
					<?php
				}
				?>
			</tr>
			</thead>
			<?php
			foreach ($data2 as $date => $data) {
				?>
				<tr><td colspan="<?= $nb_titles ?>"><h3><?= $date ?></h3></td></tr>
				<?php
				
				foreach ($data as $row) {
				?>
				<tr>
					<?php
					foreach ($row as $col) {
						?>
						<td><?= $col ?></td>
						<?php
					}
					?>
				</tr>
				<?php
				}
			}
			
			
			?>
		</table>
	</body>
</html>
