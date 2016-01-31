<?php
require "../db_access/db.php";
require "connect.php";
$faction/*=$_SESSION['faction']*/;
$faction=1;	//temporary!! don't forget to remove!!
$playerid/*=$_SESSION['tek_emailid']*/;
$playerid=1; //temporary!! don't forget to remove!! 
$moveCostFood=6;
$moveCostWater=8;
$moveCostPower=3;
$scoutCostFood=4;
$scoutCostWater=6;
/*number of troops garrisonable by fortification level
1-10
2-13
3-16
4-20
5-24
6-26
7-30
8-35
*/

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
		echo "<br>enough";
		$sql="UPDATE `player` SET $resource=$resource-'$value' WHERE tek_emailid='$playerid'";	
			if($conn->query($sql)===false)
			{
				echo "error: ".$conn->error;
			}
		return true;
	}
}
function move($srcRow,$srcCol,$destRow,$destCol,$quantity) //move works in 2 steps, first select troops from an occupied slot  
{       		                                           //then select the slot to move the troops to 
	$distance=max(abs($srcRow-$destRow),abs($srcCol-$destCol));
	$sroot="x,y";
	$droot="x,z";
	$enemy; //enemy playerid
	$etroops=0; //enemy total troop strength at the destination slot
	$obperk=0; //open battle perk level of each enemy
	$ambushSurvive=false;
	global $conn,$moveCostFood,$moveCostPower,$playerid;
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
	$foodCost=$distance*$moveCostFood;
	$waterCost=$distance*$moveCostWater;
	$powerCost=$distance*$moveCostPower;
	$troopExist=false;
	$sql="SELECT troops FROM grid WHERE row=$srcRow and col=$srcCol;"; //check if required troops present in grid table
	$res=$conn->query($sql);
	if($res->num_rows>0)
	{
		$row=$res->fetch_assoc();
		if($row['troops']<$quantity)
		{
			$troopExist=false;
		}
		else
		{
			$troopExist=true;
		}
	}
	$sql="SELECT playerid,quantity FROM troops WHERE row=$srcRow and col=$srcCol;"; //check if required troops present 
	$res=$conn->query($sql);                                                        //in troops table												
	if($res->num_rows>0)
	{
		$row=$res->fetch_assoc();
		if($row['quantity']<$quantity or $row['playerid']!=$playerid)
		{
			if(!$troopExist) //troops not enough or not present in both tables
			{
				$_SESSION['response']="You don't have those many troops. You have ".$row['troops']." soldiers.
				Create more soldier(s)!";
				unset($_SESSION['selectedRow']);
				unset($_SESSION['selectedCol']);
				unset($_SESSION['selectedTroops']);
				return;
			} 
		}
	}
	else if(!$troopExist)
	{
		$_SESSION['response']="You don't have those many troops. You have ".$row['troops']." soldiers.
				Create more soldiers!";
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
		unset($_SESSION['selectedTroops']);
		return;
	}
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
	/*open battle sim*/
	$troopDistribution=[[]];
	$sql="SELECT playerid,quantity FROM troops WHERE row=$destRow and col=$destCol;";
	$res=$conn->query($sql);
	if($res->num_rows>0)
	{
		$i=0;
		while($row=$res->fetch_assoc())
		{
			$enemy=$row['playerid'];
			$otroops=$row['quantity']; //number of troops of one enemy occupant
			$sql1="SELECT open,ttype FROM research WHERE playerid=$enemy;";
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
			$lvl=$row['ttype'];      
			$lvl=explode(":", $lvl);
			$level=$lvl[1];
			$troopDistribution[$i]['troops']=$otroops-$hiddenTroops;
			$troopDistribution[$i]['playerid']=$enemy;
			$otroops=$otroops*$level;
			$etroops+=$otroops;
			$i++;
		}
		$sql="SELECT ttype FROM research WHERE playerid=$playerid;";
		$res1=$conn->query($sql);
		$row=$res1->fetch_assoc();
		$flvl=$row['ttype'];
		$flvl=explode(":", $flvl);
		$flevel=$flvl[1];
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
				$sql2="UPDATE troops SET quantity=quantity-$loss WHERE playerid=$id and row=$destRow and col=$destCol;";
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
				if($ambushSurvive==true)
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
				$sql="UPDATE grid SET troops=troops-$quantity WHERE row=$destRow and col=$destCol";
				if($conn->query($sql)==false)
						echo "error(135) : ".$conn->error."<br>";
				if($ambushSurvive==true)
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
				$sql="UPDATE troops SET quantity=quantity-$quantity WHERE row=$srcRow and col=$srcCol;";
				if($conn->query($sql)==false)
					echo "error (143): ".$conn->error."<br>";
				$sql="SELECT quantity FROM troops WHERE row=$destRow and col=$destCol;"; //check if troops are already present
				$res1=$conn->query($sql);
				if($res1->num_rows==0)
				{
					$sql="INSERT INTO troops (row,col,playerid,quantity) VALUES ($destRow,$destCol,'$playerid',$quantity);";
					if($conn->query($sql)==false)
						echo "error(150) : ".$conn->error."<br>";
				}
				else
				{
					$sql="UPDATE troops SET quantity=quantity+$quantity WHERE row=$destRow and col=$destCol;";
					if($conn->query($sql)==false)
						echo "error (156): ".$conn->error."<br>";
				}
				$sql="DELETE FROM troops WHERE quantity<=0";
				if($conn->query($sql)==false)
					echo "error(160) : ".$conn->error;
				if($ambushSurvive==true)
				{
					$_SESSION['response']="There was an ambush on your troops and you lost a few soldiers<br>
					                       moved ".$quantity." soldiers! by ".$distance;	
				}
				else
				{
					$_SESSION['response']="moved ".$quantity." soldiers! by ".$distance;
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
				if($ambushSurvive==true)
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
	}
	unset($_SESSION['selectedRow']);
	unset($_SESSION['selectedCol']);
	unset($_SESSION['selectedTroops']);
}
function attack($srcRow,$srcCol,$destRow,$destCol,$quantity)
{
	global $conn,$player,$moveCostFood,$moveCostWater,$moveCostPower;
	/*resource validation for troops movement*/
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
	
	$sql="SELECT ttype FROM research WHERE playerid=$playerid;";
	$res=$conn->query($sql);
	$row=$res->fetch_assoc();
	$enemy="";	
	$maxLoss=100;
	$plunderBonus=0;
	$plunderPortion=5; //percent
	$supportTroops=0;
	$defenceTroops=0;
	$fortification;
	$battleResult=false;
	$winChance=0; //in percentage
	$troop=$row['ttype'];
	$troop=explode(":", $troop); //troops type as  type:level so w-warrior and s-stealth
	$troopType=$troop[0];  
	$troopLevel=$troop[1];
	$quantity=$originalQuantity;
	if($troopLevel==0)
	{
		$troopProbability=3; //troop probability is per unit chance of attack success
	}

	if($troopType=="s") //more plunder for stealth
	{
		$troopProbability=3;
		if($troopLevel==1)
		{
			$plunderBonus=5; 
		}
		else if($troopLevel==2)
		{
			$plunderBonus=10;
		}
		else if($troopLevel==3)
		{
			$plunderBonus=20;
		}
		else if($troopLevel==4)
		{
			$plunderBonus=40;
		}
		else if($troopLevel==5)
		{
			$plunderBonus=60;
		}
		else if($troopLevel==6)
		{
			$plunderBonus=80;
		}
		else if($troopLevel==7)
		{
			$troopProbability=4;
			$plunderBonus=90;
		}
		else if($troopLevel==8)
		{
			$troopProbability=5;
			$plunderBonus=100;
		}
	}

	else if($troopType="w") //fewer losses for warriors
	{
		$troopProbability=5;
		if($troopLevel==1)
		{
			$troopLevel=4;
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
	$sql="SELECT faction FROM player WHERE tek_emailid=$playerid;";
	$res=$conn->query($sql);
	$row=$res->fetch_assoc();
	$faction=$row['faction'];
	if($faction==1) //preserve
	{
		$sql="SELECT faction1,faction2 FROM research WHERE playerid=$playerid;";
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
		$quantity+=$supportTroops;
		$quantity*=$troopLevel;
		
		//defence calculations
		
		$sql="SELECT troops,fortification FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$defenceTroops+=$r1['troops']; //per troop defence probability
		$fortification=$r1['fortification'];
		$sql="SELECT SUM(quantity) FROM troops WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$defenceTroops+=$r1['troops'];
		$sql="SELECT defence FROM research WHERE playerid=$playerid;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$defPerk=$r1['defence'];
		if($defPerk==0)
		{
			$defenceProbability=0.5;
		}
		if($defPerk==1)
		{
			$defenceProbability=0.75;
		}
		if($defPerk==2)
		{
			$defenceProbability=1;
		}
		if($defPerk==3)
		{
			$defenceProbability=2;
			$sql="SELECT garrison FROM grid WHERE row=$destRow and col=$destCol;";
			$res=$conn->query($sql);
			$r1=$res->fetch_assoc();
			$defenceTroops+=$r1['garrison'];
		}
		$sql="SELECT playerid FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$enemy=$r1['playerid'];
		$sql="SELECT ttype FROM research WHERE playerid=$enemy;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$ettype=$r1['ttype'];
		$ettype=explode(":", $ettype);
		$eLevel=$ettype[1];
		$defenceTroops*=$eLevel;
		//sim battle
		$quantity/=$fortification;
		$winChance=($quantity*$troopProbability)-($defenceTroops*$defenceProbability);
		$result=rand(0,100);
		if($result>$winChance) //loss
		{
			$battleResult=false;
		}
		else //win
		{
			$battleResult=true;
		}
	}
	else if($faction==2) //exploit
	{
		//defence first
		$sql="SELECT playerid FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$enemy=$r1['playerid'];
		$sql="SELECT faction1 FROM research WHERE playerid=$enemy;";
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
		$sql="SELECT troops FROM grid WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		$defenceTroops=$r['troops'];
		$sql="SELECT SUM(quantity) FROM troops WHERE row=$destRow and col=$destCol;";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		$defenceTroops+=$r['quantity'];
		$sql="SELECT defence FROM research WHERE playerid=$playerid;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$defPerk=$r1['defence'];
		if($defPerk==0)
		{
			$defenceProbability=0.5;
		}
		if($defPerk==1)
		{
			$defenceProbability=0.75;
		}
		if($defPerk==2)
		{
			$defenceProbability=1;
		}
		if($defPerk==3)
		{
			$defenceProbability=2;
			$sql="SELECT garrison FROM grid WHERE row=$destRow and col=$destCol;";
			$res=$conn->query($sql);
			$r1=$res->fetch_assoc();
			$defenceTroops+=$r1['garrison'];
		}
		$defenceTroops+=$supportTroops;
		$sql="SELECT ttype FROM research WHERE playerid=$enemy;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$ettype=$r1['ttype'];
		$ettype=explode(":", $ettype);
		$eLevel=$ettype[1];
		$defenceTroops*=$eLevel;
		
		//attack

		$quantity*=$troopLevel;
	}
	
	//sim battle

	$quantity/=$fortification;
	$winChance=($quantity*$troopProbability)-($defenceTroops*$defenceProbability);
	$result=rand(0,100);
	if($result>$winChance) //loss
	{
		$battleResult=false;
	}
	else //win
	{
		$battleResult=true;
	}

	if($battleResult) //battle won
	{
			//loss calculation

		$quantity=$originalQuantity;
		if($fortification>$troopLevel) //low level troops attacking higher level settlements
		{
			$minLoss=20+(($fortification-$troopLevel)*10);
			$loss=floor($quantity*rand($maxLoss1,100)/100);
			if($maxLoss>0)
				$loss=floor($loss*$maxLoss/100);
			$quantity-=$loss;
			if($quantity<=0)
			{
				$quantity=1;
			}
		}
		else //high level troops attacking low level settlement
		{
			$maxLoss1=100-(($troopLevel-$fortification)*10);
			$loss=floor($quantity*rand(0,$maxLoss1)/100);
			if($maxLoss>0)
				$loss=floor($loss*$maxLoss/100);
			$quantity-=$loss;
			if($quantity<=0)
			{
				$quantity=1;
			}	
		}
		//removing resource gathering from the defeated slot
		$sql="UPDATE grid SET playerid=$playerid,type=NULL,troops=$quantity WHERE row=$destRow and col=$destCol;";
		if($conn->query($sql)===false)
			echo "<br>error: ".$conn->error;

		//plunder

		$sql="SELECT food,water,power,metal,wood FROM player WHERE row=$destRow and col=$destCol";
		$res=$conn->query($sql);
		$r=$res->fetch_assoc();
		$plunderFood=$plunderPortion*$r['food']/100;
		$plunderWater=$plunderPortion*$r['water']/100;
		$plunderPower=$plunderPortion*$r['power']/100;
		$plunderMetal=$plunderPortion*$r['metal']/100;
		$plunderWood=$plunderPortion*$r['wood']/100;
		$sql="UPDATE player SET food=food-$plunderWood,water=water-$plunderWater,power=power-$plunderPower
		      ,metal=metal-$plunderMetal,wood=wood-$plunderWood WHERE tek_emailid=$enemy";
		if($conn->query($sql)===false)
			echo "<br>error: ".$conn->error;
		$plunderFood+=$plunderFood*$plunderBonus/100;
		$plunderWater+=$plunderWater*$plunderBonus/100;
		$plunderPower+=$plunderPower*$plunderBonus/100;
		$plunderMetal+=$plunderMetal*$plunderBonus/100;
		$plunderWood+=$plunderWood*$plunderBonus/100;
		$sql="UPDATE player SET food=food+$plunderWood,water=water+$plunderWater,power=power+$plunderPower
		      ,metal=metal+$plunderMetal,wood=wood+$plunderWood WHERE tek_emailid=$playerid";
		if($conn->query($sql)===false)
			echo "<br>error: ".$conn->error;

		/*pending resoruce assignment*/

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
			playerid=$playerid;";
			if($conn->query($sql)===false)
				echo "<br>error: ".$conn->error;	
		}
	}
	unset($_SESSION['selectedRow']);
	unset($_SESSION['selectedCol']);
	unset($_SESSION['selectedTroops']);	
}
function condenseArray($arr) //removes duplicates and would reduce load on server as little as it already is..
{
	$ar=array();
	$ar[0]=$arr[0];
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
	global $conn,$playerid,$faction;
	$settleWoodCost=20;
	$settleMetalCost=30;
	$settlePowerCost=15;
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
	$roots= array();
	$root=$row.",".$col;
	$troopCount;
	$sql="SELECT row,col,fortification,root FROM grid WHERE (row=$row-1 or row=$row or row=$row+1) and (col=$col-1 or col=$col or col=$col+1);";
	$res=$conn->query($sql); //query to get all the neighbouring slots to the given slot.
	if($res->num_rows>0)
	{
		$i=0;
		while($ro=$res->fetch_assoc())
		{
			if($ro['fortification']>0 and !($ro['row']===$row and $ro['col']===$col))
			{
				$roots[$i]=$ro['root'];
			}
		}
	}
	$sql="SELECT quantity FROM troops WHERE row=$row and col=$col and playerid=$playerid;"; //find number of troops already stationed
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
		$sql="UPDATE grid SET occupied=$playerid,faction=$faction,fortification=1, root='$root',troops=$troopCount 
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
		$sql="UPDATE player SET total=total+1 WHERE playerid=$playerid;";
		if(!$conn->query($sql) === TRUE)
		{
			var_dump($sql);
			echo "<br>";
			echo "error: ".$conn->error."<br>";
		}
	}
	$_SESSION['response']="successfully settled.";
}
function scout($row,$col)
{
	global $conn,$playerid,$scoutCostFood,$scoutCostWater;
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
	$sql="SELECT occupied,fortification,troops,faction FROM grid WHERE row=$row and col=$col;"; //scouting enemy occupied slot
	$res=$conn->query($sql);
	$r=$res->fetch_assoc();
	$fortification=$r['fortification'];
	$occupied=$r['occupied'];
	$faction=$r['faction'];
	if($fortification>0)
	{
		$troops+=$r['troops'];
		$sql="SELECT SUM(quantity) FROM troops WHERE row=$row and col=$col;";
		$res=$conn->query($sql);
		$r1=$res->fetch_assoc();
		$troops+=$r1['SUM(quantity)'];
	}
	$sql="SELECT playerid,quantity FROM troops WHERE row=$row and col=$col;"; //scouting unoccupied slot
	$res=$conn->query($sql);
	if($res->num_rows>0)
	{
		while($row=$res->fetch_assoc())
		{
			$enemy=$row['playerid'];
			$otroops=$row['quantity']; //number of troops of one enemy occupant
			$sql1="SELECT open,ttype FROM research WHERE playerid=$enemy;";
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
		}
	}
	$output="Occupant : ".$occupied."<br>Fortification : ".$fortification."<br>Troops : ".$troops.
	    "<br>Faction : ".$faction;
	$_SESSION['response']=$output;
}
function createTroops($row,$col,$quantity)
{
	global $conn;
	$createTroopCostFoodBase="10";
	$createTroopCostWaterBase="13";
	$createTroopCostPowerBase="4";
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
	global $conn,$playerid;
	$fortifyWoodCost=array();
	$fortifyMetalCost=array();
	$fortifyPowerCost=array();
	$sql="SELECT fortification FROM grid WHERE row=$row and col=$col;";
	$res=$conn->query($sql);
	$level=$res['fortification'];
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
		$_SESSION['response']="fortified to level : ".$level+1;
}
if(isset($_POST['settle']))
{
	$row=testVar($_POST['row']);
	$col=testVar($_POST['col']);
	settle($row,$col);
	header("location:index.php");
}
if(isset($_POST['select_troops']))
{
	$row=testVar($_POST['row']);
	$col=testVar($_POST['col']);
	$row=$_POST['row'];
	$col=$_POST['col'];
	$_SESSION['selectedRow']=$row;
	$_SESSION['selectedCol']=$col;
	$quantity=$_POST['quantity'];
	$_SESSION['selectedTroops']=$quantity;
	header("location:index.php");
}
if(isset($_POST["scout"]))
{
	if(isset($_SESSION['selectedRow']) and !empty($_SESSION['selectedRow']))
	{
		unset($_SESSION['selectedRow']);
		unset($_SESSION['selectedCol']);
	}
	scout($_POST['row'],$_POST['col']);
	header("location:index.php");
}
if(isset($_POST['move']))
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
if(isset($_POST['attack']))
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
if(isset($_POST['create_troops']))
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
?>
