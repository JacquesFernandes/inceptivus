<?php
session_start();
require "connect.php";
$playerid=$_SESSION['tek_emailid'];
$faction=1;/*$_SESSION['faction']*/
$output="[";
function scout($row,$col)
{
	global $conn,$playerid,$output;
	$sql="SELECT occupied,fortification,troops,faction FROM grid WHERE row=$row and col=$col;";
	$res=$conn->query($sql);
	$troops=0;
	$ptroops=0; //player Troops
	if($res->num_rows>0)
	{
		$r=$res->fetch_assoc();
		if($r['troops']>0 and $r['occupied']==$playerid) //slot occupied by the player 
		{
			$ptroops=$r['troops']; //troops by player
		}
		else if($r['troops']>0 and $r['occupied']!=$playerid) //slot occupied by an ally so player troops are in troops table
		{
			$troops=$r['troops']; //ally troops
		}
	}
	if($r['occupied']==$playerid) //slot was occupied by player so calculating remaining troops from troops table
	{
		$sql="SELECT SUM(quantity) FROM troops WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		if($res->num_rows>0)
		{
			$r1=$res->fetch_assoc();
			$troops=$ptroops+$r1['SUM(quantity)']; //troops of allies and player added		
		}
	}
	else //slot is occupied by an ally ,ally occupant's troops calculated at line 23
	{
		$sql="SELECT SUM(quantity) FROM troops WHERE row=$row and col=$col and playerid<>$playerid;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$troops+=$r1['SUM(quantity)']; //total troops without player troops 
		$sql="SELECT quantity FROM troops WHERE row=$row and col=$col and playerid=$playerid;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$ptroops=$r1['quantity'];
		$troops+=$ptroops; //player troops added to total troops
	}
	$output=$output.'{"response":"Occupant:'.$r["occupied"].'<br>Fortification:'.$r["fortification"].'<br>troops:'
					.$troops.'<br>your troops:'.$ptroops.'<br>Faction:'.$r["faction"].'"}]';
}
function troopPresent($row,$col)
{
	global $conn,$playerid;
	$sql="SELECT troops FROM grid WHERE row=$row and col=$col and occupied=$playerid;";
	$res=$conn->query($sql);
	if($res->num_rows>0)
	{
		$r=$res->fetch_assoc();
		if($r['troops']>0)
			return true;
		else
			return false;
	}
	else
	{
		$sql="SELECT quantity FROM troops WHERE row=$row and col=$col and playerid=$playerid;";
		$res=$conn->query($sql);
		if($res->num_rows>0)
		{
			$r=$res->fetch_assoc();
			if($r['quantity']>0)
				return true;
			else 
				return false;
		}
		else
			return false;
	}

}
function getActions($row,$col)  //AJAX FUNCTION!!! **maybe will add action cost with actions**
{
	global $conn,$playerid,$faction,$output;
	if(!isset($row))
	{
		$row=0;
		$col=8;
	}
	$r=$row;
	$c=$col;
	global $playerid,$conn,$output;
	$sql="SELECT occupied,faction FROM grid WHERE row=$row and col=$col;";
	if(!$res=$conn->query($sql))
	{
		echo "error: ".$conn->error;
	}
	$sql1="SELECT playerid,quantity FROM troops WHERE row=$row and col=$col and playerid=$playerid;";
	$res1=$conn->query($sql1);
	$fortification=0;
	if($res->num_rows>0)
	{
		while($rw = $res->fetch_assoc())
		{
			if($rw['faction']!=$faction and $rw['faction']!=0)  //enemy faction
			{
				//echo $row['faction'];
				if(isset($_SESSION['selectedRow']))
				{
					$output=$output.'{"action":"scout"},{"action":"attack"},{"visible":"false"}]';
				}
				else
				{
					$output=$output.'{"action":"scout"},{"visible":"false"}]';
				}
					
			}
			else if($rw['faction']==$faction) //allied faction
			{
				if($rw['occupied']==$playerid) //player occupied 
				{
					if(isset($_SESSION['selectedRow']))  
					{
						if($_SESSION['selectedRow']==$row and $_SESSION['selectedCol']==$col)//player selects already
						{                                                                    //selected occupied slot
							unset($_SESSION['selectedRow']);
							unset($_SESSION['selectedCol']);
						}
						else
						{
							$output=$output.'{"action":"move"},{"visible":"false"},';
							scout($r,$c);
						}
					}
					else
					{
						$output=$output.'{"action":"fortify"},{"action":"select_troops"},
						                 {"action":"create_troops"},{"visible":"true"},';
						scout($r,$c); // PROBLEM 
					}		
				}
				else //occupied by allies
				{
					if($res1->num_rows>0) //player troops stationed
					{
						if(isset($_SESSION['selectedRow'])) 
						{																	   
							if($_SESSION['selectedRow']==$row and $_SESSION['selectedCol']==$col)//player selects already
							{																	 //selected troops 
								unset($_SESSION['selectedRow']);
								unset($_SESSION['selectedCol']);
							}
							else
							{
								$output=$output.'{"action":"move"},{"visible":"false"},';
								scout($r,$c);
							}
						}
						else
						{
							$output=$output.'{"action":"select_troops"},{"visible":"true"},';
							scout($r,$c);
						}		
					}
					else //player troops not present
					{
						if(isset($_SESSION['selectedRow']))
						{
							$output=$output.'{"action":"scout"},{"action":"move"},{"visible":"false"}]';	
						}
						else
						{
							$output=$output.'{"action":"scout"},{"visible":"false"}]';
						}
					}
				}
			}
			else //player selects unoccupied slot
			{
				if(isset($_SESSION['selectedRow']))
				{
					$output=$output.'{"action":"scout"},{"action":"move"},{"visible":"false"}]';	
				}
				else
				{
					$r3=$res1->fetch_assoc();
					if($r3['quantity']>0)
					{
						$output=$output.'{"action":"select_troops"},{"action":"settle"},{"visible":"true"},';
						scout($r,$c);	
					}
					else
					{
						$output=$output.'{"action":"scout"},{"visible":"false"}]';
					}
				}
			}
		}
	}
	echo $output;
}
// if(isset($_REQUEST['row']) and !empty($_REQUEST['row']))
// {
	$row=$_REQUEST['row'];
	$col=$_REQUEST['col'];
	getActions($row,$col);
//}
?>