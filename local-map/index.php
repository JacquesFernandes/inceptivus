<?php
/**
 * Setup
 * 
 * Data fetched from db is stored in locArray in below format
 * 
 * assoc_array locArray
 * - "p" = player owned -> ["p"] => "x,y-...."
 * - "s" = special -> ["s"] => "x,y-...."
 * - "n" = normal -> ["n"] => "x,y-...."
 * - "b" = base -> ["b"] => "x,y-...."
 * - "r" = resources -> ["r"] => "x,y-...."
 * - "f1" = faction1 -> ["f1"] => "x,y-...."
 * - "f2" = faction1 -> ["f2"] => "x,y-...."
 * 
 * Data is then parsed by "populateMapArray()" which inserts the character corresponding to the type of cell
 * at ("i","j") in the 2D array "mapArray"
 * 
 * array mapArray
 * A 2D array which represents the map, contains characters corresponding to type of cell present in each cell
 * 
**/

include "../db_access/db.php";
include "../player.php";
//echo("this is a test<br>");

$mapArray = array();
//$locArray = array("p"=>"4,4","f1"=>"4,4","f2"=>"1,1","s"=>"1,1-2,2","n"=>"2,3","b"=>"3,2");
//$_POST["locArray"] = array("p"=>"4,4","s"=>"1,1-2,2","n"=>"2,3","b"=>"3,2");


if (isset($_SESSION) && !empty($_SESSION))
{
	$locArray = $_SESSION["locArray"];
	echo("---");
	var_dump($locArray);
	echo("<br>---<br>");
	$tlc = $_SESSION["coord"]; //Top Left Coordinate
}
else
{
	redirect("www.teknack.in");
	die();
}


function populateMapArray($locArray)
{
	global $mapArray,$tlc;
	
	$str_p = $locArray["p"];
	$str_s = $locArray["s"];
	$str_n = $locArray["n"];
	$str_b = $locArray["b"];
	$str_f1 = $locArray["f1"];
	$str_f2 = $locArray["f2"];
	
	$p_loc = split("-",$str_p);
	//print_r($p_loc); 
	$s_loc = split("-",$str_s);
	$n_loc = split("-",$str_n);
	$b_loc = split("-",$str_b);
	$f1_loc = split("-",$str_f1);
	$f2_loc = split("-",$str_f2);
	
	for ($i = 0; $i <= 8; $i++)
	{
		for($j = 0; $j <= 8; $j++)
		{
			$mapArray[$i][$j] = "--";
		}
	}
	
	foreach ($p_loc as $i)
	{
		$loc = split(",",$i);
		if ($loc[0] !== "")
		{
			$mapArray[$loc[0]-$tlc[0]][$loc[1]-$tlc[1]] = "p";
		}
	}
	
	foreach ($s_loc as $i)
	{
		$loc = split(",",$i);
		if ($loc[0] !== "")
		{
			$mapArray[$loc[0]-$tlc[0]][$loc[1]-$tlc[1]] = "s";
		}
	}
	
	foreach ($n_loc as $i)
	{
		$loc = split(",",$i);
		if ($loc[0] !== "")
		{
			$mapArray[$loc[0]-$tlc[0]][$loc[1]-$tlc[1]] = "n";
		}
	}
	
	foreach ($b_loc as $i)
	{
		$loc = split(",",$i);
		if ($loc[0] !== "")
		{
			$mapArray[$loc[0]-$tlc[0]][$loc[1]-$tlc[1]] = "b";
		}
	}
	
	foreach ($f1_loc as $i)
	{
		$loc = split(",",$i);
		if ($loc[0] !== "")
		{
			$mapArray[$loc[0]-$tlc[0]][$loc[1]-$tlc[1]] = "<div style='background-color:red'>".$mapArray[$loc[0]-$tlc[0]][$loc[1]-$tlc[1]]."</div>";
		}
	}
	
	foreach ($f2_loc as $i)
	{
		$loc = split(",",$i);
		if ($loc[0] !== "")
		{
			$mapArray[$loc[0]-$tlc[0]][$loc[1]-$tlc[1]] = "<div style='background-color:blue'>".$mapArray[$loc[0]-$tlc[0]][$loc[1]-$tlc[1]]."</div>";
		}
	}
}

function genMatrix()
{
	global $mapArray;
	echo("<table border=1>");
	echo("<tr>");
	echo("<td>--</td>");
	for ($i = 0; $i <= 8; $i++)
	{
		echo("<td><b>".$i."</b></td>");
	}
	echo("</tr>");
	for ($i = 0; $i <= 8; $i++)
	{
		echo("<tr>");
		echo("<td><b>".$i."</b></td>");
		for ($j = 0; $j<= 8; $j++)
		{ 	
			/*
			//if (!empty($mapArray[$i][$j]))
			//{
				
				if (split("",$mapArray[$i][$j])[1] === "/")
				{
					echo("<td style='background-color:red'>");
					echo($mapArray[$i][$j]);
					echo("</td>");
				} 
				else
				{
					echo("<td>");
					echo($mapArray[$i][$j]);
					echo("</td>");
				}
				*/
				echo("<td>");
				echo($mapArray[$i][$j]);
				echo("</td>");
				
		}
			echo("</tr>");
	}
		echo("</table>");
}

function fake_main()
{
	global $locArray; //=$_POST["locations"];
	populateMapArray($locArray);
}

//For Button Press
?>

<html>

<head>
	<link rel="stylesheet" type="text/css" href="./index.css">
	<title>Test page</title>
</head>

<body>
	<div align="center"><?php fake_main(); genMatrix() ?></div>
	<hr>
	<div style="float:right">
	<form method="POST" action="shift.php">
		<table id="nav_buttons" border=0 align="center">
			<tr>
				<td></td>
				<td><button type="submit" name="up">UP</button></td>
				<td></td>
			</tr>
			<tr>
				<td><button name="left">LEFT</button></td>
				<td><button name="world-map">WORLD MAP</button></td>
				<td><button name="right">RIGHT</button></td>
			</tr>
			<tr>
				<td></td>
				<td><button name="down">DOWN</button></td>
				<td></td>
			</tr>
		</table>
	</form>
	</div>
	
	<div style="float:left">
	<h2>Stats</h2><br> <?php getStats()?>
	<u>Faction:</u> <?php getFaction?>
	<ul>
		<li>Food:</li> <?php getFood()?>
	</ul>
	</div>
</body>

</html>