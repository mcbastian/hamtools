#!/usr/bin/php
<?php
$logdir = "/var/log/mmdvm";

$dir = scandir($logdir);
$totsec = 0.0;
foreach ($dir as $d)
{
    if (!in_array($d,array(".","..")))
    {
	if (strpos($d, "mmdvm-")!==false)
	{
	    echo "---------------- ".$d."\n";
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
		$line = fgets($f); 
		if ($line === false) continue;
		if (($tp = strpos($line, "from "))===false) continue;
		if (($tps = strpos($line, " to "))===false) continue;
		$tp+=5;
		$call = substr($line, $tp, $tps-$tp);
		echo $call." ".$secs."\n";
	    }
	    fclose($f);
	}
    }
}

echo "Total: ".$totsec." secs = ".($totsec/3600)." hrs";