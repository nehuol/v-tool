<?php

### Ausgabe der Kundenliste, grundsätzliche Ansicht wird über $liste gesteuert ###
if($liste=="")
{
	exit('Keine Ansichtsart für Kundenliste übergeben!');
}

$zurueck_link = urlencode('hauptansicht='.$hauptansicht.'&liste='.$liste.$suche_get);
$kopfzeile_einblenden = 25;

### Kommentare, Hinweise zu den einzelnen Listen
if($liste=="objekte")
{
	echo '<h3>Objekte</h3><br><a class="btn_blau" href="index.php?hauptansicht=pass&ansicht=objekte&zurueck_link='.$zurueck_link.'"><b>Neues Objekt erfassen</b></a><br><br>';
}
elseif($liste=="wohnungen")
{
	echo '<h3>Wohnungen</h3><br><a class="btn_blau" href="index.php?hauptansicht=pass&ansicht=wohnungen&zurueck_link='.$zurueck_link.'"><b>Neue Wohnung erfassen</b></a><br><br>';
}
elseif($liste=="zaehler")
{
	echo '<h3>Zähler</h3><br><a class="btn_blau" href="index.php?hauptansicht=pass&ansicht=zaehler&zurueck_link='.$zurueck_link.'"><b>Neuen Zähler erfassen</b></a><br><br>';
}

### Spalten der einzelnen Listen definieren und Suche-SQL vorbereiten
if($liste=="objekte")
{
	$sql = 'SELECT ID FROM Objekte where 1=1 '.$suche_sql;

	
	
	$felder["objekte___Name"] = 'plain';
	$felder["___Bearbeiten"] = 'bearbeiten@pass,objekte';
	$felder["___Loeschen"] = 'loeschen';
}
elseif($liste=="wohnungen")
{
	$sql = 'SELECT ID FROM Wohnungen where 1=1 '.$suche_sql;

	
	$felder["wohnungen___Objekt_Name"] = 'plain';
	$felder["wohnungen___Name"] = 'plain';
	$felder["wohnungen___Nummer"] = 'plain';
	$felder["wohnungen___Mieter"] = 'plain';
	$felder["___Bearbeiten"] = 'bearbeiten@pass,wohnungen';
	$felder["___Loeschen"] = 'loeschen';	
}
elseif($liste=="zaehler")
{
	// Suchanfrage bearbeiten
	if($suche_aktiv==1)
	{
		if($suche_objekt!="")
		{
			$suche_sql .= " AND Objekte.ID = '".$suche_objekt."'";
		}

		if($suche_wohnung!="")
		{
			$suche_sql .= " AND Wohnungen.ID = '".$suche_wohnung."'";
		}

		if($suche_zaehler!="")
		{
			$suche_sql .= " AND Zaehler.ID = '".$suche_zaehler."'";
		}
	}
	else 
	{
		$suche_sql .= " AND 1=0";
	}
	
	
	$sql = 'select Zaehler.ID from Zaehler
			left join Wohnungen on Wohnungen.ID = Zaehler.Wohnung_ID
			left join Objekte on Objekte.ID = Wohnungen.Objekt_ID
			where 
			1=1 '.$suche_sql;
	
	
	
	$felder["zaehler___Objekt_Name"] = 'plain';
	$felder["zaehler___Wohnung_Name"] = 'plain';
	$felder["zaehler___Nummer"] = 'plain';
	$felder["zaehler___Art"] = 'plain';
	$felder["zaehler___Besonderheit"] = 'plain';
	$felder["zaehler___Ausbaustand"] = 'text@10';
	$felder["zaehler___Ausbaudatum"] = 'datum';
	
	
	for($jahr=2017;$jahr<=date("Y")+1;$jahr++)
	{
		$felder["zaehler___".$jahr] = 'text@10';
	}	
	$felder["___Bearbeiten"] = 'bearbeiten@pass,zaehler';
	$felder["___Loeschen"] = 'loeschen';
}
elseif($liste=="ablesen")
{
	// das aktuelle Jahr zum Editieren anbieten (z.B. 2018 von Dezember 2018 bis März 2019)
	// ausserdem nur nach Zählern suchen, die im akt. Jahr noch nicht abgelesen wurden
	if(date("m")=="01" || date("m")=="02" || date("m")=="03")
	{
		$jahr_akt = date("Y")-1;
	}
	else
	{
		$jahr_akt = date("Y");
	}
		
	// Suchanfrage bearbeiten
	if($suche_aktiv==1)
	{
		if($suche_objekt!="")
		{
			$query_check = 'select ID from Wohnungen where Objekt_ID = '.$suche_objekt;
			$result_check = mysqli_query($mysqli, $query_check);
			while($row_check=mysqli_fetch_assoc($result_check))
			{
				$id_check[] = $row_check["ID"];
			}
			
			if(is_array($id_check))
			{
				$suche_sql .= " AND Wohnung_ID in (".implode(",", $id_check).")";
			}
		}

		if($suche_wohnung!="")
		{
			$suche_sql .= " AND Wohnung_ID = '".$suche_wohnung."'";
		}
		
		if($suche_zaehler!="")
		{
			$suche_sql .= " AND ID = '".$suche_zaehler."'";
		}
		
		if($suche_sql=="") 
		{
			$suche_sql = " AND 1=0";
		}
	}
	else 
	{
		$suche_sql .= " AND 1=0";
	}
	
	$sql = 'SELECT ID FROM Zaehler where Zaehler.Ausbaustand = 0 and `'.$jahr_akt.'` = 0 '.$suche_sql;

	$felder["zaehler___Wohnung_Name"] = 'plain';
	$felder["zaehler___Nummer"] = 'plain';
	$felder["zaehler___Art"] = 'plain';
	

	
	$felder["zaehler___".($jahr_akt-1)] = 'plain';
	$felder["zaehler___".$jahr_akt] = 'text@10';
	
	$felder["zaehler___Ausbaustand"] = 'plain';
	$felder["zaehler___Ausbaudatum"] = 'plain@datum';
	
}
elseif($liste=="export")
{
	// Suchanfrage bearbeiten
	if($suche_aktiv==1)
	{
		if($suche_objekt!="")
		{
			$suche_sql .= " AND Objekte.ID = '".$suche_objekt."'";
		}

		if($suche_wohnung!="")
		{
			$suche_sql .= " AND Wohnungen.ID = '".$suche_wohnung."'";
		}

		if($suche_zaehler!="")
		{
			$suche_sql .= " AND Zaehler.ID = '".$suche_zaehler."'";
		}
	}
	else 
	{
		$suche_sql .= " AND 1=0";
	}
	
	
	$sql = 'select Zaehler.ID from Zaehler
			left join Wohnungen on Wohnungen.ID = Zaehler.Wohnung_ID
			left join Objekte on Objekte.ID = Wohnungen.Objekt_ID
			where 
			1=1 '.$suche_sql;
	
	$felder["zaehler___Objekt_Name"] = 'plain';
	$felder["zaehler___Wohnung_Name"] = 'plain';
	$felder["zaehler___Nummer"] = 'plain';
	$felder["zaehler___Art"] = 'plain';
	
	for($jahr=date("Y")-2;$jahr<=date("Y")+1;$jahr++)
	{
		$felder["zaehler___".$jahr] = 'plain';
	}
		
	$felder["zaehler___Ausbaustand"] = 'plain';
	$felder["zaehler___Ausbaudatum"] = 'plain@datum';
	$felder["___Bearbeiten"] = 'bearbeiten@pass,zaehler';
}

### allgemeiner Teil
include("liste_allgemein.php");

?>