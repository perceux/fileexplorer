<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

// extract and validate request params
foreach(array("callback", "start", "sortField", "sortDir", "src", "dest", "cmd") as $name) {
    $value = $_REQUEST[$name];
     if(preg_match("/w*/", $value))
        $GLOBALS[$name] = $value;
}

/*
 * cmd
 *	dir (src)
 *	copy (src, dest)
 *	move (src, dest)
 *	del (src)
 */
$src = $DOCUMENT_ROOT.$src;
if ($src=="") $src="/";
$cmd="dir";
switch ($cmd) {
	case "dir" :
		if ($callback!="") print "$callback(";
		$result = dir($src);
		$h = opendir($src);
		print "{files: [\n";
		$flag_first = true;
		$i=0;
		while ($f = readdir($h)) {
			$i++;
			if ($f == "." || $f == "..")
				continue;
			$p = $src . "/" . $f;
			if (!$flag_first) print ",\n"; else $flag_first=false;
			print "{name:\"$f\", mtime:\"" . filemtime($p). "\", ctime:\"" . filectime($p). "\", ext:\"".substr(strrchr(basename($fichier), '.'), 1)."\", type:\"" . filetype($p) . "\", size:" . filesize($p) . "}";
		}
		print "\n], src:\"$src\", total:\"". ($i) ."\"}";
		if ($callback!="") print ");";
		closedir($h);
		break;
}
?>