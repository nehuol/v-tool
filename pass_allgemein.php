<?php

#phpinfo();
#echo '<pre>';
#print_r($_POST);
#print_r($_FILES);
#echo '</pre>';


// Tabellen updaten, falls Kundenpass gespeichert werden soll
if($update_aktiv==1)
{
	if($master_id=="")
	{
		$master_id = "neu";
	}
	
	// Datei abholen, falls Upload ausgeführt wurde
	if(isset($_FILES['probe']) && ! $_FILES['probe']['error'])
	{
	  move_uploaded_file($_FILES['probe']['tmp_name'], $_SERVER["DOCUMENT_ROOT"].'/verwaltung/uploads/'.$_FILES['probe']['name']);
	  
	  // und noch den Dateinamen setzen
	  $dokument___datei[$master_id] = $_FILES['probe']['name'];
	}	

	// INSERTS und UPDATES zusammenbauen
	foreach($felder as $key1=>$val1)
	{
		#echo '<br> '.$felder[$key1].' - '.$key1.' - '.${$key1}[$master_id];
		
		if((substr($felder[$key1],0,4)=="text" && substr($felder[$key1],5)!="disabled") || substr($felder[$key1],0,6)=="select" || substr($felder[$key1],0,5)=="radio" || substr($felder[$key1],0,4)=="area" || substr($felder[$key1],0,5)=="datum" || substr($felder[$key1],0,4)=="file")
		{
			// Aktueller Feld-Inhalt (Spezialbehandlung bei Datums-Typ)
			if(substr($felder[$key1],0,5)=="datum")
			{
				$act_val = ${$key1}[$master_id];
				$act_val = substr($act_val,6,4)."-".substr($act_val,3,2)."-".substr($act_val,0,2);
			}
			else
			{
				$act_val = ${$key1}[$master_id];
			}

			$temp = explode("___", $key1);

			#echo '<br>'.$key1.' - '.$master_id.' - '.$temp[0].' - '.$temp[1].' - '.$act_val;

			if($temp[0]=="objekte")
			{
				$update_objekte .= $temp[1].' = "'.$act_val.'", ';

				$insert_objekte_fields .= $temp[1].', ';
				$insert_objekte_values .= '"'.$act_val.'", ';
			}
			elseif($temp[0]=="wohnungen")
			{
				$update_wohnungen .= $temp[1].' = "'.$act_val.'", ';

				$insert_wohnungen_fields .= $temp[1].', ';
				$insert_wohnungen_values .= '"'.$act_val.'", ';
			}
			elseif($temp[0]=="zaehler")
			{
				$update_zaehler .= $temp[1].' = "'.$act_val.'", ';

				$insert_zaehler_fields .= $temp[1].', ';
				$insert_zaehler_values .= '"'.$act_val.'", ';
			}

		}
	}
	
	// INSERTS ausführen
	if($insert_me!="")
	{
		if($ansicht=="objekte")
		{
			$insert = 'insert into Objekte ('.substr($insert_objekte_fields,0,-2).', geandert_am, geandert_von) values ('.substr($insert_objekte_values,0,-2).', now(), "'.$_SESSION["bearbeiter"]["nachname"].'")';
		}
		elseif($ansicht=="wohnungen")
		{
			$insert = 'insert into Wohnungen ('.substr($insert_wohnungen_fields,0,-2).', geandert_am, geandert_von) values ('.substr($insert_wohnungen_values,0,-2).', now(), "'.$_SESSION["bearbeiter"]["nachname"].'")';
		}
		elseif($ansicht=="zaehler")
		{
			$insert = 'insert into Zaehler ('.substr($insert_zaehler_fields,0,-2).', geandert_am, geandert_von) values ('.substr($insert_zaehler_values,0,-2).', now(), "'.$_SESSION["bearbeiter"]["nachname"].'")';
		}
	
		mysqli_query($mysqli, $insert);
		#echo '<br>'.$insert.mysqli_error($mysqli);

		$master_id = mysqli_insert_id($mysqli);
		
		// Hinweis zum Speichern
		$hinweis_speichern = 'Es wurde ein neuer Datensatz angelegt!';
	}
	// UPDATES ausführen
	else
	{
		if($ansicht=="objekte")
		{
			$update = 'update Objekte set '.substr($update_objekte,0,-2).', geandert_am = now(), geandert_von = "'.$_SESSION["bearbeiter"]["nachname"].'" where id = '.$master_id.';';
		}
		elseif($ansicht=="wohnungen")
		{
			$update = 'update Wohnungen set '.substr($update_wohnungen,0,-2).', geandert_am = now(), geandert_von = "'.$_SESSION["bearbeiter"]["nachname"].'" where id = '.$master_id.';';
		}
		elseif($ansicht=="zaehler")
		{
			$update = 'update Zaehler set '.substr($update_zaehler,0,-2).', geandert_am = now(), geandert_von = "'.$_SESSION["bearbeiter"]["nachname"].'" where id = '.$master_id.';';
		}

		mysqli_query($mysqli, $update);
		#echo '<br>'.$update.mysqli_error($mysqli);
		
		// Hinweis zum Speichern
		$hinweis_speichern = 'Die Daten wurden geändert!';
	}
}



// leeres Formular ausgeben
if($master_id=="")
{
	foreach($felder as $key=>$val)
	{
		// $key ohne führenden Tabellen-Namen
		$key_int = substr($key,strpos($key,"___")+3);

		// ggfs. Spaltennamen umbiegen
		$spaltenname_out = format_pass($key);

		$zeile[$key] = '<td>'.$spaltenname_out.'</td>'.td($key, $val, "neu", $produkt[$key_int]);
	}
}
// bestehenden Datensatz ändern
else
{
	if($ansicht=="objekte")
	{
		$daten = getObjekt($mysqli, $master_id);
	}
	elseif($ansicht=="wohnungen")
	{
		$daten = getWohnung($mysqli, $master_id);
	}
	elseif($ansicht=="zaehler")
	{
		$daten = getZaehler($mysqli, $master_id);
	}
		
	#print_r($daten);

	foreach($felder as $key=>$val)
	{
		// $key ohne führenden Tabellen-Namen
		$key_int = substr($key,strpos($key,"___")+3);
		#echo '<br>'.$key_int.' - '.$daten[$key_int];

		#if(substr($key,0,3)=="FIX")
		#{
		#	$zeile[$key] = $val;
		#}
		#else
		#{
			// ggfs. Spaltennamen umbiegen
			$spaltenname_out = format_pass($key);

			$zeile[$key] = '<td valign="top"><b>'.$spaltenname_out.'</b></td>'.td($key, $val, $master_id, $daten[$key_int]);

			if(++$pp%2==0)
				$bg_pp = ' class="even"';
			else
				$bg_pp = ' class="odd"';

			$out_print .= '<tr'.$bg_pp.'><td><nobr>'.$spaltenname_out.'</nobr></td><td>'.$daten[$key_int].'&nbsp;</tr>';
		#}
	}
}

// bearbeiten oder neu erfassen
if($master_id=="")
{
	$extra_feld = '<input type="hidden" name="insert_me" value="1">';
}
else
{
	#$extra_feld = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="insert_me" value="als neuen Datensatz speichern">';
}



// Ausgabezeilen anzeigen
echo '<form name="update" action="index.php?'.$_SERVER["QUERY_STRING"].'" method="post" onsubmit="return checkForm();">';
echo '<b>'.$hinweis_speichern.'</b><br><br>';
echo '<table class="t_border" cellspacing="0" cellpadding="2" border="0">';
echo '<input type="hidden" name="update_aktiv" value="1">';
echo '<input type="hidden" name="master_id" value="'.$master_id.'">';

if($ansicht!="versand")
{
	echo '<tr><td align="center" colspan="2"><input type="submit" value="Speichern">'.$extra_feld.'</td></tr>';
}

foreach($zeile as $key=>$val)
{
	if(++$i%2==0)
		$bg = ' class="even"';
	else
		$bg = ' class="odd"';

	echo '<tr '.$bg.'>'.$val.'</tr>';
}

if($ansicht!="versand")
{
	echo '<tr><td align="center" colspan="2"><input type="submit" value="Speichern">'.$extra_feld.'</td></tr>';
}

echo '</table>';
echo '</form>';

$fp = fopen('listen/pass.htm', 'w+');
fputs($fp, '<link rel="stylesheet" type="text/css" href="/lib/standard.css"><table cellspacing="0" cellpadding="2" border="0" class="t_border">'.$out_print.'</table>');
fclose($fp);

?>

<script type="text/javascript">
	
	<?php echo $js_out; ?>
	
	function checkForm()
	{
		var err_msg = "";
		<?php echo $js_form; ?>

		/* scharfe Variante, Daten werden nur gespeichert, wenn alle Pflichtfelder gefüllt sind */
		if(err_msg!="")
		{
			alert(err_msg + "\n\nDie Daten werden nicht gespeichert!");
			return false;
		}
		else
		{
			return true;
		}

	}
</script>