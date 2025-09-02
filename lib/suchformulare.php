<?php

// action-URL für die Suchformular
$action_url = $_SERVER["QUERY_STRING"];
$action_url = str_replace("delete_aktiv=1", "", $action_url);

### Suchformulare
if($liste=="zaehler" || $liste=="export")
{
	// Dropdown für die Objekte
	$query = 'select * from Objekte order by Name';
	$result = mysqli_query($mysqli, $query);
	#echo '<br>'.$query.mysqli_error($mysqli).mysqli_num_rows($mysqli);
	
	while($row=mysqli_fetch_assoc($result))
	{
		if($row["ID"]==$suche_objekt)
		{
			$selected = " selected";
		}
		else
		{
			$selected = "";
		}
	
		$select_objekt .= '<option value="'.$row["ID"].'"'.$selected.'>'.$row["Name"].'</option>';	
	}
	
	// Dropdown für die Wohnungen
	if($suche_objekt!="")
	{
		$query = 'select * from Wohnungen where Objekt_ID = '.$suche_objekt.' order by Name';
		$result = mysqli_query($mysqli, $query);
		#echo '<br>'.$query.mysqli_error($mysqli).mysqli_num_rows($mysqli); exit();
		
		while($row=mysqli_fetch_assoc($result))
		{
			if($row["ID"]==$suche_wohnung)
			{
				$selected = " selected";
			}
			else
			{
				$selected = "";
			}
		
			$select_wohnung .= '<option value="'.$row["ID"].'"'.$selected.'>'.$row["Name"].'</option>';	
		}		
	}
	
	// Dropdown für die Zähler
	if($suche_wohnung!="")
	{
		$query = 'select * from Zaehler where Wohnung_ID = '.$suche_wohnung.' order by Nummer';
		$result = mysqli_query($mysqli, $query);
		#echo '<br>'.$query.mysqli_error($mysqli).mysqli_num_rows($mysqli); exit();
		
		while($row=mysqli_fetch_assoc($result))
		{
			if($row["ID"]==$suche_zaehler)
			{
				$selected = " selected";
			}
			else
			{
				$selected = "";
			}
		
			$select_zaehler .= '<option value="'.$row["ID"].'"'.$selected.'>'.$row["Nummer"].'</option>';	
		}		
	}
	
	$suche = '
	<table cellspacing="0" cellpadding="2" border="0">
		<form name="suche" action="index.php?'.$action_url.'" method="post">
		<input type="hidden" name="suche_aktiv" value="1">
		<tr class="odd">
			<td>Objekt:</td>
			<td><select name="suche_objekt" onchange="document.suche.submit();"><option value="">Bitte auswählen</option>'.$select_objekt.'</select></td>
		</tr>
		<tr class="even">
			<td>Verbraucher:</td>
			<td><select name="suche_wohnung" onchange=" document.suche.suche_zaehler.selectedIndex=0; document.suche.submit();"><option value="">Bitte auswählen</option>'.$select_wohnung.'</select></td>
		</tr>
		<tr class="odd">
			<td>Zählernummer:</td>
			<td><select name="suche_zaehler" onchange="document.suche.submit();"><option value="">Bitte auswählen</option>'.$select_zaehler.'</select></td>
		</tr>
		<!--
		<tr class="even">
			<td colspan="2"><input type="submit" value="suchen"></td>
		</tr>
		-->
		</form>
	</table>';
}
elseif($liste=="ablesen")
{
	// nur suchen, wenn das aktuelle Jahr noch nicht abgelesen ist (z.B. 2018 von Dezember 2018 bis März 2019)
	if(date("m")=="01" || date("m")=="02" || date("m")=="03")
	{
		$jahr_akt = date("Y")-1;
	}
	else
	{
		$jahr_akt = date("Y");
	}	
	// Dropdown für die Objekte (nur, falls auch Zähler vorhanden UND bei den Zaehlern darf der Ausbaustand nicht gefüllt sein)
	$query = '
		select distinct Objekte.ID, Objekte.Name from Objekte
			join Wohnungen on Wohnungen.Objekt_ID = Objekte.ID
			join Zaehler on Zaehler.Wohnung_ID = Wohnungen.ID
			where Zaehler.Ausbaustand = 0 and `'.$jahr_akt.'` = 0
			order by Objekte.Name';
			
	$result = mysqli_query($mysqli, $query);
	#echo '<br>'.$query.mysqli_error($mysqli).mysqli_num_rows($mysqli);
	
	while($row=mysqli_fetch_assoc($result))
	{
		if($row["ID"]==$suche_objekt)
		{
			$selected = " selected";
		}
		else
		{
			$selected = "";
		}
	
		$select_objekt .= '<option value="'.$row["ID"].'"'.$selected.'>'.$row["Name"].'</option>';	
	}
	
	// Dropdown für die Wohnungen
	if($suche_objekt!="")
	{
		$query = '
			select distinct Wohnungen.ID, Wohnungen.Name from Wohnungen 
				join Zaehler on Zaehler.Wohnung_ID = Wohnungen.ID
				where Wohnungen.Objekt_ID = '.$suche_objekt.' and Zaehler.Ausbaustand = 0 and `'.$jahr_akt.'` = 0
				order by Wohnungen.Name';
			
			
		$result = mysqli_query($mysqli, $query);
		#echo '<br>'.$query.mysqli_error($mysqli).mysqli_num_rows($mysqli); exit();
		
		while($row=mysqli_fetch_assoc($result))
		{
			if($row["ID"]==$suche_wohnung)
			{
				$selected = " selected";
			}
			else
			{
				$selected = "";
			}
		
			$select_wohnung .= '<option value="'.$row["ID"].'"'.$selected.'>'.$row["Name"].'</option>';	
		}		
	}
	
	// Dropdown für die Zähler
	if($suche_wohnung!="")
	{
		$query = 'select * from Zaehler where Wohnung_ID = '.$suche_wohnung.' and Zaehler.Ausbaustand = 0 and `'.$jahr_akt.'` = 0 order by Nummer';
		$result = mysqli_query($mysqli, $query);
		#echo '<br>'.$query.mysqli_error($mysqli).mysqli_num_rows($mysqli); exit();
		
		while($row=mysqli_fetch_assoc($result))
		{
			if($row["ID"]==$suche_zaehler)
			{
				$selected = " selected";
			}
			else
			{
				$selected = "";
			}
		
			$select_zaehler .= '<option value="'.$row["ID"].'"'.$selected.'>'.$row["Nummer"].'</option>';	
		}		
	}
	
	$suche = '
	<table cellspacing="0" cellpadding="2" border="0">
		<form name="suche" action="index.php?'.$action_url.'" method="post">
		<input type="hidden" name="suche_aktiv" value="1">
		<tr class="odd">
			<td>Objekt:</td>
			<td><select name="suche_objekt" onchange="document.suche.submit();"><option value="">Bitte auswählen</option>'.$select_objekt.'</select></td>
		</tr>
		<tr class="even">
			<td>Verbraucher:</td>
			<td><select name="suche_wohnung" onchange=" document.suche.suche_zaehler.selectedIndex=0; document.suche.submit();"><option value="">Bitte auswählen</option>'.$select_wohnung.'</select></td>
		</tr>
		<tr class="odd">
			<td>Zählernummer:</td>
			<td><select name="suche_zaehler" onchange="document.suche.submit();"><option value="">Bitte auswählen</option>'.$select_zaehler.'</select></td>
		</tr>
		<!--
		<tr class="even">
			<td colspan="2"><input type="submit" value="suchen"></td>
		</tr>
		-->
		</form>
	</table>';
}

//Suchformulare allgemein
// damit bei den diversen Aktionen (Speichern, Sortieren) die Suche nicht verloren geht
// muß bei Einführen neuer Suchfelder entsprechend erweitert werden!!

if(isset($suche_objekt))
{
	$suche_post .= '<input type="hidden" name="suche_objekt" value="'.$suche_objekt.'">';
	$suche_get .= '&suche_objekt='.$suche_objekt;
}

if(isset($suche_wohnung))
{
	$suche_post .= '<input type="hidden" name="suche_wohnung" value="'.$suche_wohnung.'">';
	$suche_get .= '&suche_wohnung='.$suche_wohnung;
}

if(isset($suche_zaehler))
{
	$suche_post .= '<input type="hidden" name="suche_zaehler" value="'.$suche_zaehler.'">';
	$suche_get .= '&suche_zaehler='.$suche_zaehler;
}


if(isset($suche_aktiv))
{
	$suche_post .= '<input type="hidden" name="suche_aktiv" value="'.$suche_aktiv.'">';
	$suche_get .= '&suche_aktiv='.$suche_aktiv;
}

if($suche_post!="" && $suche_get!="")
{
	$suche_get = '&suche_aktiv=1'.$suche_get;
	$suche_post .= '<input type="hidden" name="suche_aktiv" value="1">'.$suche_post;
}

?>