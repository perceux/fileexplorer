<?php
$versionApp = "FileManager 1.9.2";
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
if ($rep == "/")
	$rep = "";

//-----------------------------------------------------------------------------------------------------------------------------------------
//	FONCTIONS
//-----------------------------------------------------------------------------------------------------------------------------------------

function dircopy($source, $dest, $overwrite = false) {
	global $config;
	$err = 0;
	if (is_file($source)) {
		if (!is_file($dest) || $overwrite)
			if (!copy($source, $dest)) {
				$err += 1;
			}
	}
	elseif (is_dir($source)) {
		if (!is_dir($dest))
			mkdir($dest);
		if ($handle = opendir($source)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
					$err += dircopy($source . '/' . $file, $dest . '/' . $file, $overwrite);
				}
			}
			closedir($handle);
		}
	}
	return $err;
}

function download($file_source, $file_target) {
	$rh = fopen($file_source, 'rb');
	$wh = fopen(trim($file_target), 'wb');
	if ($rh === false || $wh === false) {
		return "Erreur access aux ressources";
	}
	while (!feof($rh)) {
		if (fwrite($wh, fread($rh, 1024)) === FALSE) {
			return "Erreur lors de l'écriture";
		}
	}
	fclose($rh);
	fclose($wh);
	return "";
}

function tgzcompressfile($source, & $tar) {
	$n = 0;
	if (is_dir($source)) {
		$tar->addDirectory($source);
		$all = opendir($source);
		while ($file = readdir($all)) {
			if ($file != ".." && $file != ".") {
				$n += tgzcompressfile("$source/$file", $tar);
			}
		}
		closedir($all);
	} else {
		$tar->addFile($source);
		$n += 1;
	}
	return $n;
}

function gzcompressfile($source) {
	$dest = $source . '.gz';
	$mode = 'wb';
	$error = false;
	if ($fp_out = gzopen($dest, $mode)) {
		if ($fp_in = fopen($source, 'rb')) {
			while (!feof($fp_in))
				gzwrite($fp_out, fread($fp_in, 1024 * 512));
			fclose($fp_in);
		} else
			$error = true;
		gzclose($fp_out);
	} else
		$error = true;
	return ($error) ? false : $dest;
}

function fichier_proches($rep, $fic, $filecategory = '') {
	global $config;

	list ($liste_fichier, $poidstotalimage) = listing("$config[racine]$rep", $filecategory);
	$prev = $next = $fic;
	$found = false;
	foreach (array_keys($liste_fichier) as $f) {
		if ($found) {
			$next = $f;
			break;
		}
		if ($f == $fic) {
			$found = true;
		} else {
			$prev = $f;
		}
	}
	return array (
		$prev,
		$next
	);
}

function connecte($id) {
	global $config, $HTTP_REFERER;
	$retour = 0;
	if (!$config[auth_required]) {
		$retour = 1;
	} else
		if ($id != "") {
			if (file_exists("$config[racine]/sessions/$id.php")) {
				$retour = 1;
			}
			if (!eregi($config[installurl], $HTTP_REFERER)) {
				$retour = 0;
			}
		}
	return $retour;
}

function is_media($fichier) {
	return is_image($fichier) || is_playable($fichier);
}

function is_editable($fichier) {
	return (eregi("\.txt$|\.sql$|\.csv$|\.py$|\.php$|\.php3$|\.phtml$|\.htm$|\.html$|\.cgi$|\.pl$|\.js$|\.css$|\.inc$", $fichier)) ? 1 : 0;
}

function is_playable($fichier) {
	return (eregi("\.wav$|\.wma$|\.wmv$|\.asf$|\.mp3$|\.mpg|\.mpeg$|\.midi$|\.avi$|\.mov$", $fichier)) ? 1 : 0;
}

function is_image($fichier) {
	return (eregi("\.png$|\.bmp|\.dib$|\.jpg$|\.jpeg$|\.tiff|\.jpe$|\.gif$", $fichier)) ? 1 : 0;
}

function creer_id($chemin, $url, $user) {
	global $id, $config;
	$taille = 20;
	$lettres = "abcdefghijklmnopqrstuvwxyz0123456789";
	srand(time());
	for ($i = 0; $i < $taille; $i++) {
		$id .= substr($lettres, (rand() % (strlen($lettres))), 1);
	}
	$fp = fopen("$config[racine]/sessions/${id}.php", "w");
	if ($fp) {
		fputs($fp, "<?php \$config[racine]=\"$chemin\"; \$config[url_racine]=\"$url\"; \$user=\"$user\"; ?>");
		fclose($fp);
	} else {
		exit;
	}
}

function taille($taille) {
	global $config;
	if ($taille >= 1073741824) {
		$taille = (round($taille / 1073741824 * 100) / 100) . " G" . $config[size_unit];
	}
	elseif ($taille >= 1048576) {
		$taille = (round($taille / 1048576 * 100) / 100) . " M" . $config[size_unit];
	}
	elseif ($taille >= 1024) {
		$taille = (round($taille / 1024 * 100) / 100) . " K" . $config[size_unit];
	} else {
		$taille = $taille . " " . $config[size_unit];
	}
	if ($taille == 0) {
		$taille = "-";
	}
	return $taille;
}

function date_modif($fichier) {
	$tmp = filemtime($fichier);
	return date("d/m/Y H:i", $tmp);
}

function mimetype($fichier, $quoi) {
	global $mess, $ext2img, $config;
	if (is_dir($fichier)) {
		$res = array (
			"folder",
			$mess[8]
		);
	} else {
		$extension = substr(strrchr(basename($fichier), '.'), 1);
		$res = $ext2img["default"];
		foreach ($ext2img as $e => $r) {
			if (in_array($extension, explode("|", $e))) {
				$res = $r;
				break;
			}
		}
	}
	return ($quoi == "image") ? $config[imagetypepath] . $res[0] . '.png' : $res[1];
}

function assemble_tableaux($t1, $t2) {
	global $sens;
	if ($sens == 0) {
		$tab1 = $t1;
		$tab2 = $t2;
	} else {
		$tab1 = $t2;
		$tab2 = $t1;
	}
	if (is_array($tab1)) {
		while (list ($cle, $val) = each($tab1)) {
			$liste[$cle] = $val;
		}
	}
	if (is_array($tab2)) {
		while (list ($cle, $val) = each($tab2)) {
			$liste[$cle] = $val;
		}
	}
	return $liste;
}

function show_hidden_files($fichier) {
	global $config;
	return (substr($fichier, 0, 1) == "." && $config[showhidden] == 0) ? 0 : 1;
}

function listing($fullrep, $special_mode = '') {
	global $sens, $ordre, $config;
	$poidstotal = 0;
	$handle = opendir($fullrep);
	while ($fichier = readdir($handle)) {
		$noskip = ($fichier != "." && $fichier != ".." && show_hidden_files($fichier) == 1) ? true : false;
		if ($noskip)
			$isrep = is_dir("$fullrep/$fichier");
		switch ($special_mode) {
			case "text" :
				$noskip = $noskip && !$isrep && !is_media($fichier);
				break;
			case "media" :
				$noskip = $noskip && !$isrep && is_media($fichier);
				break;
			case "image" :
				$noskip = $noskip && !$isrep && is_image($fichier);
				break;
			case "playable" :
				$noskip = $noskip && !$isrep && is_playable($fichier);
				break;
			default :
				break;
		}
		if ($noskip) {
			if ($isrep) {
				if ($ordre == "mod") {
					$liste_rep[$fichier] = filemtime("$fullrep/$fichier");
				} else {
					$liste_rep[$fichier] = $fichier;
				}
			} else {
				$poidsfic = filesize("$fullrep/$fichier");
				$poidstotal += $poidsfic;
				switch ($ordre) {
					case "nom" :
						$liste_fic[$fichier] = mimetype("$fullrep/$fichier", "image");
						break;
					case "taille" :
						$liste_fic[$fichier] = $poidsfic;
						break;
					case "mod" :
						$liste_fic[$fichier] = filemtime("$fullrep/$fichier");
						break;
					case "type" :
						$liste_fic[$fichier] = mimetype("$fullrep/$fichier", "type");
						break;
					default :
						$liste_fic[$fichier] = mimetype("$fullrep/$fichier", "image");
						break;
				}
			}
		}
	}
	closedir($handle);

	if (is_array($liste_fic)) {
		if (in_array($ordre, array (
				"mod",
				"taille",
				"type"
			))) {
			($sens == 0) ? asort($liste_fic) : arsort($liste_fic);
		} else {
			($sens == 0) ? ksort($liste_fic) : krsort($liste_fic);
		}
	}
	if (is_array($liste_rep)) {
		if ($ordre == "mod") {
			($sens == 0) ? asort($liste_rep) : arsort($liste_rep);
		} else {
			($sens == 0) ? ksort($liste_rep) : krsort($liste_rep);
		}
	}

	$liste = assemble_tableaux($liste_rep, $liste_fic);

	return array (
		$liste,
		taille($poidstotal
	));
}

function footerHTML() {
	print "</form>";
	print "</center></body></html>";
}

function printCellHTML($content) {
	print "<td nowrap>";
	print $content;
	print "</td>";
}

function printActionHTML($action) {
	global $fic, $rep, $id, $ordre, $sens, $savefics, $saverep, $saveaction, $op;
	foreach (Array (
			"action",
			"savefics",
			"saverep",
			"saveaction",
			"fic",
			"rep",
			"id",
			"ordre",
			"sens",
			"op"
		) as $v) {
		print "<input type=\"hidden\" name=\"$v\" value=\"" . ${ $v } . "\">\n";
	}
}

$hasHeader = -1;
function headerHTML($type, $titre = 'FileManager', $text = '', $html = '', $onload = '', $defaultaction = '-') {
	global $config, $id, $ordre, $sens, $user, $mess, $rep, $msg, $hasHeader, $PHP_SELF, $op;
	$hasHeader = $type;
	print "<html><head><title>$titre</title>\n";
	print '<META name="verify-v1" content="Hq4Jid1Nw9UoiaoMivQx7JFVfy2y1q8ReZUrOkOhTwI=" />';

	// JAVASCRIPT
?>
<style>
	body,td,form,span,input.std,.msg {font:<?php echo $config[font]; ?>;}
	form {margin:0px}
	body {margin:5px}
	table {border:1;}
	th,td,textarea,select,div,input.std,.msg { font-size:11pt; margin:0; padding:0;}
	table,tr,td,th,span {unselectable:on;}
	.selected {background-color:#CCCCCC;}
	.msg {background-color:#FFEECC; width:100%; text-align:center;}
	.unselected {background-color:#EEEEEE;}
	.text, .btn, .filebtn {height:20px; margin:3px; padding:0.1em; border:1px solid gray; background-color:#EEEEEE;}
	.btn,.filebtn {width:95px;}
	.btn:hover {color:#DDDDFF;}
</style>
<script>
var Key = {
	keys:[], listeners:[],
	LEFT:37, RIGHT:39, UP:38, DOWN:40,
	BACKSPACE:8, CAPSLOCK:20, CONTROL:17,
	DELETEKEY:46, END:35, ENTER:13,
	ESCAPE:27, HOME:36, INSERT:45, TAB:9,
	PGDN:34, PGUP:33, SPACE:32, SHIFT:16
};
Key.init = function() {
	for( var num = 0; num < 256; num++ ) {
		this.keys[num] = false;
	}
	document.onkeydown = function(e){
		e=e?e:event;
		Key.keys[e.keyCode] = true;
		Key.onKeyDown(e);
		for( var num = 0; num < Key.listeners.length; num++ ) {
			if( Key.listeners[num] && Key.listeners[num].onKeyDown ) Key.listeners[num].onKeyDown(e);
		}
	}
	document.onkeyup	= function(e){
		e=e?e:event;
		Key.keys[e.keyCode] = false;
		Key.onKeyUp(e);
		for( var num = 0; num < Key.listeners.length; num++ ) {
			if( Key.listeners[num] && Key.listeners[num].onKeyUp ) Key.listeners[num].onKeyUp(e);
		}
	}
}
Key.isDown = function(check) {
		return this.keys[check];
}
Key.addListener = function(object) {
	var flag = 0;
	for( var num = 0; num < this.listeners.length; num++ ) {
		if(this.listeners[num]) {
			try {
				if( this.listeners[num].onKeyDown.toString() == object.onKeyDown.toString()
					&& this.listeners[num].onKeyUp.toString() == object.onKeyUp.toString()) {
					flag = 1;
				}
			} catch (e) {}
		}
	}
	if( flag != 1 )
		this.listeners[this.listeners.length] = object;
}
Key.removeListener = function(object) {
	for( var num = 0; num < this.listeners.length; num++ ) {
		if(this.listeners[num]) {
			try {
				if( this.listeners[num].onKeyDown.toString() == object.onKeyDown.toString()
					&& this.listeners[num].onKeyUp.toString() == object.onKeyUp.toString()) {
					delete this.listeners[num];
				}
			} catch (e) {}
		}
	}
}
Key.onKeyDown = function(){}
Key.onKeyUp	= function(){}
Key.init();

var startTR = null;
var savedTR = null;
var f1 = null;
var f2 = null;
var dragStarted = false;

function moveSelect() {
	if (dragStarted) {
		var tr = parentTR(window.event.srcElement);
		if (tr != null) {
			rangeSelect(tr, startTR);
		}
	}
}

function parentTR(o) {
	try {
		while (o!=null && !(o.tagName=="TR" && o.id && o.id.length > 3 && o.id.substring(0,3)=="tr_")) {
			o=o.parentElement;
		}
	} catch(e) {
		return null;
	}
	return o;
}

function stopSelect() {
	if (dragStarted) {
		var tr = parentTR(window.event.srcElement);
		if (tr!=null) {
			rangeSelect(tr,startTR);
		}
		document.onmousemove=f1;
		document.onmouseup=f2;
		dragStarted = false;
	}
}

function startSelect() {
	if (!dragStarted) {
		dragStarted = true;
		startTR = parentTR(window.event.srcElement);
		f1=document.onmousemove;
		f2=document.onmouseup;
		document.onmousemove=moveSelect;
		document.onmouseup=stopSelect;
	}
}

function toogleSelect(t1) {
	t1.className = (t1.className!="selected")?"selected":"unselected";
}

function rangeSelect(t1, t2) {
	var t = t1.parentElement;
	var sel = false;
	if (!document.all) return;
	for(i=1;i<t.rows.length-1;i++) {
		var tr = t.rows(i);
		sel=((t1!=t2)&&(tr==t1||tr==t2))?!sel:sel;
		if (Key.isDown(Key.CONTROL)) {
			if (sel||tr==t1||tr==t2) toogleSelect(tr);
		} else {
			newClassName = (sel||tr==t1||tr==t2)?"selected":"unselected";
			if (tr.className != newClassName) tr.className = newClassName;
		}
	}
	savedTR = (t1.className=="selected")?t1:t2;
}

function dirToogleSelect() {
	if (!document.all) return;
	for(i=1;i<t.rows.length-1;i++) {
		toogleSelect(t.rows(i));
	}
}

function dirGetSelection() {
	var t = document.getElementById('mainTable');
	var result = '';
	if (t && document.all) {
		for(i=1;i<t.rows.length-1;i++) {
			var tr = t.rows(i);
			if (tr.className=="selected" && tr.id && tr.id.length > 3 && tr.id.substring(0,3)=="tr_") {
				if (result != '') result+='/';
				result+=tr.id.substring(3);
			}
		}
	}
	return result;
}

function dirSaveInfo(saveaction,saverep,savefics) {
	var f = document.mainForm;
	if (f) {
		f.savefics.value=savefics;
		f.saverep.value=saverep;
		f.saveaction.value=saveaction;
	}
}

var renameHTML = '';
function dirSubmitAction(act,ordre,sens,rep,fic) {
	var f = document.mainForm;
	if (f) {
		if (act=="" || act=='-' || act=='view' || act=='slide' || act=='info' || act=='download') {
			f.method='get';
		}
		if (ordre!=undefined) f.ordre.value=ordre;
		if (sens!=undefined) f.sens.value=sens;
		if (rep!=undefined) f.rep.value=rep;
		
		f.fics.value=dirGetSelection();
		var s = f.fics.value.split('/');
		if (act=='delete') {
			if (!confirm('Voulez-vous vraiment supprimer:\n'+s.join('\n')+'?')) {
				return;
			}
		}
		if (fic!=undefined) {
			f.fic.value=fic;
		} else {
			f.fic.value = s[0];
		}
		if (act=='edit' || act=='download' || act=='rename') {
			if (s.length != 1) {
				alert('selection invalide pour cette action');
				return;
			} else if (act=='rename') {
				for(i=1;i<t.rows.length;i++) {
					var tr = t.rows(i);
					if (tr.id=='tr_'+ f.fic.value && renameHTML=='' ) {
						var cell = tr.cells(0);
						var imgHTML = cell.childNodes.item(0).childNodes.item(0).outerHTML;
						renameHTML = cell.innerHTML;
						cell.innerHTML = imgHTML + '<input class=std style="font-size:11pt;border:0px;width:100%;" unselectable=off onblur="dirSubmitAction(\'rename\');" onkeypress="if (event.keyCode==13) {dirSubmitAction(\'rename\');event.returnValue=false; return false;}" type=text name=destfic value="'+ f.fic.value +'" >';
						var df = document.getElementsByName('destfic');
						if (df) {
							setUnselectable(false);
							var inputField = df[0];
							inputField.focus();
						}
						return;
					} else {
					}
				}
			}
		}
		f.action.value=act;
		try {
			f.submit();
		} catch (e) {
			f.method='post';
			f.submit();
		}
	}
}


function setUnselectable(enabled) {
	if (t) {
		if (!enabled) {
			t.onselectstart = null;
		} else {
			t.onselectstart = function() { return(false); };
			t.setAttribute('unselectable', 'on', 0);
		}
	}
}

</script>
	<?php


	print "</head><body onload=\"$onload\"><center>";
	print "<table width=\"$config[tablewidth]\" cellpadding=2 border=0 cellspacing=2 bgcolor=$config[tablecolor2] style=\"margin-bottom:8px;\"><tr><td><b>\n";
	switch ($type) {
		case 0 :
			// nav rep
			print mkImgHTML("javascript:dirSubmitAction('','$ordre','$sens','','');", "home", '', "ABSMIDDLE", $user);
			$array_chemin = split("/", $rep);
			$addchemin = "";
			while (list ($cle, $val) = each($array_chemin)) {
				if ($val != "") {
					$addchemin = $addchemin . "/" . $val;
					print "/<a href=\"javascript:dirSubmitAction('','$ordre','$sens','$addchemin','');\">$val</a>";
				}
			}
			break;
		case 1 :
			// back button
			print mkImgHTML("javascript:dirSubmitAction('','$ordre','$sens','$rep','$fic');", "back", '', "ABSMIDDLE", $mess[32]);
		default :
			// controle
			print $text;
			break;
	}

	print "</b></td>";
	print "<td align=\"right\">\n";
	if (empty ($html)) {
		// ACTIONS POSSIBLES
		print mkImgHTML("javascript:dirToogleSelect();", "select", "select") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('edit');", "edit", "edit") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('reverse');", "reverse", "reverse") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('info');", "info", "info") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('slide');", "slide", "slide") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('view');", "view", "view") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('download');", "download", "download") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('compress');", "compress", "compress") . "&nbsp;";
		print mkImgHTML("javascript:dirSaveInfo('copy','$rep', dirGetSelection());", "copy", "copy") . "&nbsp;";
		print mkImgHTML("javascript:dirSaveInfo('move','$rep', dirGetSelection());", "cut", "cut") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction(document.mainForm.saveaction.value);", "paste", "paste") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('rename');", "rename", "rename") . "&nbsp;";
		print mkImgHTML("javascript:dirSubmitAction('delete');", "delete", "delete") . "&nbsp;";
		print "&nbsp;&nbsp;&nbsp;";

		// + options
		print mkImgHTML("$PHP_SELF?id=$id&ordre=$ordre&sens=$sens&rep=$rep&op=" . (($op == 1) ? 0 : 1), "plus", "new") . "</a>&nbsp;\n";

		if ($config[allow_change_lang]) {
			print mkImgHTML("$PHP_SELF?action=langue&id=$id&ordre=$ordre&sens=$sens&rep=$rep&op=$op", "lang", "$mess[92]") . "</a>&nbsp;\n";
		}
		if ($config[allow_configure]) {
			print mkImgHTML("$PHP_SELF?action=configurer&id=$id&ordre=$ordre&sens=$sens&rep=$rep&op=$op", "configurer", "Conf") . "</a>&nbsp;\n";
		}
		print mkImgHTML("javascript:location.reload()", "refresh", "$mess[85]") . "</a>&nbsp;\n";
		print mkImgHTML("$PHP_SELF?action=aide&id=$id&ordre=$ordre&sens=$sens&rep=$rep&op=$op", "help", "$mess[84]") . "</a>&nbsp;\n";
		if ($config[auth_required]) {
			print mkImgHTML("$PHP_SELF?action=deconnexion&id=$id", "disconnect", "$mess[63]") . "</a>&nbsp;";
		}

	} else {
		print $html;
	}
	print "</td></tr>";
	if (!empty ($msg)) {
		print "<tr><td colspan=2 class=msg>" . $msg . "</td></tr>";
	}
	print "</table>\n";
	print "<form name=mainForm enctype=\"multipart/form-data\" action=\"$PHP_SELF\" method=\"post\">\n";
	print "<input type=\"hidden\" name=\"fics\" value=\"\">\n";
	printActionHTML($defaultaction, "");

}

function printActionForms() {
	global $rep, $config, $mess, $sens, $id, $ordre, $PHP_SELF;

	print "<table width=\"$config[tablewidth]\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"  style=\"margin-bottom:8px;\">\n";
	print "<tr bgcolor=\"$config[tablecolor]\"><td colspan=\"2\">\n";
	print mkImgHTML('', "envoyer", '', "ABSMIDDLE") . " \n";
	print "$mess[25]<b>$rep</b></td></tr><tr><td colspan=\"2\">\n";
	print "&nbsp;<input type=\"file\" class=filebtn name=\"userfile\" size=\"30\" style=\"width:80%;\" value=\"$filename\">\n";
	print "<button class=btn onclick=\"dirSubmitAction('upload');\">$mess[27]</button>";
	print "</td></tr><tr bgcolor=\"$config[tablecolor]\"><td colspan=\"2\">\n";
	print mkImgHTML('', "foldernew", '', "ABSMIDDLE") . "\n";
	print "$mess[26]<b>$rep</b></td></tr><tr><td colspan=\"2\">\n";
	print "&nbsp;<input type=\"text\" class=text name=\"nomdir\" size=\"30\" style=\"width:80%;\">\n";
	print "<button class=btn onclick=\"dirSubmitAction('mkdir');\">$mess[29]</button>";
	print "</td></tr><tr bgcolor=\"$config[tablecolor]\"><td colspan=\"2\">\n";
	print mkImgHTML('', "filenew", '', "ABSMIDDLE") . "\n";
	print "$mess[28]<b>$rep</b></td></tr><tr><td colspan=\"2\">\n";
	print "&nbsp;<input type=\"text\" class=text name=\"nomfic\" size=\"30\" style=\"width:80%;\">\n";
	print "<button class=btn onclick=\"dirSubmitAction('newfile');\">$mess[29]</button>";
	print "</td></tr><tr bgcolor=\"$config[tablecolor]\"><td colspan=\"2\">\n";
	print mkImgHTML('', "filenew", '', "ABSMIDDLE") . " \n";
	print "Copier un fichier à partir d'une url dans: ";
	print "<b>$rep</b></td></tr><tr><td colspan=\"2\">\n";
	print "&nbsp;<input type=\"text\" class=text name=\"url\" size=\"30\" style=\"width:80%;\">\n";
	print "<button class=btn onclick=\"dirSubmitAction('copierweb');\">$mess[29]</button>";
	print "</td></tr></table>";
}

function printDirectoryContent() {
	global $rep, $config, $mess, $sens, $user, $id, $ordre, $poidstotal, $PHP_SELF;

	// FORMULAIRE REPERTOIRE
	if (ereg("\.\.", $rep))
		$rep = "";
	$fullrep = $config[racine] . $rep;
	print "<table id=mainTable width=\"$config[tablewidth]\" cellspacing=\"0\" style=\"border-collapse: collapse; border:1px solid;\" onMouseDown='startSelect();'>\n";
	print "\n<tr bgcolor=\"$config[tablecolor]\">";

	// TABLEAU REPERTOIRE
	$ordres = array (
		"nom" => array (
			$mess[1],
			"60%"
		),
		"taille" => array (
			$mess[2],
			"20%"
		),
		"type" => array (
			$mess[3],
			"20%"
		),
		"mod" => array (
			$mess[4],
			"20%"
		)
	);
	if (empty ($ordre))
		$ordre = "nom";
	if (empty ($sens))
		$sens = 0;
	$invsens = ($sens != 1) ? 1 : 0;
	foreach ($ordres as $nomordre => $info) {
		$lien = "javascript:dirSubmitAction('','$nomordre','$invsens','$rep','$fic');";
		print "<td width='$info[1]' nowrap><b><a href=\"$lien\">$info[0]</a></b>";
		if ($ordre == $nomordre) {
			print "&nbsp;";
			print mkImgHTML($lien, "sort${sens}");
		}
		print "</td>";
	}
	print "</tr>";

	// DOSSIER PARENT
	if ($rep != "") {
		$updir = dirname($rep);
		print "\n<tr class=unselected><td>";
		print mkImgHTML("javascript:dirSubmitAction('','$ordre','$sens','" . (($updir != "\\" && $updir != "." && $updir != "") ? $updir : "/") . "','');", "up", '', "ABSMIDDLE", $mess[24]);
		print "</td><td colspan=3>&nbsp;</td></tr>";
	}

	// FICHIERS
	list ($liste, $poidstotal) = listing($fullrep);

	if (is_array($liste)) {
		while (list ($fichier, $mime) = each($liste)) {
			$is_rep = is_dir("$fullrep/$fichier");

			// AFFICHAGE DE LA LIGNE
			$widths = "60%,20%,20%,20%";
			if ($is_rep) {
				$lien = "javascript:dirSubmitAction('" . (($is_rep) ? "" : "view") . "','$ordre','$sens','$rep" . (($is_rep && $fichier != "") ? "/$fichier" : "") . "','$fichier');";
			} else {
				$lien = $DOCUMENT_ROOT . $rep . "/$fichier";
			}
			print "\n<tr id=\"tr_$fichier\" class=unselected>";
			printCellHTML(mkImgHTML($lien, mimetype("$fullrep/$fichier", "image"), '', "ABSMIDDLE", " $fichier"));
			printCellHTML($is_rep ? "-" : taille(filesize("$fullrep/$fichier")));
			printCellHTML(mimetype("$fullrep/$fichier", "type"));
			printCellHTML(date_modif("$fullrep/$fichier"));
			print "</tr>";
		}
	}

	print "\n<tr><td colspan=5 height=1 bgcolor=$config[tablecolor]></td></tr>";
	print "\n<tr><td>&nbsp;</td><td>$poidstotal</td><td colspan=2 align=right nowrap>";
	print "</td></tr></table>\n<br>";
?>
<script>
var t = document.getElementById('mainTable');
setUnselectable(true);
</script>
<?php

}

function dirdelete($location) {
	if (is_dir($location)) {
		$all = opendir($location);
		while ($file = readdir($all)) {
			if (is_dir("$location/$file") && $file != ".." && $file != ".") {
				dirdelete("$location/$file");
				if (file_exists("$location/$file")) {
					rmdir("$location/$file");
				}
				unset ($file);
			}
			elseif (!is_dir("$location/$file")) {
				if (file_exists("$location/$file")) {
					unlink("$location/$file");
				}
				unset ($file);
			}
		}
		closedir($all);
		return rmdir($location);
	} else {
		if (file_exists("$location")) {
			return unlink("$location");
		}
	}
}

function enlever_controlM($fichier) {
	$fic = file($fichier);
	$fp = fopen($fichier, "w");
	while (list ($cle, $val) = each($fic)) {
		$val = str_replace(CHR(10), "", $val);
		$val = str_replace(CHR(13), "", $val);
		fputs($fp, "$val\n");
	}
	fclose($fp);
}

function traite_nom_fichier($nom) {
	global $config;
	$nom = strtr($nom, "'\"&,;/\\`<>:*|?@';+^()#=\$%", "");
	$nom = strtr($nom, " éèêçïîôûüàâ", "_eeeciiouuaa");
	$nom = substr($nom, 0, $config[max_caracteres]);
	return $nom;
}

//-----------------------------------------------------------------------------------------------------------------------------------------
//	MAIN
//-----------------------------------------------------------------------------------------------------------------------------------------
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

// BEGIN CONFIG
$config[installurl] = "http://" . $HTTP_HOST . dirname($PHP_SELF);
$config[racine] = $DOCUMENT_ROOT;
$config[url_racine] = "http://$HTTP_HOST" . (($SERVER_PORT != 80) ? ":$SERVER_PORT" : "");
//$config[imagerootpath] = "/images/icons";
$config[imagerootpath] = "http://fuckbox.free.fr/images/icons";
//$config[imageenabled] = is_dir("$config[racine]$config[imagerootpath]");
$config[imageenabled] = true;
$config[imageactionpath] = "$config[imagerootpath]/actions/";
$config[imagetypepath] = "$config[imagerootpath]/types/";
$config[displaydivheight] = "expression(document.body.clientHeight-45)";
$config[dft_langue] = "fr";
$config[allow_change_lang] = true;
$config[allow_configure] = (isset ($id) && file_exists("$config[racine]/sessions/$id.php")) ? true : false;
$config[size_unit] = "o";
$config[max_caracteres] = 40; // (max number chars for file and directory names)
$config[showhidden] = 1; // (Show hidden files, yes=1, no=0)
$config[tablecolor] = "#CCCCEE";
$config[tablecolor2] = "#DDDDFF"; // (background color of table lines)
$config[tablewidth] = "99%";
$config[font] = "Verdana";
$config[auth_required] = false;
$config[auth_user] = array (
	"admin" => "grivuxe"
);
// END CONFIG

// LANGUAGES
if ($langue == "") {
	$langue = $config[dft_langue];
}

// SESSION
if (isset ($id) && file_exists("$config[racine]/sessions/$id.php")) {
	include ("$config[racine]/sessions/$id.php");
}

switch ($langue) {
	case "fr" :
		$mess = array ( 0 => "Dernière version", 1 => "Fichier", 2 => "Taille", 3 => "Type", 4 => "Modifi&eacute; le", 5 => "Actions", 6 => "Renommer", 7 => "Supprimer", 8 => "Dossier", 9 => "Fichier MIDI", 10 => "Document texte", 11 => "Javascript", 12 => "Image GIF", 13 => "Image JPG", 14 => "Page HTML", 15 => "Page HTML", 16 => "Fichier REAL", 17 => "Fichier REAL", 18 => "Script PERL", 19 => "Fichier ZIP", 20 => "Fichier audio", 21 => "Script PHP", 22 => "Script PHP", 23 => "Fichier", 24 => "..", 25 => "Transf&eacute;rer un fichier dans : ", 26 => "Cr&eacute;er un nouveau r&eacute;pertoire dans : ", 27 => "Transf&eacute;rer", 28 => "Cr&eacute;er un nouveau fichier dans : ", 29 => "Cr&eacute;er", 30 => "Saisissez un nom de r&eacute;pertoire et cliquez sur &quot;Cr&eacute;er&quot;", 31 => "Vous n'avez pas s&eacute;lectionn&eacute; de fichier", 32 => "Retour", 33 => "Erreur de transfert !", 34 => "Le Fichier", 35 => "a &eacute;t&eacute; enregistr&eacute; dans le r&eacute;pertoire", 36 => "Sa taille est de", 37 => "Vous devez saisir un nom de fichier valide", 38 => "Le dossier", 39 => "a &eacute;t&eacute; cr&eacute;&eacute; dans le r&eacute;pertoire", 40 => "Ce dossier existe déjà", 41 => "a &eacute;t&eacute; renomm&eacute; en", 42 => "en", 43 => "existe déjà", 44 => "a &eacute;t&eacute; effac&eacute;", 45 => "r&eacute;pertoire", 46 => "fichier", 47 => "Voulez-vous supprimer d&eacute;finitivement le", 48 => "OUI", 49 => "NON", 50 => "Fichier EXE", 51 => "Editer", 52 => "Edition du fichier", 53 => "Enregistrer", 54 => "Annuler", 55 => "a &eacute;t&eacute; modifi&eacute;", 56 => "Image BMP", 57 => "Image PNG", 58 => "Fichier CSS", 59 => "Fichier MP3", 60 => "Fichier RAR", 61 => "Fichier GZ", 62 => "config[racine] du site", 63 => "D&eacute;connexion", 64 => "Fichier Excel", 65 => "Fichier Word", 66 => "Copier", 67 => "Fichier s&eacute;lectionn&eacute;", 68 => "Coller dans", 69 => "Ou choisissez un autre r&eacute;pertoire", 70 => "D&eacute;placer", 71 => "Ce fichier existe d&eacute;jà", 72 => "La racine du r&eacute;pertoire est incorrecte. V&eacute;rifier la config", 73 => "a &eacute;t&eacute; copi&eacute; dans le r&eacute;pertoire", 74 => "a &eacute;t&eacute; d&eacute;plac&eacute; dans le r&eacute;pertoire", 75 => "Le fichier users.txt est introuvable", 76 => "Ce fichier a &eacute;t&eacute; supprim&eacute;", 77 => "Envoyer", 78 => "Passe", 79 => "Fichier PDF", 80 => "Fichier MOV", 81 => "Fichier AVI", 82 => "Fichier MPG", 83 => "Fichier MPEG", 84 => "Aide", 85 => "Actualiser", 86 => "Fermer", 87 => "Rechercher", 88 => "T&eacute;l&eacute;charger", 89 => "Impossible d'ouvrir le fichier", 90 => "Imprimer", 91 => "Fichier FLASH", 92 => "Langue", 93 => "Pour s&eacute;lectionner la langue, votre navigateur doit accepter les cookies.", 94 => "Login", 95 => "Choisissez votre langue :" );
 break;
 default : 
 $mess = array ( 0 => "Last version", 1 => "Filename", 2 => "Size", 3 => "Type", 4 => "Modified", 5 => "Actions", 6 => "Rename", 7 => "Delete", 8 => "Directory", 9 => "Midi File", 10 => "Text file", 11 => "Javascript", 12 => "GIF picture", 13 => "JPG picture", 14 => "HTML page", 15 => "HTML page", 16 => "REAL file", 17 => "REAL file", 18 => "PERL script", 19 => "ZIP file", 20 => "Audio file", 21 => "PHP script", 22 => "PHP script", 23 => "File", 24 => "..", 25 => "Upload a file in the directory : ", 26 => "Create a new directory in : ", 27 => "Upload", 28 => "Create a new file in : ", 29 => "Create", 30 => "Write a name for the directory then click on &quot;Create&quot;", 31 => "You must select a file", 32 => "Go back", 33 => "Error uploading file !", 34 => "The file", 35 => "has been successfully created in the directory", 36 => "It's size is", 37 => "You must write a valid name", 38 => "The directory", 39 => "has been create in", 40 => "This directory already exists", 41 => "has been renamed to", 42 => "to", 43 => "already exists", 44 => "has been deleted", 45 => "directory", 46 => "file", 47 => "Do you really want to delete the", 48 => "YES", 49 => "NO", 50 => "Exe file", 51 => "Edit", 52 => "Editing file", 53 => "Save", 54 => "Cancel", 55 => "has been modified", 56 => "BMP picture", 57 => "PNG picture", 58 => "CSS File", 59 => "MP3 File", 60 => "RAR File", 61 => "GZ File", 62 => "Root directory", 63 => "Log out", 64 => "XLS File", 65 => "Word File", 66 => "Copy", 67 => "Selected file", 68 => "Paste in", 69 => "Or select another directory", 70 => "Move", 71 => "This file already exists", 72 => "The root path is not correct. Check it in the PRIVE/users.TXT file", 73 => "has been copied into the directory", 74 => "has been moved into the directory", 75 => "The file users.txt is not in the directory prive", 76 => "This file has been removed", 77 => "Send", 78 => "Pass", 79 => "PDF File", 80 => "MOV File", 81 => "AVI File", 82 => "MPG File", 83 => "MPEG File", 84 => "Help", 85 => "Refresh", 86 => "Close", 87 => "Search", 88 => "Download", 89 => "Unable to open file", 90 => "Print", 91 => "FLASH File", 92 => "Language", 93 => "To choose your language, your browser must accept cookies.", 94 => "Login", 95 => "Select your language :" );
 break;}

// MIMETYPE
$ext2img = array ( "mid" => array ( "midi", $mess[9] ), "txt|sql" => array ( "txt", $mess[10] ), "js" => array ( "txt2", $mess[11] ), "gif" => array ( "image", $mess[12] ), "jpg" => array ( "image2", $mess[13] ), "html" => array ( "html", $mess[14] ), "htm" => array ( "netscape_doc", $mess[15] ), "rar" => array ( "rar", $mess[60] ), "gz|tgz|z" => array ( "tgz", $mess[61] ), "ra" => array ( "real_doc", $mess[16] ), "ram|rm" => array ( "real_doc", $mess[17] ), "ps" => array ( "ps", "Postscript" ), "java|jsp" => array ( "java", "Java" ), "pl" => array ( "source_pl", $mess[18] ), "py" => array ( "source_py", $mess[18] ), "zip" => array ( "tgz", $mess[19] ), "wav|wma" => array ( "sound", $mess[20] ), "php" => array ( "php", $mess[21] ), "pdf" => array ( "pdf", $mess[79] ), "php5|php3|phtml" => array ( "php", $mess[22] ), "exe" => array ( "make", $mess[50] ), "bmp" => array ( "image", $mess[56] ), "png" => array ( "image2", $mess[57] ), "css" => array ( "mime-colorset", $mess[58] ), "mp3" => array ( "sound", $mess[59] ), "xls" => array ( "spreadsheet", $mess[64] ), "doc" => array ( "wordprocessing", $mess[65] ), "mov" => array ( "quicktime", $mess[80] ), "avi" => array ( "video", $mess[81] ), "mpg" => array ( "video", $mess[82] ), "mpeg" => array ( "video", $mess[83] ), "swf" => array ( "flash", $mess[91] ), "readme" => array ( "readme", "readme" ), "default" => array ( "mime_empty", $mess[23] ));

function mkImgHTML($link, $name, $alt = '', $align = '', $text = '') {
	global $config;
	if (!$config[imageenabled]) {
		$result = "<span style=\"font-size:0.75em;\" title=\"$alt\">[" . (empty ($name) ? "&nbsp;" : substr($name, 0, 3)) . "]</span>";
	} else {
		$src = (strpos($name, "/") === FALSE) ? ($config[imageactionpath] . (empty ($name) ? "blank" : $name) . ".png") : $name;
		if (ereg("MSIE (4|5|6)", $_SERVER["HTTP_USER_AGENT"])) {
			$result = "<span align='$align' alt=\"$alt\" style=\"margin:1px;width:16px;" . (empty ($link) ? "" : "cursor:hand;") . "height:16px;border:0px;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$src', sizingMethod='scale');\"></span>";
		} else {
			$result = "<img width=16 height=16 src=\"$src\" border=0 align='$align' alt=\"$alt\">";
		}
	}
	$result .= $text;
	return (empty ($link)) ? $result : "<a href=\"$link\">$result</a>";

}

switch ($action) {
	case "nothing" :
		headerHTML(0);
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	CONFIGURER / CONFIGURE
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "configurer";
		headerHTML(0);
		print "<table border=0>\n";
		foreach ($config as $k => $v) {
			print "<tr><td bgcolor=$config[tablecolor]>$k</td><td>$v</td></tr>\n";
		}
		print "</table>\n";
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	AIDE / HELP
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "aide";
		headerHTML(0);
		print $versionApp."<br>";
		print "<a href='http://fuckbox.free.fr/sm.php'>Site Manager</a><br>";
		print "<a href='http://percou.free.fr/tools'>Percou Tools</a><br>";
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	LANGUE / LANGUAGE
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "langue";
		setcookie("cookie_test", "ok", time() + 3600); // 1 an
		headerHTML(1);
		print "<center>$mess[95]</center><br>";
		print "\n<table width=\"70%\" cellspacing=\"20\" cellpadding=\"0\" align=\"center\">\n";
		print "\n<tr align=\"center\">";
		$langs = array (
			"fr" => "Fran&ccedil;ais",
			"en" => "English",
			"de" => "Deutsch"
		);
		foreach ($langs as $k => $v) {
			print "<td align=\"center\">" . mkImgHTML("$PHP_SELF?action=savelangue&id=$id&ordre=$ordre&sens=$sens&rep=$rep&op=$op&langue=$k", "lang_$k", "$v", "<br>$v");
			if ($langue == $k) {
				print mkImgHTML('', "check", '', "ABSMIDDLE");
			}
			print "</td>";
		}
		print "</tr></table>\n";

		break;

	case "savelangue";
		if ($cookie_test != "ok") {
			headerHTML(1, "", $mess[93]);
		} else {
			$langue = $HTTP_GET_VARS["langue"];
			setcookie("langue", $langue, time() + 31536000); // 1 an
			$nextaction = 'langue';
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	DOWNLOAD
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "download";
		$file = $rep . (($fic != "") ? "/$fic" : "");
		$taille = filesize("$config[racine]$file");
		header("Content-Type: application/force-download; name=\"$fic\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: $taille");
		header("Content-Disposition: attachment; filename=\"$fic\"");
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		readfile("$config[racine]$file");
		exit ();
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	EDIT
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "edit";
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}

		$file = "$rep/$fic";

		if ($save == 1) {
			if (get_magic_quotes_gpc() == 1) {
				$code = stripslashes($code);
			}
			$fp = fopen("$config[racine]$file", "w");
			fputs($fp, $code);
			fclose($fp);
			enlever_controlM("$config[racine]$file");
		}

		$buttons = mkImgHTML("javascript:document.mainForm.submit();", "save", "$mess[53]") . "&nbsp;\n";
		$buttons .= mkImgHTML("javascript:window.print()", "print", "$mess[90]") . "&nbsp;\n";
		$buttons .= mkImgHTML("javascript:dirSubmitAction('view','$ordre','$sens','$rep','$fic');", "view", "$mess[51]") . "&nbsp;\n";
		$buttons .= mkImgHTML("$config[url_racine]$file", "html", "HTTP") . "&nbsp;\n";
		$buttons .= mkImgHTML("javascript:if (self.search) search();", "chercher", "?") . "&nbsp;\n";
		$buttons .= mkImgHTML("javascript:dirSubmitAction('download','$ordre','$sens','$rep','$fic');", "download", "$mess[88]") . "&nbsp;\n";
		$buttons .= mkImgHTML("javascript:dirSubmitAction('','$ordre','$sens','$rep','$fic');", "exit", "$mess[86]") . "&nbsp;\n";

		headerHTML(2, $fic, mkImgHTML('', mimetype("$config[racine]$file", "image"), '', "ABSMIDDLE") . "$mess[52] : " . $file, $buttons, "document.getElementById('textareaedit').focus();", "edit");
		print "<input type=\"hidden\" name=\"save\" value=\"1\">\n";
		$fp = fopen("$config[racine]$file", "r");
		if ($fp) {
			$onkeydown = " if(event.keyCode==9){event.returnValue=false; if(document.selection) {document.selection.createRange().text=String.fromCharCode(9);} else {return false;}}";
			$onkeydown .= " if (event.ctrlKey && event.keyCode==83) {document.mainForm.submit(); event.returnValue=false; return false;}";
			print "<TEXTAREA id=textareaedit NAME=\"code\" contentEditable=\"true\" wrap=\"OFF\" style=\"padding:0px;margin:0px;height:expression(document.body.clientHeight-45);width:$config[tablewidth];margin:0px;padding:0px;\" onkeydown=\"$onkeydown\">\n";
			while (!feof($fp)) {
				$tmp = fgets($fp, 4096);
				$tmp = str_replace("&", "&amp;", $tmp);
				$tmp = str_replace("<", "&lt;", $tmp);
				print "$tmp";
			}
			print "</TEXTAREA>\n";
			fclose($fp);
		} else {
			print $mess[89] . " : " . "$config[racine]$file";
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	COPY / MOVE / DELETE
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "delete" :
		$saverep = $rep;
		$savefics = $fics;
	case "copy" :
	case "move" :
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}

		$srcrep = $config[racine] . (($saverep == "/" || $saverep == "") ? "" : "/$saverep");
		$destrep = $config[racine] . (($rep != "") ? "/$rep" : "");

		$files = explode("/", $savefics);
		if (is_array($files)) {
			foreach ($files as $f) {
				if (!empty ($f) && file_exists("$srcrep/$f")) {
					$destination = $f;
					switch ($action) {
						case "copy" :
							while ($destrep == $srcrep && file_exists("$destrep/$destination"))
								$destination = "copy_" . $destination;
							$messtmp = "copie de <b>$srcrep/$f</b> vers <b>$destrep/$destination</b>";
							$err = dircopy("$srcrep/$f", "$destrep/$destination", true);
							break;
						case "move" :
							if ($destrep != $saverep) {
								if (rename("$srcrep/$f", "$destrep/$destination")) {
									$messtmp = "deplacement de <b>$srcrep/$f</b> vers <b>$destrep/$destination</b>";
									$saverep = "";
									$savefics = "";
								}
							}
							break;
						case "delete" :
							if (dirdelete("$srcrep/$f")) {
								$messtmp = "suppression de <b>$srcrep/$f</b>";
								$saverep = "";
								$savefics = "";
							} else {
								$messtmp = "Erreur lors de la suppression de <b>$srcrep/$f</b>";
							}
							break;
					}
				}
			}
		}

		break;
	case "slide";
	list ($liste_fichier, $poidstotalimage) = listing("$config[racine]$rep", "image");
	if (is_array($liste_fichier)) {
			$buttons = mkImgHTML("$PHP_SELF?id=$id&rep=$rep&op=$op&ordre=$ordre&sens=$sens", "exit", "$mess[86]") . "&nbsp;\n";
			headerHTML(2, $rep, $rep, $buttons);
?>
<head>
<script type="text/javascript">
	var Pic = new Array;
<?
			$a=0;
			foreach (array_keys($liste_fichier) as $f) {
				$lien = "$config[url_racine]$rep/$f";
				if (is_image($f)) {
					print "Pic[$a] = \"$lien\";\n";
					$a++;
				}
			}
?>

var t 
var j = 0 
var p = Pic.length
var al = false
var preLoad = new Array()

function plImage(j) {
	preLoad[j] = new Image() 
	preLoad[j].src = Pic[j]
}
plImage(0);
function runSlideShow(){
	if (false && document.all){ 
		document.images.SlideShow.style.filter="blendTrans(duration=3)" 
		document.images.SlideShow.filters.blendTrans.Apply()  
	}
	document.images.SlideShow.src = Pic[j] 
	if (false && document.all){ 
		document.images.SlideShow.filters.blendTrans.Play() 
	} 
	j = j + 1 
	if (j > (p-1)) {
		j = 0
		al = true
	}
	t = setTimeout('runSlideShow()', 2500);
	if (!al) {
		plImage(j);
	}
}
window.onload = runSlideShow;
</script>
</head>
<body onload="runSlideShow()">
<div align="center"><img src="<? print $lien; ?>" name='SlideShow' height=100%></div>
</body>
<?
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	REVERSE
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "reverse";
		$filename = "$config[racine]$rep/$fic";
		$r = fopen($filename, "rb");
		$w = fopen($filename.".rvs","wb+");
		$l = filesize($filename);
		$messtmp = "reverse de <b>$filename</b>";
		if ($l<8000000) {
			fwrite($w,strrev(fread($r, $l)));
		} else {
			$step = 100000;
			for($i=$l;$i>=$step;$i-=$step) {
				fseek($r, $i-$step, SEEK_SET);
				fwrite($w, strrev(fread($r, $step)));
			}
			if ($i>0) {
				fseek($r, 0, SEEK_SET);
				fwrite($w, strrev(fread($r, $i)));
			}
		}
		fclose($r);
		fclose($w);
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	VIEW
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "view";
		$files = array_unique(explode("/", $fics));
		if (is_array($files) && count($files) > (($files[0] == "") ? 2 : 1)) {
			$buttons = mkImgHTML("$PHP_SELF?id=$id&rep=$rep&op=$op&ordre=$ordre&sens=$sens", "exit", "$mess[86]") . "&nbsp;\n";
			headerHTML(2, $rep, $rep, $buttons);
			foreach ($files as $f) {
				$lien = "$config[url_racine]$rep/$f";
				if (is_image($f)) {
					print "<a href=\"$lien\" target=\"_blank\"><img alt=\"$f\" src=\"$lien\"></a> ";
				}
			}
		} else {
			$file = "$rep/$fic";
			$buttons = "";
			if (!is_media($file)) {
				if (is_editable($file)) {
					$buttons .= mkImgHTML("javascript:dirSubmitAction('edit','$ordre','$sens','$rep','$fic');", "edit", "$mess[51]") . "&nbsp;\n";
				}
				$buttons .= mkImgHTML("javascript:if (self.search) search();", "chercher", "?") . "&nbsp;\n";
				$onload = "document.getElementById('displaydiv').focus();";
				$filecat = "text";
			} else {
				$filecat = "media";
			}

			list ($prevfic, $nextfic) = fichier_proches($rep, $fic, $filecat);
			$buttons .= mkImgHTML("javascript:dirSubmitAction('view','$ordre','$sens','$rep','$prevfic');", "prev", "Precedent") . "&nbsp;\n";
			$buttons .= mkImgHTML("javascript:dirSubmitAction('view','$ordre','$sens','$rep','$nextfic');", "next", "Next") . "&nbsp;\n";

			$buttons .= mkImgHTML("javascript:window.print();", "print", "$mess[90]") . "&nbsp;\n";
			$buttons .= mkImgHTML("javascript:location.reload();", "refresh", "$mess[85]") . "&nbsp;\n";
			$buttons .= mkImgHTML("$config[url_racine]$file", "html", "HTTP") . "&nbsp;\n";

			$buttons .= mkImgHTML("javascript:dirSubmitAction('download','$ordre','$sens','$rep','$fic');", "download", "$mess[88]") . "&nbsp;\n";
			$buttons .= mkImgHTML("javascript:dirSubmitAction('','$ordre','$sens','$rep','$fic');", "exit", "$mess[86]") . "&nbsp;\n";
			headerHTML(2, $fic, mkImgHTML('', mimetype("$config[racine]$file", "image"), '', "ABSMIDDLE") . "$mess[23] : " . $file, $buttons, $onload);
			if (is_image($file)) {
				print "<img src=\"$config[url_racine]$file\">\n";
			} else
				if (is_media($file)) {
					print "<embed src=\"$config[url_racine]$file\" AutoStart=true></embed>";
				} else {
					print "<div id=displaydiv align=left nowrap style='height:$config[displaydivheight];width:$config[tablewidth];border:1px solid $config[tablecolor];overflow:scroll;'>";
					@highlight_file("$config[racine]$file", FALSE);
					print "</div>";
				}
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	INFO
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "info" :
		$buttons = mkImgHTML("$PHP_SELF?id=$id&rep=$rep&op=$op&ordre=$ordre&sens=$sens", "exit", "$mess[86]") . "&nbsp;\n";
		headerHTML(2, $rep, $rep, $buttons);

		$files = array_unique(explode("/", $fics));
		if (is_array($files) && count($files) > (($files[0] == "") ? 2 : 1)) {
			clearstatcache();
			foreach ($files as $f) {
				print "<table style='border:1px solid $config[tablecolor]; border-collapse:collapse;'>";
				if ($f != "") {
					$info = stat("$config[racine]$rep/$f");
					print "\n<tr><td>file</td><td bgcolor=\"$config[tablecolor]\">$f</td></tr>";
					foreach ($info as $k => $v) {
						if (!is_numeric($k))
							print "\n<tr><td bgcolor=\"$config[tablecolor]\">$k</td><td>$v</td></tr>";
					}
				}
				print "</table>";
			}
		} else {
			$file = "$rep/$fic";
			clearstatcache();
			$info = stat("$config[racine]$file");
			print "<table style='border:1px solid $config[tablecolor]; border-collapse:collapse;'>";
			print "\n<tr><td>file</td><td bgcolor=\"$config[tablecolor]\">$file</td></tr>";
			foreach ($info as $k => $v) {
				if (!is_numeric($k))
					print "\n<tr><td bgcolor=\"$config[tablecolor]\">$k</td><td>$v</td></tr>";
			}
			print "</table>";
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	COMPRESS
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "compress";
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}
		$err = "";
		$messtmp = "";
		$fullrep = $config[racine] . $rep;
		$files = array_unique(explode('/', $fics));
		if ($files[0] == "")
			array_shift($files);
		if (!is_array($files) || (count($files) == 1 && !is_dir($fullrep . '/' . $files[0]))) {
			$fic = $files[0];
			gzcompressfile("$fullrep/$fic");
		} else {
			include_once ("tar.class.php");
			if (class_exists('tar')) {
				if (empty ($archivename)) {
					$archivename = (count($files) == 1) ? $files[0] : (($rep == "") ? "root" : basename($rep));
				}
				$archivename .= ".tgz";
				$tar = new tar();
				foreach ($files as $file) {
					$n += tgzcompressfile("$fullrep/$file", $tar);
				}
				$tar->toTar("$fullrep/$archivename", TRUE);
				unset ($tar);
				$messtmp = "<b>$n</b> fichier(s) compresse(s) dans <b>$archivename</b>";
			} else {
				$messtmp = "tar.class.php non trouve";
				$err = 1;
			}
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	RENOMMER / RENAME
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "rename";
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}
		$new = trim($new);
		$file = "$config[racine]$rep/$fic";
		if ($destfic == "") {
			$messtmp .= "$mess[37]";
			$err = 1;
		} else
			if (file_exists($new)) {
				$messtmp .= "<b>$fic_new</b> $mess[43]";
				$err = 1;
			} else {
				if (file_exists($file)) {
					rename($file, dirname($file) . "/" . $destfic);
				}
				$messtmp .= "<b>$fic</b> $mess[41] <b>$destfic</b>";
			}

		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	MKDIR
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "mkdir";
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}
		$err = "";
		$messtmp = "";
		$nomdir = traite_nom_fichier($nomdir);
		if ($nomdir == "") {
			$messtmp .= "$mess[37]";
			$err = 1;
		} else {
			if (file_exists("$config[racine]$rep/$nomdir")) {
				$messtmp .= "$mess[40]";
				$err = 1;
			} else {
				mkdir("$config[racine]$rep/$nomdir", 0775);
				$messtmp .= "$mess[38] <b>$nomdir</b> $mess[39] <b>$rep</b>";
			}
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	CREATE FILE
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "newfile";
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}
		$err = "";
		$messtmp = "";
		$nomfic = traite_nom_fichier($nomfic);
		if ($nomfic == "") {
			$messtmp .= "$mess[37]";
			$err = 1;
		} else
			if (file_exists("$config[racine]$rep/$nomfic")) {
				$messtmp .= "$mess[71]";
				$err = 1;
			} else {
				$fp = fopen("$config[racine]$rep/$nomfic", "w");
				if (eregi("\.html$", $nomfic) || eregi("\.htm$", $nomfic)) {
					fputs($fp, "<html>\n<head>\n<title>Document sans titre</title>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n</head>\n<body bgcolor=\"#FFFFFF\" text=\"#000000\">\n\n</body>\n</html>\n");
				}
				fclose($fp);
				$messtmp .= "$mess[34] <b>$nomfic</b> $mess[39] <b>$rep</b>";
			}

		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	UPLOAD
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "upload";
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}
		$messtmp = "";
		$destination = $config[racine] . $rep;
		$taille_ko = ($userfile_size != 0) ? $userfile_size / 1024 : 0;
		if ($userfile != "none" && $userfile_size != 0) {
			$userfile_name = traite_nom_fichier($userfile_name);
			if (!copy($userfile, "$destination/$userfile_name")) {
				$message = "<br>$mess[33]<br>$userfile_name";
			} else {
				if (is_editable($userfile_name)) {
					enlever_controlM("$destination/$userfile_name");
				}
				$message = "$mess[34] <b>$userfile_name</b> $mess[35] <b>$rep</b>";
			}
		} else {
			$message = $mess[31];
		}
		$messtmp .= "$message<br>";
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	COPIER WEB
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "copierweb";
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}
		$messtmp = "";
		$fic = trim(basename($url));
		$destination = $config[racine] . $rep;
		if (!empty ($url)) {
			$dl = download($url, "$destination/$fic");
			if ($dl == "") {
				$messtmp = "Ecriture de : <b>$fic</b>";
			} else {
				$messtmp = "Erreur:" . $dl;
				$err = 1;
			}
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	VERIFICATION LOGIN/PASSE
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "verif";
		foreach ($config[auth_user] as $l => $p) {
			if ($login == $l && $passe == $p && $login != "" && $passe != "") {
				creer_id($config[racine], $config[url_racine], $l);
				$ok = 1;
			}
		}

		if ($ok == 1) {
			header("Location:$PHP_SELF?id=$id");
		} else {
			header("Location:$PHP_SELF?err=1");
		}
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	DECONNEXION
		//-----------------------------------------------------------------------------------------------------------------------------------------

	case "deconnexion";
		if (!connecte($id)) {
			header("Location:$PHP_SELF");
			exit;
		}
		// EFFACE LE LOG DU USER
		if (file_exists("$config[racine]/sessions/$id.php")) {
			unlink("$config[racine]/sessions/$id.php");
		}

		//EFFACE LES LOGS DE PLUS DE 24H
		$now = time();
		$eff = $now - (24 * 3600);
		$handle = opendir("$config[racine]/sessions");
		while ($file = readdir($handle)) {
			if ($file != "." && $file != "..") {
				$tmp = filemtime("$config[racine]/sessions/$file");
				if ($tmp < $eff) {
					unlink("$config[racine]/sessions/$file");
				}
			}
		}
		closedir($handle);
		header("Location:$PHP_SELF");
		break;

		//-----------------------------------------------------------------------------------------------------------------------------------------
		//	DEFAUT
		//-----------------------------------------------------------------------------------------------------------------------------------------

	default;
		if (!connecte($id)) {
			headerHTML(2, "Connection");
			print "<span " . (($err == 1) ? "color=\"#FF0033\"" : "") . ">";
			print "<br><b>$mess[94]</b><input type=\"text\" class=text name=\"login\">\n";
			print "<br><b>$mess[78]</b><input type=\"password\" class=text name=\"passe\">\n";
			print "<input type=\"hidden\" name=\"action\" value=\"verif\"><br><br>\n";
			print "<input type=\"submit\" class=btn name=\"Submit\" value=\"$mess[77]\">\n";
			print "</span>\n";
		} else {
			headerHTML(0);
			if ($op == 1) {
				printActionForms();
			}
			printDirectoryContent();
		}
		break;
}
if ($hasHeader > -1) {
	footerHTML();
} else {
	header("Location:$PHP_SELF?id=$id&ordre=$ordre&sens=$sens&rep=$rep&op=$op&fic=$fic&savefics=" . urlencode($savefics) . "&saverep=" . urlencode($saverep) . "&saveaction=$saveaction&err=$err&msg=" . urlencode($messtmp));
}
?>