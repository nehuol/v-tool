<?php

#phpinfo(); exit();

session_start();
#echo session_id();
#phpinfo();

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", "on");

include("lib/functions.inc");

$mysqli = mysqli_connect($db_host, $db_user, $db_password, $db_name);
mysqli_set_charset($mysqli, "utf8");

include("lib/suchformulare.php");	

// Abmelden
if($logout==1)
{
	unset($_SESSION);
}

// Loginversuch
if($_POST["user"]!="" && $_POST["pass"]!="")
{
	$user = substr($_POST["user"],0,15);
	$pass = substr($_POST["pass"],0,15);
	
	$user = mysqli_real_escape_string($mysqli, $user);
	$pass = mysqli_real_escape_string($mysqli, $pass);
	
	$query = 'select * from Login where user = "'.$user.'" and pass="'.$pass.'"';
	$result = mysqli_query($mysqli, $query);
	#echo '<br>'.$query.mysqli_error();
	
	if(mysqli_num_rows($result)!=1)
	{
		$login_hinweis = '<br><br><b>Zugangsdaten falsch</b>';
	}
	else
	{
		$row = mysqli_fetch_assoc($result);
		$kunde = $row["kunde"];

		$_SESSION["bearbeiter"] = $row;
	}
}

#echo '<pre>';
#print_r($_SESSION);
#echo '</pre>';
#exit();

### Ausgaben

echo $html_header;

// Menue bzw. Loginmaske zusammenbauen
if($_SESSION["bearbeiter"]["id"]=="")
{
	$menu = '
		<br><br><h3 class="welcome">Herzlich willkommen</h3>
		<form name="login" method="post" action="index.php">
		<table border="0" cellspacing="15" cellpadding="0">
			<tr><td><b>Benutzername</b></td><td><input type="text" name="user" size="25" value=""></td>
			<tr><td><b>Passwort</b></td><td><input type="password" name="pass" size="25" value=""></td>
			<tr><td colspan="2"><input type="submit" value="einloggen">'.$login_hinweis.'</td></tr>
		</table>
		</form>';
}
else
{
	$menu .= '<b>Hallo,</b> ';
	$menu .= $_SESSION["bearbeiter"]["vorname"].' '.$_SESSION["bearbeiter"]["nachname"].'&nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php?logout=1"><b>Abmelden</b></a>';
	$menu .= '<br>';
	
	// Admin
	if($_SESSION["bearbeiter"]["berechtigung"]=="Admin")
	{
		//$menu .= '<br><h3>Zählerverwaltung</h3>';
		$menu .= '<br><br>';
		$menu .= '<ul>';
		$menu .= '<li><a href="index.php?hauptansicht=liste&liste=objekte" ><img src="./bilder/navi_objekte_off.png"></a></li>';
		$menu .= '<li><a href="index.php?hauptansicht=liste&liste=wohnungen" ><img src="./bilder/navi_wohnungen_off.png"></a></li>';
		$menu .= '<li><a href="index.php?hauptansicht=liste&liste=zaehler" ><img src="./bilder/navi_zaehler_off.png"></a></li>';
		$menu .= '<li><a href="index.php?hauptansicht=liste&liste=export" ><img src="./bilder/navi_export_off.png"></a></li>';
		$menu .= '</ul>';
		
	}
	// Abteilung
	else
	{
		//$menu .= '<br><br><h3>Zählerverwaltung</h3>';
		$menu .= '<br><br>';
		$menu .= '<ul>';
		$menu .= '<li><a href="index.php?hauptansicht=liste&liste=ablesen"><img src="./bilder/navi_zaehler_off.png"></a></li>';
		$menu .= '</ul>';
	}

}

echo '<div id="navi">'.$menu.'</div>';
echo '<div id="inhalt">';


// Unterscheidung der Hauptansichten
if($hauptansicht=="liste")
{
	#echo '<h3>'.$kunde["name"].'</h3>';

	#echo '<a href="listen/liste_'.$_SESSION["bearbeiter"]["pass"].'.htm" target="_print"><b>Druckansicht</b></a>&nbsp;&nbsp;<br><br>';
	
	if($liste=="export")
	{
		echo '<h3>Export</h3><br><a class="btn_rot" href="listen/liste_'.$_SESSION["bearbeiter"]["pass"].'.csv" target="_print"><b>Excel-Download</b></a>&nbsp;&nbsp;<br><br>';
	}

	include("liste.php");
}
elseif($hauptansicht=="pass")
{
	#echo '<h3>'.$kunde["name"].'</h3>';	
	
	if($print_me!=1)
	{
		if(isset($zurueck_link) && $zurueck_link!="")
		{
				echo '<a class="btn_blau" href="index.php?'.urldecode($zurueck_link).'"><b>Zur&uuml;ck zur Liste</b></a>';# - <a href="listen/pass.htm" target="_print"><b>Druckansicht</b></a>';
		}
		else
		{
			#echo '<a href="index.php"><b>zum Men&uuml;</b></a>';# - <a href="listen/pass.htm" target="_print"><b>Druckansicht</b></a>';
		}
	}

	include("pass.php");
}
elseif($hauptansicht=="backup")
{
	#echo '<a href="index.php"><b>zum Men&uuml;</b></a><br><br>';
	include("backup.php");
}
else 
{
	if(is_array($_SESSION["bearbeiter"]))
	{
		echo '
			<h4>&nbsp;&nbsp;Bitte wählen Sie einen Menüpunkt aus. </h4>';
	}
}

echo '<br><br></div>';

echo $html_footer;

?>