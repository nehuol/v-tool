<?php

echo '<br><br>';

### Kommentare, Hinweise zu den einzelnen Ansichten
if($ansicht=="mitglieder_admin" || $ansicht=="mitglieder")
{
	echo '<b>Mitglieder bearbeiten</b><br><br>';
}


### Kommentare, Hinweise zu den einzelnen Ansichten
if($ansicht=="objekte")
{
	echo '<b>Objekt bearbeiten</b><br><br>';
}
elseif($ansicht=="wohnungen")
{
	echo '<b>Wohnung bearbeiten</b><br><br>';
}
elseif($ansicht=="zaehler")
{
	echo '<b>Zähler bearbeiten</b><br><br>';
}


### Felderdefinition der einzelnen Ansichten
if($ansicht=="objekte")
{
	#$felder["mitglieder___id"] = 'plain';
	$felder["objekte___Name"] = 'text@50';
}
elseif($ansicht=="wohnungen")
{
	$felder["wohnungen___Objekt_ID"] = 'select';
	$felder["wohnungen___Name"] = 'text@30';
	$felder["wohnungen___Nummer"] = 'text@30';
	$felder["wohnungen___Mieter"] = 'text@30';
	
	$select["wohnungen___Objekt_ID"] = dropdown($mysqli, "Objekte", "Name", "ID");
}
elseif($ansicht=="zaehler")
{
	#echo '<br>POST: '.count($_POST).'<br>';
	#echo '<pre>';
	#print_r($_POST);
	#echo '</pre>';
	
	$felder["temp___Objekt_ID"] = 'select@submit';
	$felder["zaehler___Wohnung_ID"] = 'select';
	$felder["zaehler___Nummer"] = 'text@30';
	$felder["zaehler___Art"] = 'select';
	$felder["zaehler___Besonderheit"] = 'text';
	
	// etwas umständliche Methode, um die Dropdowns wieder richtig zu befüllen
	// ACHTUNG: Lücken in der ID-Spalte sind fatal wegen Ermittlung max_id !!!
	
	if($_POST["update_aktiv"]==1)
	{
		$query_temp = 'select max(ID) as MID from Zaehler';
		$result_temp = mysqli_query($mysqli, $query_temp);
		$row_temp = mysqli_fetch_assoc($result_temp);
		
		if($_POST["temp___Objekt_ID"][neu]!="")
		{
			$max_id = $row_temp["MID"]+1;
			$max_id_php = "neu";
		}
		else
		{
			$max_id = $row_temp["MID"];
			$max_id_php = $row_temp["MID"];
		}
	}
	else
	{
		$max_id = "neu";
		$max_id_php = "neu";
	}
	
	#echo '<br>max_id: '.$max_id;
	#echo '<br>max_id_php: '.$max_id_php;
	
	if($_POST["temp___Objekt_ID"][$max_id_php]!="")
	{
		$query_temp = 'select Wohnungen.*, Objekte.Name as Objekt_Name from Wohnungen join Objekte on Objekte.ID = Wohnungen.Objekt_ID where Objekte.ID = '.$_POST["temp___Objekt_ID"][$max_id_php].' order by Objekte.Name, Wohnungen.Name';
		$result_temp = mysqli_query($mysqli, $query_temp);
		while($row_temp=mysqli_fetch_assoc($result_temp))
		{
			$select["zaehler___Wohnung_ID"]["INT_VAL".$row_temp["ID"]] = $row_temp["Name"];
		}
	}
	elseif($_GET["master_id"]!="")
	{
		$zaehler_temp = getZaehler($mysqli, $_GET["master_id"]);

		$query_temp = 'select Wohnungen.*, Objekte.Name as Objekt_Name from Wohnungen join Objekte on Objekte.ID = Wohnungen.Objekt_ID where Objekte.ID = '.$zaehler_temp["Objekt_ID"].' order by Objekte.Name, Wohnungen.Name';
		$result_temp = mysqli_query($mysqli, $query_temp);
		while($row_temp=mysqli_fetch_assoc($result_temp))
		{
			$select["zaehler___Wohnung_ID"]["INT_VAL".$row_temp["ID"]] = $row_temp["Name"];
		}
	}

	#else
	#{
	#	$select["zaehler___Wohnung_ID"] = dropdown($mysqli, "Wohnungen", "Name", "ID");
	#}

	$count_obj = 1;
	$query_temp = 'select * from Objekte order by Name';
	$result_temp = mysqli_query($mysqli, $query_temp);
	while($row_temp=mysqli_fetch_assoc($result_temp))
	{
		$select["temp___Objekt_ID"]["INT_VAL".$row_temp["ID"]] = $row_temp["Name"];
		
		if($_POST["temp___Objekt_ID"][$max_id_php]==$row_temp["ID"])
		{
			$js_out .= 'document.getElementsByName("temp___Objekt_ID['.$max_id.']")[0].selectedIndex = '.$count_obj.';';
		}		
		
		$count_obj++;
	}
}


### allgemeiner Teil
include("pass_allgemein.php");

?>