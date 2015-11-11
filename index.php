<!DOCTYPE html>
<?php
//$cmd = '/Library/WebServer/Documents/foldingTime/vendor/bin/phpunit --verbose --log-tap /Users/dondrake/Sites/ft/tap.txt --log-json /Users/dondrake/Sites/ft/json.txt plugins';
// --testdox-html /Users/dondrake/Sites/ft/test.html		
// --log-junit /Users/dondrake/Sites/ft/junit.txt
include_once 'helper/TapHelper.php';

//$shell_output = shell_exec($cmd);
$tap = new TapHelper();
//var_dump($tap->result);
//var_dump($tap->res);
?>
<html>
	<head>
		<title>Unit Test Reoprt</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/json.js"></script>
		<script type="text/javascript" src="js/app.js"></script>
<!--		<script type="text/javascript" src="js/process.js"></script>-->
		<link href="css/tap.css" type="text/css" rel="stylesheet">
	</head>
	<body>
		<?php foreach ($tap->result as $script => $functions) : ?>
		
		<h1 class="script"><?= $script ?></h1>
		
			<?php 
			foreach ($functions as $function => $tests) : 
				$uuid = uniqid();
			?>
		
				<h2 id="<?= $uuid; ?>" class="function <?= "{$tap->functionStatus($tests)} toggle"; ?>">
					<?= "$function ({$tap->functionTime($tests)} seconds for {$tap->countTests($tests)} tests)"; ?>
				</h2>
				<table>
					<tbody>
						
						<?php 
						foreach ($tests as $index => $test) : 
							$row_class = ($tap->testStatus($test) === 'ok') ? "ok $uuid" : 'notok';
							$row_span = $row_class === 'notok' ? '3' : '2';
						?>
						
						<tr class="<?= $row_class; ?>">
							<td class="index" rowspan="<?= $row_span; ?>"><?= $index; ?></td>
							<td><?= $tap->testTime($test); ?></td>
						</tr>
						<tr class="<?= $row_class; ?>">
							<td><?= $tap->testData($test); ?></td>
						</tr>
						<?php if ($row_class === 'notok') : ?>
						<tr class="outcome">
							<td><pre><?= $tap->testOutcome($test); ?></pre></td>
						</tr>
						<?php endif; ?>
						
						<?php endforeach; ?>
						
					</tbody>
				</table>
				
		
			<?php endforeach; ?>
		<?php endforeach; ?>
		<?php // echo '<pre>' . $shell_output . '</pre>'; ?>
	</body>
</html>
<?php
