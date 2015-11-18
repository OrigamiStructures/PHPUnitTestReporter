<!DOCTYPE html>
<?php
//  Sample commands
//  To generate coverage reports as an html site
//  vendor/bin/phpunit --coverage-html /Users/dondrake/Sites/ft/coverage.html plugins
//  
//  to generate the tap and json files to drive this page
//  vendor/bin/phpunit --log-tap /Users/dondrake/Sites/ft/data/tap.txt --log-json /Users/dondrake/Sites/ft/data/json.txt plugins 
  
require '/Library/WebServer/Documents/foldingTime/vendor/autoload.php';

//$cmd = '/Library/WebServer/Documents/foldingTime/vendor/bin/phpunit --log-tap /Users/dondrake/Sites/ft/data/tap.txt --log-json /Users/dondrake/Sites/ft/data/json.txt /Library/WebServer/Documents/foldingTime/plugins';

$cmd = "cd /Library/WebServer/Documents/foldingTime/\n"
		. "vendor/bin/phpunit --coverage-html /Users/dondrake/Sites/ft/coverage plugins";
$shell_output = shell_exec($cmd);
//var_dump($shell_output);


header("location: coverage/");
exit();