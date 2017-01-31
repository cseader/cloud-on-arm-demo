<?php
$set = 2;

if (isset($_REQUEST["set"])) {
    $set = intval($_REQUEST["set"]);
}

$myfile = fopen("demo-left.json", "r") or die("Unable to open input file!");
$myoutfile = fopen("demo-left.json.new", "w") or die("Unable to open output file!");
$myphpfile = fopen("demo-left.json.php.new", "w") or die("Unable to open output file!");
# Write php header
fputs($myphpfile, '<?php header("Access-Control-Allow-Origin: * "); ?>');
# Read { line
$line = fgets($myfile);
# Write { line
fputs($myoutfile, $line);
fputs($myphpfile, $line);
# Read first data line
$line = fgets($myfile);
$counter = 0;
$lastline = "";
# Read other data lines and copy them
while (($line = fgets($myfile)) !== false) {
  # "17:03:15.042":5

  $tline = trim($line);

  if ($tline == "}")
    break;

  $lastline = $tline;

  if (substr($tline, -1) != ",")
    $line = $tline . ",\n";

  fputs($myoutfile, $line);
  fputs($myphpfile, $line);
}

list($foo, $datestr, $counter) = explode("\"", $lastline);
list($counter) = sscanf($counter, ':%d');

$counter = $set;
if ($counter < 0)
  $counter = 0;

if ($counter > 20)
  $counter = 20;

$usec = microtime(true);
$usec_str = sprintf('%.1f', $usec - floor($usec));
$usec_str = str_replace('0.', '', $usec_str);
$usec_str = str_replace('1.', '', $usec_str);

fputs($myoutfile, sprintf("\"%s\":%d\n", strftime("%T.") . $usec_str , $counter));
fputs($myoutfile, "}\n");

fputs($myphpfile, sprintf("\"%s\":%d\n", strftime("%T.") . $usec_str , $counter));
fputs($myphpfile, "}\n");

fclose($myfile);
fclose($myoutfile);
fclose($myphpfile);

rename("demo-left.json.new", "demo-left.json");
rename("demo-left.json.php.new", "demo-left.json.php");

?>
