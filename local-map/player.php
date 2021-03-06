<?php
require "../db_access/db.php";
require "connect.php";
require "actionCostValues.php";
$playerid=$_SESSION['tek_emailid']; //temporary!! don't forget to remove!!
$faction=$_SESSION['faction'];
if(!isset($_SESSION['faction']))
	$_SESSION['faction']=getFaction($playerid);
/*1-preserver
  2-exploiter*/

/*1-grass
  2-sand
  3-water
  4-mountain*/

/*for preservers 
  faction1-team bonus
  faction2-plunder bonus
  for exploiters
  faction1-scout cost
  faction2-settle cost*/

/*Nischal's code */
if($_SERVER["REQUEST_METHOD"] == "GET"){
	$stringresource="";
	if (isset($_GET['foodres'])) {
		creditResource('food_regen',$_GET['foodres']);
		$stringresource = $stringresource.$_GET['foodres'].':';
	}
	if (isset($_GET['waterres'])) {
		creditResource('water_regen',$_GET['waterres']);
		$stringresource = $stringresource.$_GET['waterres'].':';
	}
	if (isset($_GET['powerres'])) {
		creditResource('power_regen',$_GET['powerres']);
		$stringresource = $stringresource.$_GET['powerres'].':';
	}
	if (isset($_GET['metalres'])) {
		creditResource('metal_regen',$_GET['metalres']);
		$stringresource = $stringresource.$_GET['metalres'].':';
	}
	if (isset($_GET['woodres'])) {
		creditResource('wood_regen',$_GET['woodres']);
		$stringresource = $stringresource.$_GET['woodres'].':';
		$rows = $_SESSION['settleRow'];
		$column = $_SESSION['settleCol'];
		updateGridColumn('type',$stringresource, $rows, $column);/*Need to change to actual row and column when implementing*/
		unset($_SESSION['settleRow']);
		unset($_SESSION['settleCol']);
		echo("<script>window.location='./index.php'</script>");
	}


}

function getStats(){
	//global $dbconn;
	connect();
	$playerid = $_SESSION["tek_emailid"];
	setTable("player");
	$res=fetchAll($playerid);
	//alert(var_dump($res));
	//exiting
	setTable("grid");
	disconnect();
	return($res);
}
/*function max($x,$y)    //finds the greater of the two numbers
{
	if($x>$y)
		return $x;
	else
		return $y;
}*/


function validateAction($action,$row,$col) //return true if action is permitted PENDING
{
	global $conn,$playerid,$faction;
	//if an action involves 2 slots pass destination row and col to this function
	if($action=="move")
	{
		$sql="SELECT faction FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		if($r['faction']==$faction or $r['faction']==0) //allied or unoccupied slot
			return true;
		else
			return false;
	}
	else if($action=="attack")
	{
		$sql="SELECT fortification FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r2=$res->fetch_assoc();
		$sql="SELECT status FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$sql="SELECT faction FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		if($r['faction']==$faction or $r['faction']==0 or $r1['status']=="attack" or $r2['fortification']==-9)
			return false;
		else
			return true;
	}
	else if($action=="selectTroops")
	{
		$sql="SELECT faction FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		if($r['faction']==$faction or $r['faction']==0) //allied or unoccupied slot
			return true;
		else
			return false;
	}
	else if($action=="createTroops" or $action=="fortify" or $action=="addBaseTile")
	{
		$sql="SELECT occupied,fortification FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		echo $r['occupied'];
		echo "<br>".$playerid;
		echo "<br>".$action;
		if($action=="createTroops" or $action=="addBaseTile")
		{
			if($r['occupied']==$playerid)
			{
				return true;
			}
			else
				return false;	
		}
		else if($action=="fortify")
		{
			if($r['fortification']<8 and $r['fortification']>0)
				return true;
			else
				return false;		
		}
	}
	else if($action=="settle")
	{
		$sql="SELECT fortification FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r3=$res->fetch_assoc();
		$sql="SELECT special FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r2=$res->fetch_assoc();
		$sql="SELECT status FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		var_dump($sql);
		$r1=$res->fetch_assoc();
		$sql="SELECT faction FROM grid WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		if($r['faction']==0 and $r1['status']!="settle" and $r1['status']!="attack" and $r3['fortification']!=-9) 
			return true;	//only unoccupied slot
		else
			return false;
	}
}


function message($playerid,$message)
{
	global $conn;
	$sql="SELECT message FROM player WHERE tek_emailid='$playerid';";
	$res=$conn->query($sql);
	$row=$res->fetch_assoc();
	$message=$row['message']."\n".$message;
	$sql="UPDATE player SET message=$message WHERE tek_emailid='$playerid';";
	if($conn->query($sql))
		echo "error : ".$conn->error;
}


function queryResource($resource,$value)
{
	global $conn,$playerid;
	$sql="SELECT $resource FROM player WHERE tek_emailid='$playerid'";
	$res=$conn->query($sql);
	$row=$res->fetch_assoc();
	$reso=intval($row[$resource]);
	if($value>$reso)
	{
		echo "<br>not enough";
		return false;
	}
	else
		return true;
}


function creditResource($resource,$value)   //use to reduce resource on some action give resource name and value resp.
{
	global $conn,$playerid;

	if(!($value === NULL))
		$sql="UPDATE `player` SET $resource=$resource+'$value' WHERE tek_emailid='$playerid'";

	if($conn->query($sql)===false)
	{
		echo "error: ".$conn->error;
	}

	return true;
}

function updateGridColumn($name,$value,$row,$col){
	global $conn,$playerid;
	if(!($value === NULL))
		$sql="UPDATE `grid` SET $name='$value' WHERE row='$row' AND col='$col'"; //Change to row/column

	if($conn->query($sql)===false)
	{
		echo "error: ".$conn->error;
	}
	return true;
}

function deductResource($resource,$value)   //use to reduce resource on some action give resource name and value resp.
{
	global $conn,$playerid;
	if(!queryResource($resource,$value))
	{
		echo "<br>not enough";
		return false;
	}
	else
	{
		$sql="UPDATE player SET $resource=$resource-'$value' WHERE tek_emailid='$playerid'";
			if($conn->query($sql)===false)
			{
				echo "error: ".$conn->error;
			}
		return true;
	}
}


function troopExist($row,$col,$quantity)
{
	global $conn,$playerid;
	$troopExist=false;
	$sql="SELECT troops FROM grid WHERE row=$row and col=$col and occupied='$playerid';";
	$res=$conn->query($sql);                  //check if required troops present in grid table
	if($res->num_rows>0)
	{
		$r=$res->fetch_assoc();
		if($r['troops']<$quantity)
		{
			$_SESSION['response']=$row['troops'];
			$troopExist=false;
		}
		else
		{
			$_SESSION['response']=$row['troops'];
			$troopExist=true;
		}
	}
	$sql="SELECT quantity FROM troops WHERE row=$row and col=$col and playerid='$playerid';"; //check if required troops present
	$res=$conn->query($sql);                                                        //in troops table
	if($res->num_rows>0)
	{
		$r=$res->fetch_assoc();
		if($r['quantity']<$quantity)
		{
			if(!$troopExist) //troops not enough but present in troops table
			{
				$_SESSION['response']="You don't have those many troops.Create more soldiers!(troops)";
				unset($_SESSION['selectedRow']);
				unset($_SESSION['selectedCol']);
				unset($_SESSION['selectedTroops']);
				return false;
			}
		}
		else
		{
			$troopExist=true;
		}
	}
	if(!$troopExist)
	{
		$_SESSION['response']="You don't have those many troops.Create more soldiers!"; //troops not present in either of the tables
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return false;
	}
	else
	{
		$_SESSION['response']="Selected $quantity soldiers";
		return true;
	}
}


function move($srcRow,$srcCol,$destRow,$destCol,$quantity) //move works in 2 steps, first select troops from an occupied slot
{
	global $conn,$moveCostFood,$moveCostWater,$moveCostPower,$playerid;
	if(!validateAction("move",$destRow,$destCol))
	{
		$_SESSION['response']="the slot is now accupied by an enemy :(.
			Well you can attack your enemy to get the slot.";
		return;
	}     		                                           //then select the slot to move the troops to
	$sql="SELECT special FROM grid WHERE row=$destRow and col=$destCol;";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$slotType=$r['special'];
	$sql="SELECT civperk2 FROM research WHERE playerid='$playerid';";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	if($r['civperk2']<4 and $slotType==4)
	{
		$_SESSION['response']="you cannot station your soldiers on water yet.Get civperk2 level 4 first";
		return;
	}
	$distance=max(abs($srcRow-$destRow),abs($srcCol-$destCol));
	$sroot="x,y";
	$droot="x,z";
	$enemy; //enemy playerid
	$etroops=0; //enemy total troop strength at the destination slot
	$obperk=0; //open battle perk level of each enemy
	$ambush=false;
	$ambushSurvive=false;
	$sql="SELECT root FROM grid WHERE row=$srcRow and col=$srcCol;"; //the query for root column decide if movement is within
										 //the faction occupied region
	if($res=$conn->query($sql))
	{
		$conn->error;
	}
	if($res->num_rows>0)
	{
		while($row=$res->fetch_assoc())
		{
			$sroot=$row['root'];
		}
	}
	$sql="SELECT root FROM grid WHERE row=$destRow and col=$destCol;";
	if($res=$conn->query($sql))
	{
		$conn->error;
	}
	if($res->num_rows>0)
	{
		while($row=$res->fetch_assoc())
		{
			$droot=$row['root'];
		}
	}
	if($sroot==$droot)
	{
		$distance/=2;
	}


	$sql="SELECT civperk1 FROM research WHERE playerid='$playerid';"; //find if player has researched for move
	$res=$conn->query($sql);									   //discount
	$r=$res->fetch_assoc();
	$lvl=$r['civperk1'];
	if($lvl==1)
	{
		$moveCostFood-=$moveCostFood*0.1;
		$moveCostWater-=$moveCostWater*0.1;
		$moveCostPower-=$moveCostPower*0.1;
	}
	else if($lvl==2)
	{
		$moveCostFood-=$moveCostFood*0.2;
		$moveCostWater-=$moveCostWater*0.2;
		$moveCostPower-=$moveCostPower*0.2;
	}
	else if($lvl==3)
	{
		$moveCostFood-=$moveCostFood*0.3;
		$moveCostWater-=$moveCostWater*0.3;
		$moveCostPower-=$moveCostPower*0.3;
	}


	$foodCost=$distance*$moveCostFood;
	$waterCost=$distance*$moveCostWater;
	$powerCost=$distance*$moveCostPower;

	if(!troopExist($srcRow,$srcCol,$quantity)) //checks if actually player has the required troops
		return;
	//number of required troops exist

	if(!queryResource("food",$foodCost))
	{
		echo "here but don't know how";
		$_SESSION['response']="You don't have the required resources(food).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("water",$waterCost))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("power",$powerCost))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("food",$foodCost);
	deductResource("water",$waterCost);
	deductResource("power",$powerCost);
	//resources validated


	if(!troopExist($srcRow,$srcCol,$quantity)) //checks if actually player has the required troops
		return;
	//number of required troops exist

	/*open battle sim*/
	$troopDistribution=[[]];
	$hiddenTroops=0;
	$sql="SELECT faction FROM player WHERE tek_emailid='$playerid';";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$pfaction=$r['faction'];
	$sql="SELECT playerid FROM troops WHERE row=$destRow and col=$destCol;";
	$res1=$conn->query($sql);
	if($res1->num_rows==0)
	{
		$ambush=false;
	}
	else
	{
		$r=$res1->fetch_assoc();
		$p=$r['playerid'];
		$sql="SELECT faction FROM player WHERE tek_emailid='$p';";
		$res1=$conn->query($sql);
		$r=$res1->fetch_assoc();
		$faction=$r['faction'];
		if($faction!=$pfaction and $faction!=0)
		{
			echo "bhencho";
			$ambush=true;
			$sql="SELECT playerid,quantity FROM troops WHERE row=$destRow and col=$destCol;";
			$res=$conn->query($sql);
			if($res->num_rows>0)
			{
				$i=0;
				while($row=$res->fetch_assoc())
				{
					$enemy=$row['playerid'];
					$otroops=$row['quantity']; //number of troops of one enemy occupant
					$sql1="SELECT open,ttype FROM research WHERE playerid='$enemy';";
					$res1=$conn->query($sql1);
					$r=$res1->fetch_assoc();
					$obperk=$r['open'];
					if($obperk==1)
					{
						$hiddenTroops=max(0.25*$otroops,1); //25% percent troops hidden and are twice as effective
						$otroops+=$hiddenTroops;
					}
					else if($obperk==2)
					{
						$hiddenTroops=max(0.5*$otroops,1); //50% percent troops hidden and are twice as effective
						$otroops+=$hiddenTroops;
					}
					else if($obperk==3)
					{
						$hiddenTroops=$otroops; //100% percent troops hidden and are twice as effective
						$otroops+=$hiddenTroops;
					}
					$lvl=$r['ttype'];
					$lvl=explode(":", $lvl);
					$level=$lvl[1];
					$troopDistribution[$i]['troops']=$otroops-$hiddenTroops;
					$troopDistribution[$i]['playerid']=$enemy;
					$otroops=$otroops*$level;
					$etroops+=$otroops;
					$i++;
				}
				$sql="SELECT ttype FROM research WHERE playerid='$playerid';";
				$res1=$conn->query($sql);
				$row=$res1->fetch_assoc();
				$flvl=$row['ttype'];
				$flvl=explode(":", $flvl);
				$flevel=$flvl[1];
				if($flevel==0)
					$flevel=1;
				$quantity*=$flevel;
				if($quantity>$etroops) //player troops survive the ambush
				{
					$ambushSurvive=true;
					$quantity=$quantity-$etroops;
					$quantity/=$flevel;
					$quantity=floor($quantity);
					$sql2="DELETE FROM troops WHERE row=$destCol and col=$destCol;";
					if($conn->query($sql2)===false)
					{
						var_dump($sql2);
						echo "<br> error: ".$conn->error;
					}
					/*message to all*/
				}
				else //enemy ambush wins
				{
					$ambushSurvive=false;
					$quantity/=$flevel;
					for($i=0;$i<count($troopDistribution);$i++)
					{
						$loss=floor($troopDistribution[$i]['troops']/$quantity);
						$id=$troopDistribution['playerid']; //deducting troops as battle losses
						$sql2="UPDATE troops SET quantity=quantity-$loss WHERE playerid='$id' and row=$destRow and col=$destCol;";
						if($conn->query($sql2)===false)
						{
							var_dump($sql2);
							echo "<br> error: ".$conn->error;
						}
					}
					$sql2="DELETE FROM troops WHERE quantity<=0;";
					if($conn->query($sql2)===false)
					{
						var_dump($sql2);
						echo "<br> error: ".$conn->error;
					}
					/*message to all*/
					$_SESSION['response']="Your troops were ambushed!.There were no survivors :-(";
				}
			}
			else
			{
				$enemy=0;
				$etroops=0;
				$ebperk=0;
			}
		}
	}
	/*it ends here*/
	$sql="SELECT occupied FROM grid WHERE row=$srcRow and col=$srcCol;";
	$res=$conn->query($sql);
	if($res->num_rows>0)  //player moving from settled slot
	{

		$row=$res->fetch_assoc();
		if($row['occupied']==$playerid)
		{
			$sql="SELECT occupied FROM grid WHERE row=$destRow and col=$destCol";
			$res2=$conn->query($sql);
			$r=$res2->fetch_assoc();
			if($r['occupied']!=$playerid) //player moving form settled to unoccupied/allied slot
			{
				$sql="SELECT quantity FROM troops WHERE row=$destRow and col=$destCol;"; //check if troops are already present
				$res1=$conn->query($sql);
				if($res1->num_rows==0)
				{
					$sql="INSERT INTO troops (row,col,playerid,quantity) VALUES ($destRow,$destCol,'$playerid',$quantity);";
					if($conn->query($sql)==false)
						echo "error(114) : ".$conn->error."<br>";
				}
				else
				{
					$sql="UPDATE troops SET quantity=quantity+$quantity WHERE row=$destRow and col=$destCol;";
					if($conn->query($sql)==false)
						echo "error (119): ".$conn->error."<br>";
				}
				$sql="UPDATE grid SET troops=troops-$quantity WHERE row=$srcRow and col=$srcCol;";
				if($conn->query($sql)===false)
					echo "error (124 wala): ".$conn->error."<br>";
				if($ambushSurvive==true and $ambush==true)
				{
					$_SESSION['response']="There was an ambush on your troops and you lost a few soldiers<br>
					                       moved ".$quantity." soldiers! by ".$distance;
				}
				else
				{
					$_SESSION['response']="moved ".$quantity." soldiers! by ".$distance;
				}
			}
			else //player moves from occupied slot to occupied slot
			{
				$sql="UPDATE grid SET troops=troops-$quantity WHERE row=$srcRow and col=$srcCol";
				if($conn->query($sql)==false)
						echo "error(131) : ".$conn->error."<br>";
				$sql="UPDATE grid SET troops=troops+$quantity WHERE row=$destRow and col=$destCol";
				if($conn->query($sql)==false)
						echo "error(135) : ".$conn->error."<br>";
				if($ambushSurvive==true and $ambush==true)
				{
					$_SESSION['response']="There was an ambush on your troops and you lost a few soldiers<br>
					                       moved ".$quantity." soldiers! by ".$distance;
				}
				else
				{
					$_SESSION['response']="moved ".$quantity." soldiers! by ".$distance;
				}
			}
		}
		else //player moving stationed troops
		{
			$sql="SELECT occupied FROM grid WHERE row=$destRow and col=$destCol";
			$res2=$conn->query($sql);
			$r=$res2->fetch_assoc();
			if($r['occupied']!=$playerid) //player moves from unoccupied/allied slots to unoccupied/allied slots
			{
				$sql="UPDATE troops SET quantity=quantity-$quantity WHERE row=$srcRow and col=$srcCol and playerid='$playerid';";
				if($conn->query($sql)==false)
					echo "error (143): ".$conn->error."<br>";
				$sql="SELECT quantity FROM troops WHERE row=$destRow and col=$destCol and playerid='$playerid';"; //check if troops are already present
				$res1=$conn->query($sql);
				if($res1->num_rows==0)
				{
					$sql="INSERT INTO troops (row,col,playerid,quantity) VALUES ($destRow,$destCol,'$playerid',$quantity);";
					if($conn->query($sql)==false)
						echo "error(150) : ".$conn->error."<br>";
				}
				else
				{
					$sql="UPDATE troops SET quantity=quantity+$quantity WHERE row=$destRow and col=$destCol and playerid='$playerid';";
					if($conn->query($sql)==false)
						echo "error (156): ".$conn->error."<br>";
				}
				$sql="DELETE FROM troops WHERE quantity<=0";
				if($conn->query($sql)==false)
					echo "error(160) : ".$conn->error;
				if($ambushSurvive==true and $ambush==true)
				{
					$_SESSION['response']="There was an ambush on your troops and you lost a few soldiers<br>
					                       moved $quantity soldiers! by $distance to ($destRow,$destCol)";
				}
				else
				{
					$_SESSION['response']="moved $quantity soldiers! by $distance to ($destRow,$destCol)";
				}
			}
			else //move troops from unoccupied/allied slots to occupied slot
			{
				$sql="UPDATE troops SET quantity=quantity-$quantity WHERE row=$srcRow and col=$srcCol;";
				if($conn->query($sql)==false)
					echo "error (192): ".$conn->error."<br>";
				$sql="UPDATE grid SET troops=troops+$quantity WHERE row=$destRow and col=$destCol;";
				if($conn->query($sql)==false)
					echo "error (195): ".$conn->error."<br>";
				$sql="DELETE FROM troops WHERE quantity<=0";
				if($conn->query($sql)==false)
					echo "error(160) : ".$conn->error;
				if($ambushSurvive==true and $ambush==true)
				{
					$_SESSION['response']="There was an ambush on your troops and you lost a few soldiers<br>
					                       moved $quantity soldiers! by $distance to ($destRow,$destCol)";
				}
				else
				{
					$_SESSION['response']="moved $quantity soldiers! by $distance to ($destRow,$destCol)";
				}
			}
		}
	}
	unset($_SESSION['selectedRow']);
	unset($_SESSION['selectedCol']);
	unset($_SESSION['selectedTroops']);
}


function addBaseTile($row,$col)
{
	global $conn,$playerid,$addBaseWood,$addBaseMetal;

	if(!validateAction("addBaseTile",$row,$col))
	{
		$_SESSION['response']="tile's probablty not yours anymore<br>";
		return;
	}
	if(!queryResource("wood",$addBaseWood))
	{
		echo "here but don't know how";
		$_SESSION['response']="You don't have the required resources(wood).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("metal",$addBaseMetal))
	{
		$_SESSION['response']="You don't have the required resources(metal).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("wood",$addBaseWood);
	deductResource("power",$addBaseMetal);

	$sql="UPDATE grid SET fortification=-9 WHERE row=$row and col=$col;";
	if(!$conn->query($sql))
	{
		echo "error: ".$conn->error."<br>";
		var_dump($sql);
	}
	else
		$_SESSION['response']="Added base tile<br>";

	header("location:index.php");
}


function simAftermath($srcRow,$srcCol,$destRow,$destCol,$quantity,$action)
{
	global $conn,$playerid,$faction,$moveCostFood,$moveCostWater,$moveCostPower,$plunderPortion;
	
	$battleRes=simBattle($srcRow,$srcCol,$destRow,$destCol,$quantity);
	$battleResult=$battleRes['result'];
	echo "<br>RESULT:-$battleResult<br>";
    $plunderBonus=$battleRes['plunderBonus'];
    $fortification=$battleRes['fortification'];
    $maxLoss=$battleRes['maxLoss'];
	$enemy=$battleRes['enemy'];
	$troopLevel=$battleRes['troopLevel'];
	$faction=$battleRes['faction'];
	$winChance=$battleRes['winChance'];
	if(!isset($_SESSION['selectedRow']) or !isset($_SESSION['selectedCol']))
	{
		$srcRow=$_SESSION['selectedRow'];
		$srcCol=$_SESSION['selectedCol'];
	}
	if($action=="attack")
	{
		if(isset($_SESSION['result'])) //in case called after mini-game
		{
			echo "mini-game!";
			$result=$_SESSION['result'];
			if($result)
			{
				$battleResult=false;
				$percent=$_SESSION['ppercent'];
				$quantity=$percent*$quantity/100; //same percentage as the health of the character in mini-game
				//removing resource gathering from the defeated slot pending
				$sql="SELECT type FROM grid WHERE row=$destRow and col=$destCol;";
				$res=$conn->query($sql);
				$r=$res->fetch_assoc();
				$distribution=$r['type'];
				$distribution=explode(":",$distribution);
				$foodRegen=$distribution[0];
				$waterRegen=$distribution[1];
				$powerRegen=$distribution[2];
				$metalRegen=$distribution[3];
				$woodRegen=$distribution[4];

				$sql="UPDATE player SET food_regen=food_regen-$foodRegen,water_regen=water_regen-$waterRegen,
				      power_regen=power_regen-$powerRegen,metal_regen=metal_regen-$metalRegen,
				      wood_regen=wood_regen-$woodRegen WHERE tek_emailid='$enemy';";
				if($conn->query($sql)===false)
					echo "<br>error in reducing enemy regen: ".$conn->error;

				//removing resource gathering from the defeated slot
				$sql="UPDATE grid SET occupied='$playerid',type=NULL,faction=$faction,troops=$quantity WHERE
				      row=$destRow and col=$destCol;";
				if($conn->query($sql)===false)
					echo "<br>error: ".$conn->error;

				//plunder

				$sql="SELECT food,water,power,metal,wood FROM player WHERE tek_emailid='$enemy'";
				$res=$conn->query($sql);
				if(!$res)
					echo $conn->error;
				$r=$res->fetch_assoc();
				$plunderFood=$plunderPortion*$r['food']/100;
				$plunderWater=$plunderPortion*$r['water']/100;
				$plunderPower=$plunderPortion*$r['power']/100;
				$plunderMetal=$plunderPortion*$r['metal']/100;
				$plunderWood=$plunderPortion*$r['wood']/100;
				$sql="UPDATE player SET food=food-$plunderWood,water=water-$plunderWater,power=power-$plunderPower
				      ,metal=metal-$plunderMetal,wood=wood-$plunderWood WHERE tek_emailid='$enemy'"; //remove resources
				if($conn->query($sql)===false)                                     //from the defeated
					echo "<br>error: ".$conn->error;
				$plunderFood+=$plunderFood*$plunderBonus/100;
				$plunderWater+=$plunderWater*$plunderBonus/100;
				$plunderPower+=$plunderPower*$plunderBonus/100;
				$plunderMetal+=$plunderMetal*$plunderBonus/100;
				$plunderWood+=$plunderWood*$plunderBonus/100;
				$sql="UPDATE player SET food=food+$plunderWood,water=water+$plunderWater,power=power+$plunderPower
				      ,metal=metal+$plunderMetal,wood=wood+$plunderWood WHERE tek_emailid='$playerid'";
				if($conn->query($sql)===false)
					echo "<br>error: ".$conn->error;

				/*pending resoruce assignment*/

				/*root reassignment*/
				$sql="SELECT root FROM grid WHERE row=$destRow and col=$destCol;";
				$res=$conn->query($sql);
				$rw=$res->fetch_assoc();
				$droot=$rw['root'];
				$newRoot=$droot;
				$sql="SELECT row,col,root FROM grid WHERE root='$droot'"; //reset root for loser enemy
				$res=$conn->query($sql);
				if(!$res)
					echo $conn->error;
				if($res->num_rows>1)
				{
					$sql="UPDATE grid SET root=NULL WHERE row=$destRow and col=$destCol;";
					if($conn->query($sql)===false)
						echo "error: ".$conn->error;
					while($droot==$newRoot)
					{
						$r=$res->fetch_assoc();
						$newRoot=$r['row'].",".$r['col'];
					}
					$sql="UPDATE grid SET root='$newRoot' WHERE root='$droot';";
					if($conn->query($sql)===false)
						echo "error: ".$conn->error;
				}

				$roots= array();
				$root=$destRow.",".$destCol;
				$sql="UPDATE grid SET root='$root' WHERE row=$destRow and col=$destCol;"; //root was made null now
					if($conn->query($sql)===false)									    //it's given the original value
						echo "error: ".$conn->error;
				$troopCount;
				$sql="SELECT row,col,fortification,faction,root FROM grid WHERE (row=$destRow-1 or row=$destRow
				or row=$destRow+1) and (col=$destCol-1 or col=$destCol or col=$destCol+1);";
				$res=$conn->query($sql); //query to get all the neighbouring slots to the given slot.
				if($res->num_rows>0)
				{
					while($ro=$res->fetch_assoc())
					{
						if($ro['fortification']>0 and $ro['faction']==$faction and !($ro['row']==$destRow and $ro['col']==$destCol))
						{
							$roots[count($roots)]=$ro['root'];
						}
					}
				}
				$roots=condenseArray($roots); //refer line 54
				$j=0;
				while($j<count($roots) and !empty($roots[$j])) //sets the root of all neighbouring slots if occupied as the root of the given slot
				{
					$r=$roots[$j];
					$sql="UPDATE grid SET root='$root' WHERE root='$r'"; //connectivity
					if(!$conn->query($sql) === TRUE)
					{
						var_dump($sql);
						echo "<br>";
						echo "error: ".$conn->error."<br>";
					}
					$j++;
				}
				$sql="UPDATE player SET total=total+1;";
				if($conn->query($sql)===false)
					echo "error (1270): ".$conn->error;
				//moving the troops
				$sql="SELECT troops FROM grid WHERE row=$srcRow and col=$srcCol;";//troops sent from occupied slot
				$res=$conn->query($sql);
				if($res->num_rows>0)
				{
					$deployed=$_SESSION['selectedTroops'];
					$sql="UPDATE grid SET troops=troops-$deployed WHERE row=$srcRow and col=$srcCol;";
					if($conn->query($sql)===false)
						echo "<br>error: ".$conn->error;
				}
				else //troops sent from unoccupied slot
				{
					$deployed=$_SESSION['selectedTroops'];
					$sql="UPDATE troops SET quantity=quantity-$deployed WHERE row=$srcRow and col=$srcCol and
					playerid='$playerid';";
					if($conn->query($sql)===false)
						echo "<br>error: ".$conn->error;
				}
				$message="you lost your settlement at ($destRow,$destCol) to an attack.";
				message($enemy,$message);
				$sql="UPDATE grid SET status='' WHERE row=$destRow and col=$destCol;";
				if($conn->query($sql)===false)
					echo "error : ".$conn->error;
				if($action=="attack")
					$_SESSION['response']='You won the attack! '.$quantity.' soldiers survived.';
				else if($action=="loot")
					$_SESSION['response']='That was a good loot!';
				unset($_SESSION['result']);
				unset($_SESSION['ppercent']);
				unset($_SESSION['bpercent']);
				if($result)
				{
					unset($_SESSION['selectedRow']);
					unset($_SESSION['selectedCol']);
					unset($_SESSION['selectedTroops']);
					unset($_SESSION['destRow']);
					unset($_SESSION['destCol']);
					$_SESSION['settleRow']=$destRow;
					$_SESSION['settleCol']=$destCol;
					$_SESSION['claim']=true;
					//echo "<script>alert('shud')</script>";
					echo "reaches";
					header("location:../mini-game/settle.php");
				}
				
			}
			else
			{
				$sql="SELECT troops FROM grid WHERE row=$srcRow and col=$srcCol;";//troops sent from occupied slot
				$res=$conn->query($sql);
				if($res->num_rows>0)
				{
					$deployed=$_SESSION['selectedTroops'];
					$sql="UPDATE grid SET troops=troops-$deployed WHERE row=$srcRow and col=$srcCol;";
					if($conn->query($sql)===false)
						echo "<br>error: ".$conn->error;
				}
				else //troops sent from unoccupied slot
				{
					$deployed=$_SESSION['selectedTroops'];
					$sql="UPDATE troops SET quantity=quantity-$deployed WHERE row=$srcRow and col=$srcCol and
					playerid='$playerid';";
					if($conn->query($sql)===false)
						echo "<br>error: ".$conn->error;
				}
				$sql="UPDATE grid SET status='' WHERE row=$destRow and col=$destCol;";
				if($conn->query($sql)===false)
					echo "error in status: ".$conn->error;
				$sql="SELECT troops FROM grid WHERE row=$destRow and col=$destCol;";
				$res=$conn->query($sql);
				$r=$res->fetch_assoc();
				$troops=$r['troops'];
				$percent=$_SESSION['bpercent'];
				$troops=$troops*$percent/100;
				$sql="UPDATE grid SET troops=troops-troops*$percent WHERE row=$destRow and col=$destCol;";
				if($conn->query($sql)===false)
					echo "error: ".$conn->error;
				if($action=="attack")
					$_SESSION['response']="You lost the attack";
				else if($action=="loot")
					$_SESSION['response']='That was okay , try with upgraded troops for better luck';
				unset($_SESSION['result']);
				unset($_SESSION['ppercent']);
				unset($_SESSION['bpercent']);
				$sql="UPDATE grid SET status='' WHERE row=$destRow and col=$destCol;";
				if(!$conn->query($sql))
				{
					echo "error : ".$conn->error."   <br>";
					var_dump($sql);
				}
				unset($_SESSION['selectedRow']);
				unset($_SESSION['selectedCol']);
				unset($_SESSION['selectedTroops']);
				unset($_SESSION['destRow']);
				unset($_SESSION['destCol']);
				echo "<script>window.location.href='index.php';</script>";
			}
		}
		else
		{
			if($battleResult) //battle won
			{
					//loss calculation
				$result=false;
				if($fortification>=$troopLevel) //low level troops attacking higher level settlements
				{
					$minLoss=70+(($fortification-$troopLevel)*10);
					if($minLoss>=100)
						$num=100;
					$num=rand($minLoss,100);
					$loss=floor($quantity*$num/100);
					if($maxLoss>0)
						$loss=floor($loss*$maxLoss/100);
					$quantity-=$loss;
					if($quantity<=0)
					{
						$quantity=1;
						echo "remaining=$quantity<br>"."num=$num";
					}
				}
				else //high level or equal level troops attacking low level settlement
				{
					$maxLoss1=100-(($troopLevel-$fortification)*10)+(100-$winChance);
					$num=rand(0,$maxLoss1);
					echo "loss=".$maxLoss1;
					echo "<br>100-(($troopLevel-$fortification)*10)+(100-$winChance)";
					$loss=floor($quantity*$num/100);
					echo "losstroops=".$loss;
					if($maxLoss>0)
						$loss=floor($loss*$maxLoss/100);
					$quantity-=$loss;
					if($quantity<=0)
					{
						$quantity=1;
						echo "remaining=$quantity<br>"."num=$num";
					}
				}
				//removing resource gathering from the defeated slot pending
				$sql="SELECT type FROM grid WHERE row=$destRow and col=$destCol;";
				$res=$conn->query($sql);
				$r=$res->fetch_assoc();
				$distribution=$r['type'];
				$distribution=explode(":",$distribution);
				$foodRegen=$distribution[0];
				$waterRegen=$distribution[1];
				$powerRegen=$distribution[2];
				$metalRegen=$distribution[3];
				$woodRegen=$distribution[4];

				$sql="UPDATE player SET food_regen=food_regen-$foodRegen,water_regen=water_regen-$waterRegen,
				      power_regen=power_regen-$powerRegen,metal_regen=metal_regen-$metalRegen,
				      wood_regen=wood_regen-$woodRegen WHERE tek_emailid='$enemy';";
				if($conn->query($sql)===false)
					echo "<br>error in reducing enemy regen: ".$conn->error;

				//removing resource gathering from the defeated slot
				$sql="UPDATE grid SET occupied='$playerid',type=NULL,faction=$faction,troops=$quantity WHERE
				      row=$destRow and col=$destCol;";
				if($conn->query($sql)===false)
					echo "<br>error: ".$conn->error;

				//plunder

				$sql="SELECT food,water,power,metal,wood FROM player WHERE tek_emailid='$enemy'";
				$res=$conn->query($sql);
				if(!$res)
					echo $conn->error;
				$r=$res->fetch_assoc();
				$plunderFood=$plunderPortion*$r['food']/100;
				$plunderWater=$plunderPortion*$r['water']/100;
				$plunderPower=$plunderPortion*$r['power']/100;
				$plunderMetal=$plunderPortion*$r['metal']/100;
				$plunderWood=$plunderPortion*$r['wood']/100;
				$sql="UPDATE player SET food=food-$plunderWood,water=water-$plunderWater,power=power-$plunderPower
				      ,metal=metal-$plunderMetal,wood=wood-$plunderWood WHERE tek_emailid='$enemy'"; //remove resources
				if($conn->query($sql)===false)                                     //from the defeated
					echo "<br>error: ".$conn->error;
				$plunderFood+=$plunderFood*$plunderBonus/100;
				$plunderWater+=$plunderWater*$plunderBonus/100;
				$plunderPower+=$plunderPower*$plunderBonus/100;
				$plunderMetal+=$plunderMetal*$plunderBonus/100;
				$plunderWood+=$plunderWood*$plunderBonus/100;
				$sql="UPDATE player SET food=food+$plunderWood,water=water+$plunderWater,power=power+$plunderPower
				      ,metal=metal+$plunderMetal,wood=wood+$plunderWood WHERE tek_emailid='$playerid'";
				if($conn->query($sql)===false)
					echo "<br>error: ".$conn->error;

				/*pending resoruce assignment*/

				/*root reassignment*/
				$sql="SELECT root FROM grid WHERE row=$destRow and col=$destCol;";
				$res=$conn->query($sql);
				$rw=$res->fetch_assoc();
				$droot=$rw['root'];
				$newRoot=$droot;
				$sql="SELECT row,col,root FROM grid WHERE root='$droot'"; //reset root for loser enemy
				$res=$conn->query($sql);
				if(!$res)
					echo $conn->error;
				if($res->num_rows>1)
				{
					$sql="UPDATE grid SET root=NULL WHERE row=$destRow and col=$destCol;";
					if($conn->query($sql)===false)
						echo "error: ".$conn->error;
					while($droot==$newRoot)
					{
						$r=$res->fetch_assoc();
						$newRoot=$r['row'].",".$r['col'];
					}
					$sql="UPDATE grid SET root='$newRoot' WHERE root='$droot';";
					if($conn->query($sql)===false)
						echo "error: ".$conn->error;
				}

				$roots= array();
				$root=$destRow.",".$destCol;
				$sql="UPDATE grid SET root='$root' WHERE row=$destRow and col=$destCol;"; //root was made null now
					if($conn->query($sql)===false)									    //it's given the original value
						echo "error: ".$conn->error;
				$troopCount;
				$sql="SELECT row,col,fortification,faction,root FROM grid WHERE (row=$destRow-1 or row=$destRow
				or row=$destRow+1) and (col=$destCol-1 or col=$destCol or col=$destCol+1);";
				$res=$conn->query($sql); //query to get all the neighbouring slots to the given slot.
				if($res->num_rows>0)
				{
					while($ro=$res->fetch_assoc())
					{
						if($ro['fortification']>0 and $ro['faction']==$faction and !($ro['row']==$destRow and $ro['col']==$destCol))
						{
							$roots[count($roots)]=$ro['root'];
						}
					}
				}
				$roots=condenseArray($roots); //refer line 54
				$j=0;
				while($j<count($roots) and !empty($roots[$j])) //sets the root of all neighbouring slots if occupied as the root of the given slot
				{
					$r=$roots[$j];
					$sql="UPDATE grid SET root='$root' WHERE root='$r'"; //connectivity
					if(!$conn->query($sql) === TRUE)
					{
						var_dump($sql);
						echo "<br>";
						echo "error: ".$conn->error."<br>";
					}
					$j++;
				}
				$sql="UPDATE player SET total=total+1;";
				if($conn->query($sql)===false)
					echo "error (1270): ".$conn->error;
				//moving the troops
				$sql="SELECT troops FROM grid WHERE row=$srcRow and col=$srcCol;";//troops sent from occupied slot
				$res=$conn->query($sql);
				if($res->num_rows>0)
				{
					$deployed=$_SESSION['selectedTroops'];
					$sql="UPDATE grid SET troops=troops-$deployed WHERE row=$srcRow and col=$srcCol;";
					if($conn->query($sql)===false)
						echo "<br>error: ".$conn->error;
				}
				else //troops sent from unoccupied slot
				{
					$deployed=$_SESSION['selectedTroops'];
					$sql="UPDATE troops SET quantity=quantity-$deployed WHERE row=$srcRow and col=$srcCol and
					playerid='$playerid';";
					if($conn->query($sql)===false)
						echo "<br>error: ".$conn->error;
				}
				$message="you lost your settlement at ($destRow,$destCol) to an attack.";
				message($enemy,$message);
				$sql="UPDATE grid SET status='' WHERE row=$row and col=$col;";
				if($conn->query($sql)===false)
					echo "error in status: ".$conn->error;
				$_SESSION['response']='You won the attack! '.$quantity.' soldiers survived.';
			}
			else //battle lost
			{
				$sql="SELECT troops FROM grid WHERE row=$srcRow and col=$srcCol;";//troops sent from occupied slot
				$res=$conn->query($sql);
				if($res->num_rows>0)
				{
					$deployed=$_SESSION['selectedTroops'];
					$sql="UPDATE grid SET troops=troops-$deployed WHERE row=$srcRow and col=$srcCol;";
					if($conn->query($sql)===false)
						echo "<br>error: ".$conn->error;
				}
				else //troops sent from unoccupied slot
				{
					$deployed=$_SESSION['selectedTroops'];
					$sql="UPDATE troops SET quantity=quantity-$deployed WHERE row=$srcRow and col=$srcCol and
					playerid='$playerid';";
					if($conn->query($sql)===false)
						echo "<br>error: ".$conn->error;
				}
				$x=$winChance/100; // defensive troops loss calculation if winChance is 40%, 40% of troops will die
				$sql="SELECT troops FROM grid WHERE row=$destRow and col=$destCol;";
				$res=$conn->query($sql);
				$r=$res->fetch_assoc();
				$troops=$r['troops'];
				$troops-=$troops*$x;
				echo $x;
				$sql="UPDATE grid SET status='' WHERE row=$destRow and col=$destCol;";
				if($conn->query($sql)===false)
					echo "error in status: ".$conn->error;
				$sql="UPDATE grid SET troops=$troops WHERE row=$destRow and col=$destCol;";
				if($conn->query($sql)===false)
					echo "error: ".$conn->error;
				$_SESSION['response']="You lost the attack";
			}
			$sql="UPDATE grid SET status='' WHERE row=$destRow and col=$destCol;";
			if(!$conn->query($sql))
			{
				echo "error : ".$conn->error."   <br>";
				var_dump($sql);
			}
			if($battleResult)
			{
				unset($_SESSION['selectedRow']);
				unset($_SESSION['selectedCol']);
				unset($_SESSION['selectedTroops']);
				unset($_SESSION['destRow']);
				unset($_SESSION['destCol']);
				$_SESSION['settleRow']=$destRow;
				$_SESSION['settleCol']=$destCol;
				$_SESSION['claim']=true;
				header("location:../mini-game/settle.php");
			}
			else
			{
				unset($_SESSION['selectedRow']);
				unset($_SESSION['selectedCol']);
				unset($_SESSION['selectedTroops']);
				unset($_SESSION['destRow']);
				unset($_SESSION['destCol']);
				header("location:index.php");
			}
		}
	}	
}

function simBattle($srcRow,$srcCol,$destRow,$destCol,$quantity)
{
	global $conn,$playerid,$moveCostFood,$moveCostWater,$moveCostPower;
	/*resource validation for troops movement*/
	$distance=max(abs($srcRow-$destRow),abs($srcCol-$destCol));
	$foodCost=$distance*$moveCostFood;
	$waterCost=$distance*$moveCostWater;
	$powerCost=$distance*$moveCostPower;
	if(!isset($destRow))
	{
		$destRow=$_SESSION['destRow'];
		$destCol=$_SESSION['destCol'];
	}
	if(!queryResource("food",$foodCost))
	{
		$_SESSION['response']="You don't have the required resources(food).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("water",$waterCost))
	{
		$_SESSION['response']="You don't have the required resources(water).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("power",$powerCost))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("food",$foodCost);
	deductResource("water",$waterCost);
	deductResource("power",$powerCost);

	$sql="SELECT ttype FROM research WHERE playerid='$playerid';";
	$res=$conn->query($sql);
	$row=$res->fetch_assoc();
	$enemy="";
	$maxLoss=100;
	$plunderBonus=0;
	$supportTroops=0;
	$defenceTroops=0;
	$fortification;
	$battleResult=false;
	$winChance=0; //in percentage
	$troop=$row['ttype'];
	$troop=explode(":", $troop); //troops type as  type:level so w-warrior and s-stealth
	$troopType=$troop[0];
	$troopLevel=$troop[1];
	echo "<br>quantity:$quantity<br>lvl:$troopLevel<br>";
	$originalQuantity=$quantity;
	if($troopLevel==0)
	{
		$troopProbability=3; //troop probability is per unit chance of attack success
		$troopLevel=1;
	}
	echo "<br>quantity:$quantity<br>lvl:$troopLevel<br>";
	if($troopType=="s") //more plunder for stealth
	{
		$troopProbability=3;
		if($troopLevel==1)
		{
			$troopProbability=4;
			$plunderBonus=5;
		}
		else if($troopLevel==2)
		{
			$troopProbability=4;
			$plunderBonus=10;
		}
		else if($troopLevel==3)
		{
			$troopProbability=4;
			$plunderBonus=20;
		}
		else if($troopLevel==4)
		{
			$troopProbability=4;
			$plunderBonus=40;
		}
		else if($troopLevel==5)
		{
			$troopProbability=4;
			$plunderBonus=60;
		}
		else if($troopLevel==6)
		{
			$troopProbability=4;
			$plunderBonus=80;
		}
		else if($troopLevel==7)
		{
			$troopProbability=5;
			$plunderBonus=90;
		}
		else if($troopLevel==8)
		{
			$plunderBonus=100;
		}
	}
	else if($troopType=="w") //fewer losses for warriors
	{
		$troopProbability=5;
		if($troopLevel==1)
		{
			$maxLoss=100;
		}
		else if($troopLevel==2)
		{
			$maxLoss=90;
		}
		else if($troopLevel==3)
		{
			$maxLoss=80;
		}
		else if($troopLevel==4)
		{
			$maxLoss=70;
		}
		else if($troopLevel==5)
		{
			$maxLoss=60;
		}
		else if($troopLevel==6)
		{
			$maxLoss=50;
		}
		else if($troopLevel==7)
		{
			$maxLoss=40;
		}
		else if($troopLevel==8)
		{
			$troopProbability=6;
		}
	}
	echo "<br>quantity:$quantity<br>lvl:$troopLevel<br><br>";
	$sql="SELECT faction FROM player WHERE tek_emailid='$playerid';";
	$res=$conn->query($sql);
	$row=$res->fetch_assoc();
	$faction=$row['faction'];
	if($faction==1) //preserve
	{
		$sql="SELECT faction1,faction2 FROM research WHERE playerid='$playerid';";
		$res=$conn->query($sql);
		$row=$res->fetch_assoc();
		if($row['faction2']==1)
		{
			$plunderBonus+=10;
		}
		else if($row['faction2']==2)
		{
			$plunderBonus+=15;
		}
		else if($row['faction2']==3)
		{
			$plunderBonus+=30;
		}
		else
		{
			$plunderBonus+=0;
		}
		$teamBonus=$row['faction1'];
		if($row['faction1']>0) //check if team bonus is researched
		{
			$sql="SELECT row,col FROM grid WHERE (row=$destRow-1 or row=$destRow or row=$destRow+1)
		           and (col=$destCol-1 or col=$destCol or col=$destCol+1)  /*finding all*/
		           and fortification>0 and faction=$faction;";             /*all surrounding allied settled slots*/
		    $res=$conn->query($sql);
		    if($res->num_rows>0)
		    {
		    	$neighbours=array();
			    while($r=$res->fetch_assoc())
			    {
			    	$neighbours[count($neighbours)]=$r;
			    }
			    $sql="SELECT SUM(troops) FROM grid WHERE (row=$destRow-1 or row=$destRow or row=$destRow+1)
			           and (col=$destCol-1 or col=$destCol or col=$destCol+1) and fortification>0 and faction=$faction;";
			    $res=$conn->query($sql);
			    $r=$res->fetch_assoc();
			    $supportTroops+=$r['SUM(troops)'];
			    for($i=0;$i<count($neighbours);$i++)
			    {
			    	$rw=$neighbours[$i]['row'];
			    	$cl=$neighbours[$i]['col'];
			    	$sql="SELECT SUM(quantity) FROM troops WHERE row=$rw and col=$cl;";
			    	$res=$conn->query($sql);
			    	$r=$res->fetch_assoc();
			    	$supportTroops+=$r['SUM(troops)'];
			    }
			    if($teamBonus==1)
			    {
			    	$supportTroops/=10;    //10%
			    }
			    else if($teamBonus==2)
			    {
			    	$supportTroops*=0.3;   //30%
			    }
			    else if($teamBonus==3)
			    {
			    	$supportTroops/=2;     //50%
			    }
		    }
		}
		echo "<br>quantity:$quantity<br>lvl:$troopLevel<br>";
		$quantity+=$supportTroops;
		echo "<br>quantity:$quantity<br>lvl:$troopLevel<br>";
		$quantity*=$troopLevel;
		echo "<br>quantity:$quantity<br>lvl:$troopLevel<br>";
		//defence calculations

		$sql="SELECT troops,fortification FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		var_dump($sql);
		$r1=$res->fetch_assoc();
		$defenceTroops+=$r1['troops']; //per troop defence probability
		$fortification=$r1['fortification'];
		$sql="SELECT SUM(quantity) FROM troops WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$defenceTroops+=$r1['SUM(quantity)'];
		$sql="SELECT defence FROM research WHERE playerid='$playerid';";
		$res=$conn->query($sql);
		//var_dump($res);
		$r1=$res->fetch_assoc();
		$defPerk=$r1['defence'];
		if($defPerk==0)
		{
			$defenceProbability=1;
		}
		if($defPerk==1)
		{
			$defenceProbability=2;
		}
		if($defPerk==2)
		{
			$defenceProbability=3;
		}
		if($defPerk==3)
		{
			$defenceProbability=3;
			$sql="SELECT garrison FROM grid WHERE row=$destRow and col=$destCol;";
			$res=$conn->query($sql);
			$r1=$res->fetch_assoc();
			$defenceTroops+=$r1['garrison'];
		}
		$sql="SELECT occupied FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$enemy=$r1['occupied'];
		$sql="SELECT ttype FROM research WHERE playerid='$enemy';";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$ettype=$r1['ttype'];
		$ettype=explode(":", $ettype);
		$eLevel=$ettype[1];
		if($eLevel==0)
			$eLevel=1;
		$defenceTroops*=$eLevel;
	}
	else if($faction==2) //exploit
	{
		//defence first
		$sql="SELECT occupied FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		var_dump($sql);
		echo $conn->error;
		$r1=$res->fetch_assoc();
		$enemy=$r1['occupied'];
		$sql="SELECT faction1 FROM research WHERE playerid='$enemy';";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$teamBonus=$r1['faction1'];
		if($teamBonus>0) //check if team bonus is researched
		{
			$sql="SELECT row,col FROM grid WHERE (row=$destRow-1 or row=$destRow or row=$destRow+1)
		           and (col=$destCol-1 or col=$destCol or col=$destCol+1)  /*finding all*/
		           and fortification>0 and faction=1;";             /*all surrounding allied settled slots*/
		    $res=$conn->query($sql);
		    if($res->num_rows>0)
		    {
		    	$neighbours=array();
			    while($r=$res->fetch_assoc())
			    {
			    	$neighbours[count($neighbours)]=$r;
			    }
			    $sql="SELECT SUM(troops) FROM grid WHERE (row=$destRow-1 or row=$destRow or row=$destRow+1)
			           and (col=$destCol-1 or col=$destCol or col=$destCol+1) and fortification>0 and faction=1;";
			    $res=$conn->query($sql);
			    $r=$res->fetch_assoc();
			    $supportTroops+=$r['SUM(troops)'];
			    for($i=0;$i<count($neighbours);$i++)
			    {
			    	$rw=$neighbours[$i]['row'];
			    	$cl=$neighbours[$i]['col'];
			    	$sql="SELECT SUM(quantity) FROM troops WHERE row=$rw and col=$cl;";
			    	$res=$conn->query($sql);
			    	$r=$res->fetch_assoc();
			    	$supportTroops+=$r['SUM(quantity)'];
			    }
			    if($teamBonus==1)
			    {
			    	$supportTroops/=10;    //10%
			    }
			    else if($teamBonus==2)
			    {
			    	$supportTroops*=0.3;   //30%
			    }
			    else if($teamBonus==3)
			    {
			    	$supportTroops/=2;     //50%
			    }
		    }
		}
		$sql="SELECT troops FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		$defenceTroops=$r['troops'];
		$sql="SELECT SUM(quantity) FROM troops WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		$defenceTroops+=$r['SUM(quantity)'];
		$sql="SELECT defence FROM research WHERE playerid='$playerid';";
		$res=$conn->query($sql);
		echo $conn->error;
		$r1=$res->fetch_assoc();
		$defPerk=$r1['defence'];
		if($defPerk==0)
		{
			$defenceProbability=1;
		}
		if($defPerk==1)
		{
			$defenceProbability=2;
		}
		if($defPerk==2)
		{
			$defenceProbability=3;
		}
		if($defPerk==3)
		{
			$defenceProbability=3;
			$sql="SELECT garrison FROM grid WHERE row=$destRow and col=$destCol;";
			$res=$conn->query($sql);
			$r1=$res->fetch_assoc();
			$defenceTroops+=$r1['garrison'];
		}
		$defenceTroops+=$supportTroops;
		$sql="SELECT ttype FROM research WHERE playerid='$enemy';";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$ettype=$r1['ttype'];
		$ettype=explode(":", $ettype);
		$eLevel=$ettype[1];
		$defenceTroops*=$eLevel;

		//attack
		$sql="SELECT fortification FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$fortification=$r1['fortification'];
		$quantity*=$troopLevel;
	}

	//sim battle
	if($fortification==0)
		$fortification=1;
	$quantity/=$fortification;
	$attackProb=$quantity*$troopProbability;
	$defProb=$defenceTroops*$defenceProbability;
	echo "<BR>defTroops:$defenceTroops<br>attckTroops:$quantity<br><br>";
	$winChance=($quantity*$troopProbability)-($defenceTroops*$defenceProbability);
	$result=rand(0,100);
	if($result>$winChance) //loss
	{
		$battleResult=false;
		echo "<br>num:$result<br>attck:$attackProb<br>def:$defProb<br>";
		echo "lost had winchance=".$winChance;
	}
	else //win
	{
		$battleResult=true;
		echo "<br>num:$result<br>attck:$attackProb<br>def:$defProb<br>";
		echo "<br>won had winchance=".$winChance;
	}
	$give['result']=$battleResult;
	$give['winChance']=$winChance;
	$give['plunderBonus']=$plunderBonus;
	$give['fortification']=$fortification;
    $give['maxLoss']=$maxLoss;
    $give['enemy']=$enemy;
    $give['faction']=$faction;
    $give['troopLevel']=$troopLevel;
    $give['troopType']=$troopType;
    return $give;
}

function loot($srcRow,$srcCol,$destRow,$destCol,$quantity)
{
	global $conn,$playerid,$moveCostFood,$moveCostWater,$moveCostPower;
	/*pending based on location*/
	$sql="SELECT civperk1 FROM research WHERE playerid='$playerid';"; //find if player has researched for move
	$res=$conn->query($sql);									   //discount
	$r=$res->fetch_assoc();
	$lvl=$r['civperk1'];
	if($lvl==1)
	{
		$moveCostFood=intval($moveCostFood-$moveCostFood*0.1);
		$moveCostWater=intval($moveCostWater-$moveCostWater*0.1);
		$moveCostPower=intval($moveCostPower-$moveCostPower*0.1);
	}
	else if($lvl==2)
	{
		$moveCostFood=intval($moveCostFood-$moveCostFood*0.1);
		$moveCostWater=intval($moveCostWater-$moveCostWater*0.1);
		$moveCostPower=intval($moveCostPower-$moveCostPower*0.1);
	}
	else if($lvl==3)
	{
		$moveCostFood=intval($moveCostFood-$moveCostFood*0.1);
		$moveCostWater=intval($moveCostWater-$moveCostWater*0.1);
		$moveCostPower=intval($moveCostPower-$moveCostPower*0.1);
	}


	$distance=max(abs($srcRow-$destRow),abs($srcCol-$destCol));
	$foodCost=2*$distance*$moveCostFood;
	$waterCost=2*$distance*$moveCostWater;
	$powerCost=2*$distance*$moveCostPower;
	if(!queryResource("food",$foodCost))
	{
		$_SESSION['response']="You don't have the required resources(food).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("water",$waterCost))
	{
		$_SESSION['response']="You don't have the required resources(water).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("power",$powerCost))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("food",$foodCost);
	deductResource("water",$waterCost);
	deductResource("power",$powerCost);


	$sql="SELECT special FROM grid WHERE row=$destRow and col=$destCol;";	
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	if($r['special']!=3)
	{
		$_SESSION['response']="you cannot loot there";
		return;
	}
	$sql="SELECT ttype FROM research WHERE playerid='$playerid'";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$tr=$r['ttype'];
	$tr=explode(":", $tr);
	$level=$tr[1];
	$type=$tr[0];
	if($type == "s" or $type=="n")
		$type=0;
	else 
		$type=1;
	if($level==0)
		$level=1;
	$_SESSION['playerLevel']=$level;
	$_SESSION['baseLevel']=6;
	$_SESSION['troopType']=1;
	$_SESSION['destRow']=$destRow;
	$_SESSION['destCol']=$destCol;
	$_SESSION['selectedRow']=$srcRow;
	$_SESSION['selectedCol']=$srccol;
	header("location:../mini-game/attack/redirect.php");
}


function attackM($srcRow,$srcCol,$destRow,$destCol,$quantity,$factor)
{
	global $conn,$playerid,$faction,$moveCostFood,$moveCostWater,$moveCostPower;
	$_SESSION['destRow']=$destRow;
	$_SESSION['destCol']=$destCol;
	if(!validateAction("attack",$destRow,$destCol))
	{
		$_SESSION['response']="the slot is now accupied by an ally :( or is a base slot which cannot be captured.";
		header("location:index.php");
	}

	//confirm if player can afford to move
	$sql="SELECT civperk1 FROM research WHERE playerid='$playerid';"; //find if player has researched for move
	$res=$conn->query($sql);									   //discount
	$r=$res->fetch_assoc();
	$lvl=$r['civperk1'];
	if($lvl==1)
	{
		$moveCostFood=intval($moveCostFood-$moveCostFood*0.1);
		$moveCostWater=intval($moveCostWater-$moveCostWater*0.1);
		$moveCostPower=intval($moveCostPower-$moveCostPower*0.1);
	}
	else if($lvl==2)
	{
		$moveCostFood=intval($moveCostFood-$moveCostFood*0.1);
		$moveCostWater=intval($moveCostWater-$moveCostWater*0.1);
		$moveCostPower=intval($moveCostPower-$moveCostPower*0.1);
	}
	else if($lvl==3)
	{
		$moveCostFood=intval($moveCostFood-$moveCostFood*0.1);
		$moveCostWater=intval($moveCostWater-$moveCostWater*0.1);
		$moveCostPower=intval($moveCostPower-$moveCostPower*0.1);
	}


	$distance=max(abs($srcRow-$destRow),abs($srcCol-$destCol));
	$foodCost=$factor*$distance*$moveCostFood;
	$waterCost=$factor*$distance*$moveCostWater;
	$powerCost=$factor*$distance*$moveCostPower;
	if(!queryResource("food",$foodCost))
	{
		$_SESSION['response']="You don't have the required resources(food).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		header("location:index.php");
	}
	if(!queryResource("water",$waterCost))
	{
		$_SESSION['response']="You don't have the required resources(water).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		header("location:index.php");
	}
	if(!queryResource("power",$powerCost))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		header("location:index.php");
	}
	deductResource("food",$foodCost);
	deductResource("water",$waterCost);
	deductResource("power",$powerCost);

	$result=simBattle($srcRow,$srcCol,$destRow,$destCol,$quantity);
	$playerLevel=$result['troopLevel'];
	$fortification=$result['fortification'];
	/*warrior-0 stealth-1*/
	$type=$result['troopType'];
	$addDiff=0;
	$decDiff=0;
	if($type=="s" or $type=="n")
	{
		$type=1;
	}
	else if($type=="w")
	{
		$type=0;
	}
	$winChance=$result['winChance'];
	if($winChance>=90 and $winChance<100) // decrease difficulty if there are enough troops
	{
		$decDiff=1;
	}
	if($winChance>=100 and $winChance<110)
	{
		$decDiff=2;
	}
	if($winChance>=110 and $winChance<120)
	{
		$decDiff=3;
	}
	if($winChance>=120 and $winChance<130)
	{
		$decDiff=4;
	}
	if($winChance>=130 and $winChance<140)
	{
		$decDiff=5;
	}
	else if($winChance>=140)
	{
		$decDiff=6;
	}


	if($winChance<50 and $winChance>40) //if win chance is very less we add more difference between the troops
	{									// and base i.e increase the difficulty
		$addDiff=3;
	}
	else if($winChance<=40 and $winChance>30)
	{
		$addDiff=4;
	}
	else if($winChance<=30 and $winChance>20)
	{
		$addDiff=5;
	}
	else if($winChance<=20 and $winChance>10)
	{
		$addDiff=6;
	}
	else if($winChance<10)
	{
		$addDiff=7;
	}
	if($fortification-$playerLevel<$addDiff and $addDiff>0)
	{
		while($fortification<8 and $fortification-$playerLevel<$addDiff)
		{
			$fortification++;
		}
		while($playerLevel>1 and $fortification-$playerLevel<$addDiff)
		{
			$playerLevel--;
		}
	}
	if($playerLevel-$fortification<$decDiff and $decDiff>0)
	{
		while($fortification>2 and $playerLevel-$fortification>$decDiff)
		{
			$fortification--;
		}
		while($playerLevel<8 and $fortification-$playerLevel>$decDiff)
		{
			$playerLevel++;
		}
	}
	echo "difference:".$addDiff."<br>";
	echo $fortification;
	$_SESSION['playerLevel']=$playerLevel;
	$_SESSION['troopType']=$type;
	$_SESSION['baseLevel']=$fortification; 
	/*redirect to mini-game*/
 	header('location:../mini-game/attack/redirect.php');
    /*pending mitesh's mini game*/

	/*get result from minigame and calculate losses*/
}


function attack($srcRow,$srcCol,$destRow,$destCol,$quantity)
{
	if(!validateAction("attack",$destRow,$destCol))
	{
		$_SESSION['response']="the slot is now accupied by an ally :(.";
		return;
	}
	global $conn,$playerid,$moveCostFood,$moveCostWater,$moveCostPower,$plunderPortion;
	/*resource validation for troops movement*/
	$sql="SELECT special FROM grid WHERE row=$destRow and col=$destCol;";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$slotType=$r['special'];
	$sql="SELECT civperk2 FROM research WHERE playerid='$playerid';";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	if($r['civperk2']<4 and $slotType==4)
	{
		$_SESSION['response']="you cannot attack settlements on water yet.Get civperk2 level 4 first";
		return;
	}
	$sql="UPDATE grid SET status='attack' WHERE row=$destRow and col=$destCol;";
	if($conn->query($sql)===false)
		echo "error in status: ".$conn->error;
	$sql="SELECT civperk1 FROM research WHERE playerid='$playerid';"; //find if player has researched for move
	$res=$conn->query($sql);									   //discount
	$r=$res->fetch_assoc();
	$lvl=$r['civperk1'];
	if($lvl==1)
	{
		$moveCostFood-=$moveCostFood*0.1;
		$moveCostWater-=$moveCostWater*0.1;
		$moveCostPower-=$moveCostPower*0.1;
	}
	else if($lvl==2)
	{
		$moveCostFood-=$moveCostFood*0.2;
		$moveCostWater-=$moveCostWater*0.2;
		$moveCostPower-=$moveCostPower*0.2;
	}
	else if($lvl==3)
	{
		$moveCostFood-=$moveCostFood*0.3;
		$moveCostWater-=$moveCostWater*0.3;
		$moveCostPower-=$moveCostPower*0.3;
	}


	$distance=max(abs($srcRow-$destRow),abs($srcCol-$destCol));
	$foodCost=$distance*$moveCostFood;
	$waterCost=$distance*$moveCostWater;
	$powerCost=$distance*$moveCostPower;
	if(!queryResource("food",$foodCost))
	{
		$_SESSION['response']="You don't have the required resources(food).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("water",$waterCost))
	{
		$_SESSION['response']="You don't have the required resources(water).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("power",$powerCost))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("food",$foodCost);
	deductResource("water",$waterCost);
	deductResource("power",$powerCost);

	$enemy="";
	$maxLoss=100;
	$plunderBonus=0;
	$supportTroops=0;
	$defenceTroops=0;
	$fortification;
	$battleResult=false;
	$winChance=0; //in percentage
	$originalQuantity=$quantity;

	simAftermath($srcRow,$srcCol,$destRow,$destCol,$quantity,"attack");
}


function condenseArray($arr) //removes duplicates and would reduce load on server as little as it already is..
{
	$ar=array();
	if(count($arr)!=0)
	{
		$ar[0]=$arr[0];
	}
	else
		return;
	$j=1;
	for($i=1;$i<count($arr);$i++)
	{
		//$ar[$j]=$arr[$i];
		//unset($arr[$i]);
		if(array_search($arr[$i],$ar) ===false)
		{
			$ar[$j]=$arr[$i];
			$j++;
		}
	}
	return $ar;
}


function settle($row,$col) //occupies selected slot pending increment of resource regen
{
	if(!validateAction("settle",$row,$col))
	{
		$_SESSION['response']="the slot is now accupied by an enemy/ally :(.";
		header("location:index.php");
	}
	global $conn,$playerid,$faction,$settleWoodCost,$settleMetalCost,$settlePowerCost;
	$sql="UPDATE grid SET status='settle' WHERE row=$row and col=$col;";
	if($conn->query($sql)===false)
		echo "error in status: ".$conn->error;
	$sql="SELECT special FROM grid WHERE row=$row and col=$col;";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$slotType=$r['special'];
	$sql="SELECT civperk2 FROM research WHERE playerid='$playerid';";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	if($r['civperk2']<4 and ($slotType==4 or $slotType==3))
	{
		$_SESSION['response']="you cannot settle on water or mountain yet .Get civperk2 level 4 first";
		return;
	}
	if($faction==2)
	{
		$sql="SELECT faction2 FROM research WHERE playerid='$playerid';";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		$lvl=$r['faction2'];
		if($lvl==1)
		{
			$settleWoodCost-=$settleWoodCost*0.1;
			$settleMetalCost-=$settleMetalCost*0.1;
			$settlePowerCost-=$settlePowerCost*0.1;
		}
		else if($lvl==2)
		{
			$settleWoodCost-=$settleWoodCost*0.2;
			$settleMetalCost-=$settleMetalCost*0.2;
			$settlePowerCost-=$settlePowerCost*0.2;
		}
		else if($lvl==3)
		{
			$settleWoodCost-=$settleWoodCost*0.3;
			$settleMetalCost-=$settleMetalCost*0.3;
			$settlePowerCost-=$settlePowerCost*0.3;
		}
	}

	/*pending resource allocation*/
	if(!queryResource("wood",$settleWoodCost)) //settling resources
	{
		$_SESSION['response']="You don't have the required resources(wood).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("power",$settlePowerCost))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("metal",$settleMetalCost))
	{
		$_SESSION['response']="You don't have the required resources(metal).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("wood",$settleWoodCost);
	deductResource("metal",$settleMetalCost);
	deductResource("power",$settlePowerCost);
	$sql="SELECT faction FROM player WHERE tek_emailid='$playerid';";// find player faction
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$faction=$r['faction'];
	$roots= array();
	$root=$row.",".$col;
	$troopCount;
	$sql="SELECT row,col,fortification,faction,root FROM grid WHERE (row=$row-1 or row=$row or row=$row+1) and (col=$col-1 or col=$col or col=$col+1);";
	$res=$conn->query($sql); //query to get all the neighbouring slots to the given slot.
	if($res->num_rows>0)
	{
		$i=0;
		while($ro=$res->fetch_assoc())
		{
			if($ro['fortification']>0 and $ro['faction']==$faction and !($ro['row']===$row and $ro['col']===$col))
			{
				$roots[$i]=$ro['root'];
			}
		}
	}
	$sql="SELECT quantity FROM troops WHERE row=$row and col=$col and playerid='$playerid';"; //find number of troops already stationed
	$res=$conn->query($sql); //query to get all the neighbouring slots to the given slot.
	if($res->num_rows>0)
	{
		while($ro=$res->fetch_assoc())
		{
			$troopCount=$ro['quantity'];
		}
	}
	$roots=condenseArray($roots); //refer line 54
	$j=0;
	while($j<count($roots)) //sets the root of all neighbouring slots if occupied as the root of the given slot
	{
		var_dump($roots);
		echo "<br>";
		$r=$roots[$j];
		$sql="UPDATE grid SET root='$root' WHERE root='$r'"; //connectivity
		if(!$conn->query($sql) === TRUE)
		{
			var_dump($sql);
			echo "<br>";
			echo "error: ".$conn->error."<br>";
		}
		$j++;
	}
	$sql="UPDATE grid SET occupied='$playerid',faction=$faction,fortification=1, root='$root',troops=$troopCount
	      WHERE row=$row and col=$col;"; //transferring troops from troop to grid
	if(!$conn->query($sql) === TRUE)
	{
		var_dump($sql);
		echo "<br>";
		echo "error: ".$conn->error."<br>";
	}
	$sql="DELETE FROM troops WHERE row=$row and col=$col";
	if(!$conn->query($sql) === TRUE)
	{
		var_dump($sql);
		echo "<br>";
		echo "error: ".$conn->error."<br>";
	}
	$sql="UPDATE player SET total=total+1 WHERE tek_emailid='$playerid';";
	if(!$conn->query($sql) === TRUE)
	{
		var_dump($sql);
		echo "<br>";
		echo "error: ".$conn->error."<br>";
	}
	$_SESSION['settleRow']=$row;
	$_SESSION['settleCol']=$col;
	$_SESSION['claim']=true;
	header("location:../mini-game/settle.php");
}


function scout($row,$col)
{
	global $conn,$playerid,$faction,$scoutCostFood,$scoutCostWater;
	$output="";
	if($faction==2)
	{
		$sql="SELECT faction1 FROM research WHERE playerid='$playerid';";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		$lvl=$r['faction1'];
		if($lvl==1)
		{
			$scoutCostFood-=$scoutCostFood*0.25;
			$scoutCostWater-=$scoutCostWater*0.25;
		}
		else if($lvl==2)
		{
			$scoutCostFood-=$scoutCostFood*0.5;
			$scoutCostWater-=$scoutCostWater*0.5;
		}
		else if($lvl==3)
		{
			$scoutCostFood=0;
			$scoutCostWater=0;
		}
	}
	if(!queryResource("food",$scoutCostFood))
	{
		$_SESSION['response']="You don't have the required resources(food).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("water",$scoutCostWater))
	{
		$_SESSION['response']="You don't have the required resources(water).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("food",$scoutCostFood);
	deductResource("water",$scoutCostWater);
	$troops=0;
	$sql="SELECT occupied,fortification,troops,faction,special FROM grid WHERE row=$row and col=$col;"; //scouting enemy occupied slot
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$fortification=$r['fortification'];
	$occupied=$r['occupied'];
	$sfaction=$r['faction'];
	$winchance=0;
	if($r['special']==0)
		$slotType="mud";
	else if($r['special']==1)
		$slotType="grass";
	else if($r['special']==2)
		$slotType="sand";
	else if($r['special']==3)
		$slotType="water";
	else if($r['special']==4)
		$slotType="mountain";
	if($fortification!=0)
	{
		$troops=$r['troops'];
		$sql="SELECT SUM(quantity) FROM troops WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$troops+=$r1['SUM(quantity)'];
		if(isset($_SESSION['selectedTroops']) and !empty($_SESSION['selectedTroops']))
		{
			echo "$row<br><br>";
			$srcRow=$_SESSION['selectedRow'];
			$srcCol=$_SESSION['selectedCol'];
			$chance=simBattle($srcRow,$srcCol,$row,$col,$_SESSION['selectedTroops']);
		}
		else
			$chance=0;
	}
	else
	{
		$chance=0;
		$sql="SELECT playerid,quantity FROM troops WHERE row=$row and col=$col;"; //scouting unoccupied slot
		$res=$conn->query($sql);
		if($res->num_rows>0)
		{
			while($r2=$res->fetch_assoc())
			{
				$enemy=$r2['playerid'];
				$otroops=$r2['quantity']; //number of troops of one enemy occupant
				$sql1="SELECT open,ttype FROM research WHERE playerid='$occupied';";
				$res1=$conn->query($sql1);
				$r=$res1->fetch_assoc();
				$obperk=$r['open'];
				if($obperk==1)
				{
					$hiddenTroops=max(0.25*$otroops,1); //25% percent troops hidden and are twice as effective
					$otroops-=$hiddenTroops;
				}
				else if($obperk==2)
				{
					$hiddenTroops=max(0.5*$otroops,1); //50% percent troops hidden and are twice as effective
					$otroops-=$hiddenTroops;
				}
				else if($obperk==3)
				{
					$hiddenTroops=$otroops; //100% percent troops hidden and are twice as effective
					$otroops-=$hiddenTroops;
				}
				$troops+=$otroops;
				$sql="SELECT faction FROM player WHERE tek_emailid='$enemy';"; //scouting unoccupied slot
				$res=$conn->query($sql);
				$r=$res->fetch_assoc();
				$sfaction=$r['faction'];
				$occupied="unoccupied";
			}
		}
	}

	if($sfaction==1 )
	{
		$dispfaction="Eos";
	}
	else if($sfaction==2)
	{
		$dispfaction="Zephyros";
	}

	if($chance==0)
	{
	$output="(".$row.",".$col.") Occupant : ".$occupied."<br>Fortification : ".$fortification."<br>Troops : ".$troops.
	    "<br>Faction : ".$dispfaction."<br>Type:".$slotType;
	}
	else
	{
		$winChance=$chance['winChance'];
		if($slotType==3)
		{
			$output="(".$row.",".$col.") Occupant : ".$occupied."<br>Fortification : ".$fortification."<br>Troops : ".$troops.
	                 "<br>Faction : ".$dispfaction."<br>Type:".$slotType."<br>Loot percentage: ".$winChance;
		}
		else
		{
			$output="(".$row.",".$col.") Occupant : ".$occupied."<br>Fortification : ".$fortification."<br>Troops : ".$troops.
	    	        "<br>Faction : ".$dispfaction."<br>Type:".$slotType."<br>Win probability : ".$winChance;
		}	
	}
	echo $output;
	echo $faction."<BR>".$sfaction;
	if($faction!=$sfaction and $sfaction!=0 and $fortification==0)
	{
		$_SESSION['scoutRow']=$row;
		$_SESSION['scoutCol']=$col;
		$_SESSION['side']="enemy";
	}
	else if($faction==$sfaction and $fortification==0)
	{
		$_SESSION['scoutRow']=$row;
		$_SESSION['scoutCol']=$col;
		$_SESSION['side']="ally";
	}
	echo "<BR><BR><BR>";
	echo "faction:$faction<br>gotten faction:$sfaction";
	$_SESSION['response']=$output;
}


function createTroops($row,$col,$quantity)
{
	if(!validateAction("createTroops",$row,$col))
	{
		$_SESSION['response']="the slot is now accupied by an enemy :(.";
		return;
	}
	global $conn,$createTroopCostFoodBase,$createTroopCostWaterBase,$createTroopCostPowerBase;
	$createTroopCostFood=$quantity*$createTroopCostFoodBase;
	$createTroopCostWater=$quantity*$createTroopCostWaterBase;
	$createTroopCostPower=$quantity*$createTroopCostPowerBase;
	if(!queryResource("food",$createTroopCostFood))
	{
		$_SESSION['response']="You don't have the required resources(food).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("water",$createTroopCostWater))
	{
		$_SESSION['response']="You don't have the required resources(water).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("power",$createTroopCostPower))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("food",$createTroopCostFood);
	deductResource("water",$createTroopCostWater);
	deductResource("power",$createTroopCostPower);
	$sql="UPDATE grid SET troops=troops+$quantity WHERE row=$row and col=$col;";
	if($conn->query($sql)==false)
	{
		echo "error(160) : ".$conn->error;
		$_SESSION['response']="error in query";
	}
	else
		$_SESSION['response']="created ".$quantity." troops.";
}


function fortify($row,$col)/*pending*/
{
	if(!validateAction("fortify",$row,$col))
	{
		$_SESSION['response']="the slot is now accupied by an ally or you tried to fortify your spawn point do not fortify them since they cannot 
		be conquered or settled upon :(.";
		header("location:index.php");
	}
	global $conn,$playerid,$fortifyWoodCost,$fortifyMetalCost,$fortifyPowerCost;
	$sql="SELECT fortification FROM grid WHERE row=$row and col=$col;";
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$level=$r['fortification'];
	if(!queryResource("wood",$fortifyWoodCost[$level-1]))
	{
		$_SESSION['response']="You don't have the required resources(food).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("metal",$fortifyMetalCost[$level-1]))
	{
		$_SESSION['response']="You don't have the required resources(water).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	if(!queryResource("power",$fortifyPowerCost[$level-1]))
	{
		$_SESSION['response']="You don't have the required resources(power).";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
	deductResource("wood",$fortifyWoodCost[$level-1]);
	deductResource("metal",$fortifyMetalCost[$level-1]);
	deductResource("power",$fortifyPowerCost[$level-1]);
	//validated resources
	$sql="UPDATE grid SET fortification=fortification+1 WHERE row=$row and col=$col;";
	if($conn->query($sql)==false)
	{
		echo "error(160) : ".$conn->error;
		$_SESSION['response']="error in query";
	}
	else
		$_SESSION['response']="fortified to level : ".($level+1);
}




if(isset($_POST['fortify']))
{
	$row=$_POST['row'];
	$col=$_POST['col'];
	fortify($row,$col);
	header("location:index.php");
}
else if(isset($_POST['settle']))
{
	$row=$_POST['row'];
	$col=$_POST['col'];
	//$row=1;
	//$col=13;
	settle($row,$col);
	//header("location:index.php");
}
else if(isset($_POST['select_troops']))
{
	$row=$_POST['row'];
	$col=$_POST['col'];
	if(isset($_POST['quantity']) and !empty($_POST['quantity']))
	{
		$quantity=$_POST['quantity'];
	}
	else
		$quantity=1;
	if($quantity>0)
	{
		echo "looks okay";
		if(troopExist($row,$col,$quantity))
		{
			$_SESSION['selectedRow']=$row;
			$_SESSION['selectedCol']=$col;
			$_SESSION['selectedTroops']=$quantity;
		}
	}
	header("location:index.php");
}
else if(isset($_POST["scout"]))
{
	scout($_POST['row'],$_POST['col']);
	//scout(1,19);
	header("location:index.php");
}
else if(isset($_POST['move']))
{
	if(isset($_SESSION['selectedTroops']) and !empty($_SESSION['selectedTroops']))
	{
		$quantity=$_SESSION['selectedTroops'];
		unset($_SESSION['selectedTroops']);
	}
	else
	{
		$quantity=1;
	}
	$srcrow=$_SESSION['selectedRow'];
	$srccol=$_SESSION['selectedCol'];
	$row=$_POST['row'];
	$col=$_POST['col'];
	//echo $quantity;
	move($srcrow,$srccol,$row,$col,$quantity);
	header("location:index.php");
}
else if(isset($_POST['attack']))
{
	if(isset($_SESSION['selectedTroops']) and !empty($_SESSION['selectedTroops']))
	{
		$quantity=$_SESSION['selectedTroops'];
	}
	else
	{
		$quantity=1;
	}
	$srcrow=$_SESSION['selectedRow'];
	$srccol=$_SESSION['selectedCol'];
	if(isset($_POST['row'])and isset($_POST['col']))
	{
		$row=$_POST['row'];
		$col=$_POST['col'];
		//echo $quantity;
		attackM($srcrow,$srccol,$row,$col,$quantity,1);
	}
	//header("location:index.php");
}
else if(isset($_POST['sim_attack']))
{
	if(isset($_SESSION['selectedTroops']) and !empty($_SESSION['selectedTroops']))
	{
		$quantity=$_SESSION['selectedTroops'];
	}
	else
	{
		$quantity=1;
	}
	$srcrow=$_SESSION['selectedRow'];
	$srccol=$_SESSION['selectedCol'];
	$row=$_POST['row'];
	$col=$_POST['col'];
	//echo $quantity;
	attack($srcrow,$srccol,$row,$col,$quantity);
	header("location:index.php");
}
else if(isset($_POST['loot']))
{
	if(isset($_SESSION['selectedTroops']) and !empty($_SESSION['selectedTroops']))
	{
		$quantity=$_SESSION['selectedTroops'];
	}
	else
	{
		$quantity=1;
	}
	$_SESSION['loot']=1;
	$srcrow=$_SESSION['selectedRow'];
	$srccol=$_SESSION['selectedCol'];
	$row=$_POST['row'];
	$col=$_POST['col'];
	//echo $quantity;
	loot($srcrow,$srccol,$row,$col,$quantity);
	//header("location:index.php");
}
else if(isset($_POST['create_troops']))
{
	$row=$_POST['row'];
	$col=$_POST['col'];
	if(isset($_POST['quantity']) and !empty($_POST['quantity']))
	{
		$quantity=$_POST['quantity'];
	}
	else
		$quantity=1;
	createTroops($row,$col,$quantity);
	header("location:index.php");
}
else if(isset($_SESSION['result']))
{
	if(isset($_SESSION['selectedTroops']) and !empty($_SESSION['selectedTroops']))
	{
		$quantity=$_SESSION['selectedTroops'];
	}
	$srcrow=$_SESSION['selectedRow'];
	$srccol=$_SESSION['selectedCol'];
	$row=$_SESSION['destRow'];
	$col=$_SESSION['destCol'];
	//echo $quantity;
	simAftermath($srcrow,$srccol,$row,$col,$quantity,"attack");
	//header("location:index.php");
}
else if(isset($_POST['add_base_tile']))
{
	$row=$_POST['row'];
	$col=$_POST['col'];
	addBaseTile($row,$col);
}
else
	header("location:index.php");
?>
<html>
<head>
	<script>
		function refresh()
		{
        		setInterval(function(){
        			window.location.reload(true);
        		}, 5000);
        }
    </script>
</head>
<body>
</body>
</html>
