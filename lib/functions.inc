<?php

$db_user = 'd02c91f4';
$db_password = 'mrSPK1921!';
$db_host = 'localhost';
$db_name = 'd02c91f4';

// --> Notlösung über Umgebungsvariable GET
if(count($_GET)>0)
{
   foreach($_GET as $key=>$val)
   {
      $$key = $val;
      #echo '<br>'.$key.' --> '.$val;
   }
}

// --> Notlösung über Umgebungsvariable POST
if(count($_POST)>0)
{
   foreach($_POST as $key=>$val)
   {
      $$key = $val;
      #echo '<br>'.$key.' --> '.$val;
   }
}

// Darstellung einer td-Zelle in allen Masken
function td($key, $val, $id, $inhalt, $faellig = 0)
{
	#echo '<br>'.$key.' - '.$val.' - '.substr($val,0,13).' - '.$id.' - <pre>'.$inhalt.'</pre>';

	// aktuell gewählte Firma
	global $kunde;
	global $mysqli;

	// hier sind die Auswahlmöglichkeiten für select-Felder hinterlegt
	global $select;

	// für die Rückverlinkung von Pass auf Liste
	global $suche_get;
	global $hauptansicht;
	global $ansicht;
	global $liste;

	// für Druckausgabe
	global $value_print;

	// JS-Prüfschnippsel
	global $js_form;
	
	// für den Mailversand
	global $mail_header;
	global $mail_footer;
	
	// für die kompletten Daten des aktuellen Satzes
	global $daten;


	// falls $val "§§" enthält, dann soll das Feld per JavaScript geprüft werden
	$temp = explode("§§", $val);
	$val = $temp[0];
	$js_check = $temp[1] ?? $temp[0];

	// Ausgabe als Text
	if(substr($val,0,5)=="plain")
	{
		if($inhalt=="0000-00-00")
		{
			$inhalt = "";
		}
		
		#echo '<br>'.$inhalt;
		
		
		if(substr($val,6)=="")
		{
			$size = 100;
		}
		elseif(substr($val,6)=="datum" && $inhalt!="")
		{
			// Tabellen-Zelle einfärben, falls faellig = 1 und $inhalt < heute
			if($faellig==1 && $inhalt<=date("Y-m-d"))
			{
				$inhalt = '<div style="background-color:#FF6666; color:#000000;">'.substr($inhalt,8,2).".".substr($inhalt,5,2).".".substr($inhalt,0,4).'</div>';
				$size = 500;
			}
			else
			{
				$inhalt = substr($inhalt,8,2).".".substr($inhalt,5,2).".".substr($inhalt,0,4);				
				$size = 10;
			}			
		}
		else
		{
			$size = substr($val,6);
		}

		if($size=="datum")
		{
			$size = "10";
		}

		$ret = substr($inhalt,0,$size);
		
		// als Tooltip darstellen
		if(substr($val,6)=="tooltip")
		{
			$inhalt_tt = nl2br($inhalt);
			$inhalt_tt = str_replace("'", "", $inhalt_tt);
			$inhalt_tt = str_replace('"', '', $inhalt_tt);
			$inhalt_tt = str_replace(chr(13), '', $inhalt_tt);
			$inhalt_tt = str_replace(chr(10), '', $inhalt_tt);
			
			$ret = '<span onmouseover="Tip(\''.$inhalt_tt.'\', TITLE, \'Beschreibung\');">'.substr($inhalt,0,50).'</span>';
		}
	}
	// fixen Text anzeigen
	elseif(substr($val,0,3)=="fix")
	{
		$ret = substr($val,4);
	}
	elseif($val=="datum")
	{
		// leere Datumsangaben abfangen
		if($inhalt!="" && $inhalt!="0000-00-00")
		{
			$inhalt = substr($inhalt,8,2).".".substr($inhalt,5,2).".".substr($inhalt,0,4);
		}
		else
		{
			$inhalt = "";
			$extra = 'onclick="this.value=\''.date("d.m.Y").'\'"';
		}

		$ret = '<input type="text" name="'.$key.'['.$id.']" value="'.$inhalt.'" size="10" '.$extra.'>';
		
		// Javascript-Prüfung auf Inhalt
		if($js_check=="empty")
		{
			$js_form .= '
				if(document.getElementsByName("'.$key.'['.$id.']")[0].value.replace(/\s+$/,"").replace(/^\s+/,"")=="")
				{
					err_msg += "Bitte \"'.ucfirst(substr($key,strpos($key,"___")+3)).'\" angeben!\n";
				}';
		}		
	}
	// Ausgabe als Eingabe-Feld
	elseif(substr($val,0,4)=="text")
	{
		if(substr($val,5)=="")
		{
			$extra = 'size="10"';
		}
		elseif(substr($val,5)=="disabled")
		{
			$extra = 'size="5" disabled';
		}
		else
		{
			$extra = 'size='.substr($val,5);
		}

		// Sonderzeichen ersetzen
		$inhalt = str_replace('"', '&quot;', $inhalt);

		// Dezimaltrenner umwandeln (von Punkt nach Komma)
		if(floatval($inhalt)==$inhalt && substr_count($inhalt, ".")==1 && !preg_match("/[a-zA-Z]/", $inhalt))
		{
			#echo '<br>'.floatval($inhalt).'=='.$inhalt;
			$inhalt = str_replace(".", ",", $inhalt);
		}

		$ret = '<input type="text" name="'.$key.'['.$id.']" value="'.$inhalt.'" '.$extra.'>';

		// Feldbezeichnung für JS-Ausgabe
		if($transform[substr($key,strpos($key,"___")+3)]!="")
		{
			$js_name = $transform[substr($key,strpos($key,"___")+3)];
		}
		else
		{
			$js_name = substr($key,strpos($key,"___")+3);
		}

		// Javascript-Prüfung auf Inhalt
		if($js_check=="empty")
		{
			$js_form .= '
				if(document.getElementsByName("'.$key.'['.$id.']")[0].value.replace(/\s+$/,"").replace(/^\s+/,"")=="")
				{
					err_msg += "Bitte \"'.ucfirst($js_name).'\" angeben!\n";
				}';
		}
		// Javascript-Prüfung auf Leerzeichen
		elseif($js_check=="space")
		{
			$js_form .= '
				if(document.getElementsByName("'.$key.'['.$id.']")[0].value.indexOf(" ")>=0)
				{
					err_msg += "Keine Leerzeichen in '.strip_tags(translate($js_name, $lang, 0)).' erlaubt!\n";
				}';
		}
		// Javascript-Prüfung auf Leerzeichen
		elseif($js_check=="leerundnull")
		{
			$js_form .= '
				if(document.getElementsByName("'.$key.'['.$id.']")[0].value.replace(/\s+$/,"").replace(/^\s+/,"")=="" || document.getElementsByName("'.$key.'['.$id.']")[0].value=="0")
				{
					err_msg += "'.translate("js_bitteangeben_1",$lang,0).'\"'.strip_tags(translate($js_name, $lang, 0)).'\"'.translate("js_bitteangeben_2",$lang,0).'!\n";
				}';
		}		
	}
	// Ausgabe als Textarea
	elseif(substr($val,0,4)=="area")
	{
		$extra = explode("|", substr($val,5));

		$ret = '<textarea name="'.$key.'['.$id.']" id="'.$key.'_'.$id.'" cols="'.$extra[0].'" rows="'.$extra[1].'">'.$inhalt.'</textarea>';
	}
	// Ausgabe als Select-Liste
	elseif(substr($val,0,6)=="select")
	{
		if($hauptansicht=="pass")
		{
			$sel_class = "auto_select_pass";
		}
		elseif($hauptansicht=="liste")
		{
			$sel_class = "auto_select_liste";
		}
		
		// automatisch abschicken, aber nicht speichern
		if(substr($val,7)=="submit")
		{
			$extra .= ' onchange="document.update.update_aktiv.value=0; document.update.submit();"';
		}		

		$ret = '<select name="'.$key.'['.$id.']" class="'.$sel_class.'" '.$extra.'><option value="">Bitte auswählen</option>';

		if(is_array($select[$key]))
		{
			foreach($select[$key] as $key1=>$val1)
			{
				// key oder val als Rückgabewert im dropdown
				if(substr($key1,0,7)=="INT_VAL")
					$value = substr($key1,7);
				else
					$value = $val1;

				#echo '<br>'.$inhalt.' - '.$value;
				$selected = '';

				if($inhalt==$value)
				{
					$selected = 'selected';
					$value_print = $val1;
				}

				$ret .= '<option value="'.$value.'" '.$selected.'>'.substr($val1,0,100).'</option>';
			}
		}

		$ret .= '</select>';

		// Javascript-Prüfung auf Auswahl
		if($js_check=="empty")
		{
			$js_form .= '
				if(document.getElementsByName("'.$key.'['.$id.']")[0].value=="")
				{
					err_msg += "Bitte \"'.ucfirst(substr($key,strpos($key,"___")+3)).'\" auswählen!\n";
				}';
		}
	}
	// Ausgabe als Select-Liste mit Mehrfachauswahl
	elseif(substr($val,0,11)=="multiselect")
	{
		$ret = '<select name="'.$key.'['.$id.'][]" class="'.$sel_class.'" size="'.count($select[$key]).'" multiple>';

		if(is_array($select[$key]))
		{
			foreach($select[$key] as $key1=>$val1)
			{
				// key oder val als Rückgabewert im dropdown
				if(substr($key1,0,7)=="INT_VAL")
					$value = substr($key1,7);
				else
					$value = $val1;

				$selected = '';
				if(is_array($inhalt) && in_array($value, $inhalt))
				{
					$selected = 'selected';
					$value_print = $val1;
				}

				$ret .= '<option value="'.$value.'" '.$selected.'>'.substr($val1,0,60).'</option>';
			}
		}

		$ret .= '</select>';

		// Javascript-Prüfung auf Auswahl
		if($js_check=="empty")
		{
			$js_form .= '
				if(document.getElementsByName("'.$key.'['.$id.']")[0].value=="")
				{
					err_msg += "Bitte \"'.ucfirst(substr($key,strpos($key,"___")+3)).'\" auswählen!\n";
				}';
		}
	}	
	// Ausgabe Radio-Buttons
	elseif(substr($val,0,5)=="radio")
	{
		#echo '<hr>key: '.$key;
		#echo '<br>val: '.$val;
		#echo '<br>id: '.$id;
		#echo '<br>inhalt: '.$inhalt;

		$temp = explode("|", substr($val,6));
		#echo "<br>".$inhalt.":".$temp[0]."|".$temp[1];


		if($inhalt==$temp[0])
		{
			$check_yes = "checked";
			$value_print = translate("ja",$lang);				
		}
		elseif($inhalt==$temp[1])
		{
			$check_no = "checked";
			$value_print = translate("nein",$lang);								
		}

		$out_yes = translate("Ja", $lang);
		if($temp[0]!="1" && $temp[0]!="j")
		{
			$out_yes = $temp[0];
		}

		$out_no = translate("Nein", $lang);
		if($temp[1]!="0" && $temp[1]!="n")
		{
			$out_no = $temp[1];
		}

		$ret = '<nobr><input type="radio" name="'.$key.'['.$id.']" value="'.$temp[0].'" '.$check_yes.'>'.$out_yes.'&nbsp;&nbsp;<input type="radio" name="'.$key.'['.$id.']" value="'.$temp[1].'" '.$check_no.'>'.$out_no.'</nobr>';
		
		// Javascript-Prüfung, Feld darf nicht "Nein" sein
		if($js_check=="empty")
		{
			$err_out = translate("js_bitteangeben_1",$lang,0).'\"'.strip_tags(translate(substr($key,strpos($key,"___")+3), $lang, 0)).'\"'.translate("js_bitteangeben_2",$lang,0).'!';
			
			$js_form .= '
				if(document.getElementsByName("'.$key.'['.$id.']")[0].checked==false)
				{
					err_msg += "'.$err_out.'\n";
				}';
		}

	}
	// Ausgabe als Checkbox
	elseif(substr($val,0,5)=="check")
	{
		if($inhalt==1)
		{
			$extra = 'checked';
		}
		
		if(substr($val,6)=="submit")
		{
			$extra .= ' onclick="if(confirm(\'Sicher?\')) {document.update.submit()} else {this.checked = \'\';}"';
		}
			
		$ret = '<input type="checkbox" name="'.$key.'['.$id.']" value="1" '.$extra.'>';
		
		// Javascript-Prüfung, Feld darf nicht "Nein" sein
		if($js_check=="empty")
		{
			$err_out = 'Bitte '.strip_tags(translate(substr($key,strpos($key,"___")+3), $lang, 0)).' angeben!';					
			
			$js_form .= '
				if(document.getElementsByName("'.$key.'['.$id.']")[0].checked==false)
				{
					err_msg += "'.$err_out.'\n";
				}';
		}		
	}
	// Ausgabe des Bearbeiten-Link
	elseif(substr($val,0,10)=="bearbeiten")
	{
		$zurueck_link = urlencode('hauptansicht='.$hauptansicht.'&liste='.$liste.$suche_get);
		$temp = explode(",", substr($val,strpos($val,"@")+1));
		
		if($temp[1]=="umfrage")
		{
			$ret = '     <b> - <a href="index.php?hauptansicht='.$temp[0].'&ansicht=umfrage1&master_id='.$id.'&zurueck_link='.$zurueck_link.'">Umfrage-Bearbeiter eintragen</a></b>';
			$ret .= '<br><b> - <a href="index.php?hauptansicht='.$temp[0].'&ansicht=umfrage2&master_id='.$id.'&zurueck_link='.$zurueck_link.'">Empfänger-Konfiguration</a></b>';
			$ret .= '<br><b> - <a href="index.php?hauptansicht='.$temp[0].'&ansicht=umfrage3&master_id='.$id.'&zurueck_link='.$zurueck_link.'">Merkmale sortieren</a></b>';
			$ret .= '<br><b> - <a href="index.php?hauptansicht='.$temp[0].'&ansicht=umfrage4&master_id='.$id.'&zurueck_link='.$zurueck_link.'">Kompetenz-Ebenen priorisieren</a></b>';
			$ret .= '<br><b> - <a href="index.php?hauptansicht='.$temp[0].'&ansicht=umfrage5&master_id='.$id.'&zurueck_link='.$zurueck_link.'">Mailtexte bearbeiten</a></b>';
			$ret .= '<br><br>';
		}
		else 
		{
			$ret = '<a href="index.php?hauptansicht='.$temp[0].'&ansicht='.$temp[1].'&master_id='.$id.'&zurueck_link='.$zurueck_link.'">Bearbeiten</a>';
		}
	}
	// Ausgabe des Löschen-Link
	elseif(substr($val,0,8)=="loeschen")
	{
		$ret = '<a href="index.php?hauptansicht='.$hauptansicht.'&liste='.$liste.'&delete_aktiv=1&master_id='.$id.$suche_get.'" onclick="return confirm(\'Wirklich löschen?\');">L&ouml;schen</a>';
	}
	// Ausgabe des Löschen-Link
	elseif(substr($val,0,8)=="inaktiv")
	{
		if($liste=="mitglieder_admin" || $liste=="mitglieder")
		{
			if($daten["aktiv"]==1)
			{
				$ret = '<a href="index.php?hauptansicht='.$hauptansicht.'&liste='.$liste.'&delete_aktiv=2&master_id='.$id.'" onclick="return confirm(\'Das Mitglied wird nur ausgeblendet und kann bei Bedarf wieder hergestellt werden!\\n\\nWirklich fortfahren?\');">ausblenden</a>';
			}
			elseif($daten["aktiv"]==0)
			{
				$ret = '<a href="index.php?hauptansicht='.$hauptansicht.'&liste='.$liste.'&delete_aktiv=3&master_id='.$id.'" onclick="return confirm(\'Das Mitglied wird wieder aktiviert!\\n\\nWirklich fortfahren?\');">einblenden</a>';
			}
			
			
		}		
	}
	// 
	elseif(substr($val,0,4)=="link")
	{
		$temp = substr($val,strpos($val,"@")+1);


		if($temp=="kopieren")
		{
			$ret = '<a href="index.php?hauptansicht=kopieren&umfrage_id='.$id.'">kopieren</a>';
		}
		elseif($temp=="auswertung")
		{
			$ret = '<a href="auswertung.php?umfrage_id='.$id.'">auswerten</a>';
		}


		#if($temp=="rechnung")
		#{
		#	$ret = '<a href="javascript:var x = window.open(\'rechnung2pdf.php?stufe=rechnung&id='.$id.'\',\'Rechnung\',\'toolbar=no,location=no,directories=no,status=no,menubar=no,resizeable=no,width=800,height=1200,scrollbars=yes,top=50,left=400\');">erzeugen</a>';
		#}
	}
	// Ausgabe der Bilderspalte
	elseif(substr($val,0,5)=="image")
	{
			$img_path_short = '/verwaltung/bilder/mitarbeiter/';
			$img_path = $_SERVER["DOCUMENT_ROOT"].$img_path_short;
			
			$bild_name = $id;
			$bilder_vorhanden = 0;
	
			if(file_exists($img_path.$bild_name.'.jpg'))
			{
				$bilder_vorhanden++;
				$bilder_anzeigen .= '<img src='.$img_path_short.$bild_name.'.jpg>';
			}

		$ret = '<a onmouseover="Tip(\''.$bilder_anzeigen.'\', TITLE, \'Bild in Originalgröße\');" href="javascript:var x = window.open(\'tools/bilderpflege.php?hauptansicht='.$hauptansicht.'&liste='.$liste.'&id='.$id.'\',\'bilderpflege\',\'toolbar=no,location=no,directories=no,status=no,menubar=no,resizeable=yes,scrollbars=yes,width=800,height=400,top=50,left=50\');">'.$bilder_vorhanden.' St&uuml;ck</a>';
	}
	// Ausgabe des Datei-Auswählers
	elseif(substr($val,0,4)=="file")
	{
		$ret = '<b><a href="uploads/'.$inhalt.'" target="_blank">'.$inhalt.'</a></b><br><br><input type="file" name="probe">';
	}
	// Downloader
	elseif(substr($val,0,8)=="download")
	{
		$ret = '<a href="uploads/'.$inhalt.'" target="_blank">'.$inhalt.'</a>';
	}
	
	
	// Zelle beim Erstaufruf anzeigen?
	$css_visi = '';
	if($hauptansicht=="liste")
	{
		if((is_array($not_visible) && in_array($key,$not_visible) && $_SESSION["checker"][$key]!=1) || $_SESSION["checker"][$key]===0)
		{
			$css_visi = 'style="display:none;"';
		}
	}
	
	return '<td class="'.$key.'" id="'.$id.'" valign="" '.$css_visi.'>'.$ret.'</td>'.chr(13).chr(10);

}

// Dropdown-Menü erzeugen
function dropdown($mysqli, $table, $field_ext, $field_int)
{
	$query = 'select distinct '.$field_ext.', '.$field_int.' from '.$table.' where '.$field_ext.'!="" and '.$field_ext.' is not NULL group by '.$field_ext.' order by '.$field_ext;
	$result = mysqli_query($mysqli, $query);
	#echo "<hr>".$query."<br>".mysqli_error($mysqli);
	while($row = mysqli_fetch_array($result))
	{
		if($field_ext!=$field_int)
			$ret["INT_VAL".$row[1]] = $row[0];
		else
			$ret[] = $row[0];
	}

	return $ret;
}

function getObjekt($mysqli, $id)
{
	$query = 'select * from Objekte where ID = '.$id;
	$result = mysqli_query($mysqli, $query);
	$ret = mysqli_fetch_assoc($result);
	
	return $ret;
}

function getWohnung($mysqli, $id)
{
	$query = 'select Wohnungen.*, Objekte.Name as Objekt_Name from Wohnungen left join Objekte on Objekte.ID = Wohnungen.Objekt_ID where Wohnungen.ID = '.$id;
	$result = mysqli_query($mysqli, $query);
	$ret = mysqli_fetch_assoc($result);
	
	return $ret;
}

function getZaehler($mysqli, $id)
{
	$query = '
		select Zaehler.*, Wohnungen.Name as Wohnung_Name, Objekte.Name as Objekt_Name, Objekte.ID as Objekt_ID from Zaehler
			left join Wohnungen on Wohnungen.ID = Zaehler.Wohnung_ID
			left join Objekte on Objekte.ID = Wohnungen.Objekt_ID
			where 
			Zaehler.ID = '.$id;
		
		
	$result = mysqli_query($mysqli, $query);
	$ret = mysqli_fetch_assoc($result);
	
	return $ret;
}

function getLogin($mysqli, $id)
{
	$query = 'select * from Login where id = '.$id;
	$result = mysqli_query($mysqli, $query);
	$ret = mysqli_fetch_assoc($result);
	
	return $ret;
}


// Spaltennamen im Pass formatieren
function format_pass($key)
{
	global $liste;
	global $ansicht;
	
	$spalte = substr($key,strpos($key,"___")+3);

	if($spalte=="Objekt_Name" || $spalte=="Objekt_ID")
	{
		$spaltenname_out = "Objekt";
	}
	elseif($spalte=="Wohnung_Name" || $spalte=="Wohnung_ID")
	{
		// Für die Zählerliste
		$spaltenname_out = "Verbraucher";
	}
	elseif($spalte=="Name" && $liste=="wohnungen")
	{
		$spaltenname_out = "Verbraucher";
	}
		
	elseif($spalte=="Nummer" && $liste=="wohnungen")
	{
		$spaltenname_out = "Besonderheiten";
	}
	
	

	
	
	elseif($spalte=="Bearbeiten")
	{
		$spaltenname_out = "";
	}
	elseif($spalte=="Loeschen")
	{
		$spaltenname_out = "";
	}
	
	else
	{
		$spaltenname_out = ucfirst($spalte);
	}

	$spaltenname_out = strip_tags($spaltenname_out);
	
	return $spaltenname_out;
}

// Auswahllisten für die diversen Dropdowns vorbelegen
$select["zaehler___Art"][] = "GW";	
$select["zaehler___Art"][] = "KW";	
$select["zaehler___Art"][] = "KW f. WW";
$select["zaehler___Art"][] = "WW";	
$select["zaehler___Art"][] = "WMZ";	
$select["zaehler___Art"][] = "Strom";	
$select["zaehler___Art"][] = "Gas";	
$select["zaehler___Art"][] = "Öl";	


$html_header = '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>C.-W. Müller GmbH - Zählerverwaltung</title>

<link rel="stylesheet" type="text/css" href="lib/standard.css">

<script type="text/javascript" src="https://code.jquery.com/jquery-1.5.min.js"></script>
<script type="text/javascript" src="lib/allgemein.js"></script>

</head>

<body style="overflow:scrolling">

<header>
	<div class="header_inner">
		<table border="0" cellspacing="0" cellpadding="8">
			<tr>
				<td><img src="bilder/logo.png"></td>
			</tr>
		</table>
	</div>
</header>

<div id="container"><section id="content">';

$html_footer = '</section></div>

<footer>
	<div class="inner">
		<div id="footer_inner">
		<p>C.-W. Müller GmbH &copy; 2018</p>
		</div>
	</div>
</footer>

</body>
</html>';

?>