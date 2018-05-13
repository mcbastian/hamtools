#!/usr/bin/php
<?php
$logdir = "/var/log/mmdvm";
$dbstring = "sqlite:/home/pi/mmdvm.db";
$dbuser = null;
$dbpass = null;

$db = new PDO($dbstring, $dbuser, $dbpass);

$dir = scandir($logdir);
$totsec = 0.0;
foreach ($dir as $d)
{
    if (!in_array($d,array(".","..")))
    {
	if (strpos($d, "mmdvm-")!==false)
	{
	    echo "---------------- ".$d."\n";
	    $q = $db->prepare("SELECT * FROM files WHERE name = :name");
	    $q->execute(array(':name'=>$d));
	    $ar = $q->fetchAll();
	    // we've had this log before, continue with the next one in foreach-loop
	    if (count($ar)>0) continue;
	    
	    $f = fopen($logdir."/".$d,"r");
	    while (($line = fgets($f)) !== false) 
	    {
		//		echo $line;
		if (($sp = strpos($line, "transmission"))===false) continue;
		if (($sps = strpos($line, "seconds"))===false) continue;
		$sp+=14; $sps--;
		$secs = substr($line,$sp,$sps-$sp);
		$totsec+=$secs;
		$time = substr($line,3,19);
		$line = fgets($f); 
		if ($line === false) continue;
		if (($tp = strpos($line, "from "))===false) continue;
		if (($tps = strpos($line, " to "))===false) continue;
		$tp+=5;
		$call = substr($line, $tp, $tps-$tp);
		echo $time." ".$call." ".$secs."\n";
		$q = $db->prepare("INSERT INTO log (ts, call, duration) VALUES (:ts, :call, :dur)");
		$q->execute(array(':ts'=>$time, ':call'=>$call, ':dur'=>$secs));
	    }
	    $q = $db->prepare("INSERT INTO files (name) VALUES (:name)");
	    $q->execute(array(':name'=>$d));
	    fclose($f);
	}
    }
}

echo "Time since last run: Total: ".$totsec." secs = ".($totsec/3600)." hrs";
