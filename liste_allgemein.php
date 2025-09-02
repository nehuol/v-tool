<?php

#phpinfo();

#echo '<pre>';
#print_r($_POST);
#echo '</pre>';

// Tabellen updaten, falls Liste gespeichert werden soll
if($update_aktiv==1)
{
	foreach($master_id as $key=>$val)
	{
		$update_zaehler = '';

		foreach($felder as $key1=>$val1)
		{
			#echo '<br> '.$felder[$key1].' - '.$key1.' - '.${$key1}[$key].' - '.$val1;
			// nur text (falls nicht disabled), radio und select beachten
			if((substr($felder[$key1],0,4)=="text" && substr($felder[$key1],5)!="disabled") || $felder[$key1]=="select" || substr($felder[$key1],0,5)=="radio" || substr($felder[$key1],0,5)=="check" || substr($felder[$key1],0,5)=="datum")
			{
				$act_val = ${$key1}[$key];				
				
				// Datumsspalten aufbereiten
				if(substr($felder[$key1],0,5)=="datum")
				{
					$act_val = substr($act_val,6,4)."-".substr($act_val,3,2)."-".substr($act_val,0,2);
					
					if($act_val=="--")
					{
						$act_val = "";
					}
				}
				
				// Dezimaltrenner umwandeln (von Komma nach Punkt)
				if(substr_count($act_val, ",")==1 && !preg_match("/[a-zA-Z]/", $act_val))
				{
					$act_val = str_replace(",", ".", $act_val);
				}				
				
				$temp = explode("___", $key1);
				
				if($temp[0]=="zaehler")
				{
					$update_zaehler .= '`'.$temp[1].'` = "'.$act_val.'", ';
				}
			}
		}

		if($update_zaehler!="")
		{
			$update_zaehler = 'update Zaehler set '.substr($update_zaehler,0,-2).' where ID = '.$key;
			mysqli_query($mysqli, $update_zaehler);
			#echo '<br>'.$update_zaehler.mysqli_error($mysqli);
			
			if($liste=="ablesen" && mysqli_affected_rows($mysqli)==1)
			{
				$zaehler_temp = getZaehler($mysqli, $key);
				
				$hinweis_out .= ''.$zaehler_temp["Art"].' mit Zählernummer '.$zaehler_temp["Nummer"].' wurde gespeichert!<br>';
			}
		}
	}
}
// einzelnen Datensatz löschen
elseif($delete_aktiv==1)
{
	if($liste=="zaehler")
	{
		$del[] = 'delete from Zaehler where ID = '.$master_id;
	}
	elseif($liste=="wohnungen")
	{
		$del[] = 'delete from Wohnungen where ID = '.$master_id;
		$del[] = 'delete from Zaehler where Wohnung_ID = '.$master_id;
	}
	elseif($liste=="objekte")
	{
		// vorab die Zähler bestimmen und löschen
		$query_del = 'select ID from Wohnungen where Objekt_ID = '.$master_id;
		$result_del = mysqli_query($mysqli, $query_del);
		while($row_del=mysqli_fetch_assoc($result_del))
		{
			$del[] = 'delete from Zaehler where Wohnung_ID = '.$row_del["ID"];
		}
		
		$del[] = 'delete from Objekte where ID = '.$master_id;
		$del[] = 'delete from Wohnungen where Objekt_ID = '.$master_id;
	}
	
	foreach($del as $key=>$val)
	{
		mysqli_query($mysqli, $val);
		#echo '<br>'.$val.mysqli_error($mysqli);
	}
}


// Kopfzeile mit Spalten-Überschriften vorbereiten
if($order_sort=="" || $order_sort=="a")
{
	$order_sort_out = "d";
}
else
{
	$order_sort_out = "a";
}

foreach($felder as $key=>$val)
{
	$spaltenkopf_out = substr($key,strpos($key,"___")+3);

	// ggfs. Spaltennamen umbiegen
	$spaltenkopf_out = format_pass($key);

	$href = '';
	// Sortierlinks (nicht bei Bearbeiten-Links)
	if($key!="___Bearbeiten" && $key!="___Loeschen" && $key!="___Versand" && $key!="___Kopieren" && $key!="___Auswertung" && $key!="___Inaktiv" && $key!="mitarbeiter___bild")
	{
		$href = '<a href="index.php?hauptansicht=liste&liste='.$liste.'&order_sort='.$order_sort_out.'&spalte_sort='.$key.$suche_get.'">';
	}

	$kopfzeile .= '<td class="'.$key.'"><b>'.$href.$spaltenkopf_out.'</a></b></td>';
	
	$out_csv_head[] = $spaltenkopf_out;
}

#### Ausgaben über Liste (Suchformular)

// ausgewähltes Suchformular anzeigen
$suche_out .= $suche;
if($suche_out!="")
{
	echo '<b>Suchformular</b><br><table class="t_border" cellspacing="3" cellpadding="0" bordercolor="#000000"><tr><td valign="top">';
	echo $suche_out;
	echo '</td></tr></table><br>';
}

### Ende Ausgaben über der Liste


// Ausgabezeilen vorbereiten
$result = mysqli_query($mysqli, $sql);
#echo "<hr>".$sql."<br>".mysqli_error($mysqli)."<hr>";

if(mysqli_num_rows($result)==0)
{
	echo '<b>Keine Treffer</b>';
}

while($row=mysqli_fetch_assoc($result))
{
	$treffer_zaehler++;
	
	if($liste=="objekte")
	{
		$daten = getObjekt($mysqli, $row["ID"]);
		$sort_standard = "Name";
	}
	elseif($liste=="wohnungen")
	{
		$daten = getWohnung($mysqli, $row["ID"]);
		$sort_standard = "Objekt_Name";
	}
	elseif($liste=="zaehler" || $liste=="ablesen" || $liste=="export")
	{
		$daten = getZaehler($mysqli, $row["ID"]);
		$sort_standard = "Wohnung_Name";
	}

	foreach($felder as $key=>$val)
	{
		// $key ohne führenden Tabellen-Namen
		$key_int = substr($key,strpos($key,"___")+3);

		#echo '<br>'.$key.' - '.$val.' - '.$daten[$key_int];

		// Sortierung vorbereiten (Standardsortierung auf Weingutname!)
		if($key==$spalte_sort || ($spalte_sort=="" && $key_int==$sort_standard))
		{
			$zeile_sort[$row["ID"]] = $daten[$key_int];
		}

		###echo '<br>'.$key;
	
		// falls xxx
		if($key=="umfrage___abgeschlossen")
		{
			$ret_temp = "";
			
			$query_temp = 'select * from Mitarbeiter, UmfrageMitarbeiter where
				Mitarbeiter.id = UmfrageMitarbeiter.mitarbeiter_id and
				UmfrageMitarbeiter.umfrage_id = '.$row["id"].'
					order by Mitarbeiter.nachname';
				
			$result_temp = mysqli_query($mysqli, $query_temp);
			while($row_temp=mysqli_fetch_assoc($result_temp))
			{
				if($row_temp["abgeschlossen"]!="")
				{
					$umfrage_erledigt = '<img src="bilder/allgemein/gruen.png" height="10">';
				}
				else
				{
					$umfrage_erledigt = '<img src="bilder/allgemein/rot.png" height="10">';
				}
				
				$ret_temp .= $umfrage_erledigt.' '.$row_temp["nachname"].' '.$row_temp["vorname"].'<br>';
			}
			
			
			$zeile[$row["id"]] .= td($key, $val, $row["id"], $ret_temp, 1);
			
			// Ausgabe zum Drucken
			$zeile_print[$row["id"]] .= '<td valign="top" width="500">'.$daten[$key_int].'</td>';		
		}

		// Standardanzeige
		else
		{
			$zeile[$row["ID"]] .= td($key, $val, $row["ID"], $daten[$key_int]);
			
			// Ausgabe zum Drucken
			$zeile_print[$row["ID"]] .= td($key, "plain@3000", $row["ID"], $daten[$key_int]);
			
			// Ausgabe als CSV
			$zeile_csv[$row["ID"]] .= ($daten[$key_int]).';';
		}
	}

	// immer noch ein hidden-Feld pro Zeile anhängen (praktisch als Laufvariable bei den Updates!)
	$zeile[$row["ID"]] .= '<input type="hidden" name="master_id['.$row["ID"].']" value="'.$row["ID"].'">';
}

// Ausgabezeilen anzeigen
if($zeile)
{
	if($treffer_zaehler>0)
	{
		echo '<h3>'.$hinweis_out.' </h3>';
		echo '<br><br>';
		echo '<b>'.$treffer_zaehler.' Treffer</b>';
	}
	
	echo '<table cellspacing="0" cellpadding="2" border="0" class="t_border">';
	echo '<form name="update" action="index.php?'.$_SERVER["QUERY_STRING"].'" method="post">';
	echo '<input type="hidden" name="update_aktiv" value="1">';

	if($order_sort=="d")
	{
		arsort($zeile_sort);
	}
	else
	{
		asort($zeile_sort);
	}
	

	foreach($zeile_sort as $key=>$val)
	{
		if(++$i%2==0)
			$bg = 'class="odd"';
		else
			$bg = 'class="even"';

		if($i%$kopfzeile_einblenden==1)
		{
			if($liste=="zaehler" || $liste=="ablesen")
			{
				echo '<tr><td align="left" colspan="'.count($felder).'"><input class="btn_liste_speichern" type="submit" value="---   Liste speichern   ---">'.$button_sepa.'</td></tr>';
			}
			
			echo '<tr>'.$kopfzeile.'</tr>';
			$out_print .= '<tr>'.str_replace('a href', 'keintag ', $kopfzeile).'</tr>';
		}
	
		echo '<tr '.$bg.'>'.$zeile[$key].'</tr>';
				
		// Ausgabe zum Drucken
		$out_print .= '<tr '.$bg.'>'.$zeile_print[$key].'</tr>';
		
		// Ausgabe als CSV
		$out_csv .= utf8_decode($zeile_csv[$key]).chr(13).chr(10);
	}

	// ggfs. Formulareingaben aus dem Suchformular mitziehen
	echo $suche_post;

	echo '</form>';
	echo '</table>';
	
	// echo $hinweis_out;
}

echo '<script type="text/javascript">'.$js_out.'</script>';

// Ausgabe zum Drucken
$fp = fopen('listen/liste_'.$_SESSION["bearbeiter"]["pass"].'.htm', 'w+');
fputs($fp, '<link rel="stylesheet" type="text/css" href="/lib/standard.css"><table cellspacing="0" cellpadding="2" border="1" width="100%">'.utf8_decode($out_print).'</table>');
fclose($fp);

// Ausgabe als CSV
$fp = fopen('listen/liste_'.$_SESSION["bearbeiter"]["pass"].'.csv', 'w+');
fputs($fp, implode(";", $out_csv_head).chr(13).chr(10).$out_csv);
fclose($fp);

?>