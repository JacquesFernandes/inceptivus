var grid=[];
var canvas;
var	ctx;
var slotSize=50;
var playArea=9;
var selectedTroops=0;
var selectedRow;
var selectedCol;
var food=1000;
var foodr=0;
var water=1000;
var waterr=0;
var wood=1000;
var woodr=0;
var metal=1000;
var metalr=0;
var power=750;
var powerr=0;
var max=0;
var moveCostFood=12;
var moveCostWater=16;
var moveCostPower=6;
var scoutCostFood=8;
var scoutCostWater=12;
var settleWoodCost=40;
var settleMetalCost=60;
var settlePowerCost=35;
var fortifyWoodCost=[40,70,100,130,160,190,200];
var fortifyMetalCost=[60,100,140,180,220,260,300];
var fortifyPowerCost=[35,70,90,110,140,160,180,200];
var createTroopCostFoodBase=10;
var createTroopCostWaterBase=13;
var createTroopCostPowerBase=4;
function assignGrid()
{
	for(var i=0;i<playArea;i++)
	{
		for(var j=0;j<playArea;j++)
		{
			if((i==3 && j==2) || (i==4 && j==2) || (i==3 && j==3))
			{
				grid[i][j]['color']="yellow";
				grid[i][j]['troops']=parseInt(2,10);
				grid[i][j]['fortification']=1;
			}
			else if((i==2 && j==6) || (i==3 && j==7) || (i==3 && j==8))
			{
				grid[i][j]['color']="red";
				grid[i][j]['troops']=parseInt(2,10);
				grid[i][j]['fortification']=1;
			}
			else if( i==5 && j==3)
			{
				grid[i][j]['color']="blue";
				grid[i][j]['troops']=parseInt(2,10);
				grid[i][j]['fortification']=1;
			}
			else
			{
				grid[i][j]['color']="white";
				grid[i][j]['troops']=parseInt(0,10);
				grid[i][j]['fortification']=0;
			}
		}
	}
}

function renderGrid()
{
	for(var i=0,y=0;i<playArea;i++,y+=slotSize)
	{
		for(var j=0,x=0;j<playArea;j++,x+=slotSize)
		{
			ctx.fillStyle=grid[i][j]['color'];
			ctx.strokeRect(x,y,slotSize,slotSize);
			ctx.fillRect(x,y,slotSize,slotSize);
		}
	}
}

function getCursorPosition(canvas , event) {
  	var rect = canvas.getBoundingClientRect();
    var x = event.clientX - rect.left;
    var y = event.clientY - rect.top;
    var res=x+","+y;
    var row=Math.floor(y/slotSize);
    var col=Math.floor(x/slotSize);
    var rc=row+","+col;
    response("rc",rc);
    return({x:x,y:y});
}

function updateResources()
{
	document.getElementById("food").innerHTML="food:"+food+"/"+foodr;
	document.getElementById("water").innerHTML="water"+water+"/"+waterr;
	document.getElementById("wood").innerHTML="wood"+wood+"/"+woodr;
	document.getElementById("metal").innerHTML="metal"+metal+"/"+metalr;
	document.getElementById("power").innerHTML="power"+power+"/"+powerr;
}

function show(actionmenu,quantityTB)
{
		var menu=[];
		menu['allyMenu']=document.getElementById("ally");
		menu['allyMenuS']=document.getElementById("sally");
		menu['enemyMenuS']=document.getElementById("sEnemy");
        menu['quantityTextBox']=document.getElementById("quantity");
		menu['enemyMenu']=document.getElementById("enemy");
        menu['playerMenu']=document.getElementById("player");
        menu['playerMenuS']=document.getElementById("splayer");
        menu['neutralMenuS']=document.getElementById("sneutral");
        menu['neutralMenu']=document.getElementById("neutral");
        for(var i in menu)
        {
        	if(i==actionmenu)
        	{
        		menu[i].style.visibility="visible";
        	}
        	else
        	{
        		menu[i].style.visibility="hidden";
        	}
        }
        if(quantityTB)
        	menu['quantityTextBox'].style.visibility="visible";
}

function hideAll()
{
		var menu=[];
		menu['allyMenu']=document.getElementById("ally");
		menu['allyMenuS']=document.getElementById("sally");
		menu['enemyMenuS']=document.getElementById("sEnemy");
        menu['quantityTextBox']=document.getElementById("quantity");
		menu['enemyMenu']=document.getElementById("enemy");
        menu['playerMenu']=document.getElementById("player");
        menu['playerMenuS']=document.getElementById("splayer");
        menu['neutralMenuS']=document.getElementById("sneutral");
        menu['neutralMenu']=document.getElementById("neutral");
        for(var i in menu)
        {
        	menu[i].style.visibility="hidden";
        }
}

function getActions(event)
{
	var coord=getCursorPosition(canvas,event);
	var cx=coord.x;
	var cy=coord.y;
	var i=Math.floor(cy/slotSize);
	var j=Math.floor(cx/slotSize);
	document.getElementById("row").value=i;
	document.getElementById("col").value=j;
	options=document.getElementById("bottom_action");
	if(grid[i][j]['color']=="blue")
	{
		scout();
		if(selectedTroops==0)
		{
			show("playerMenu",true);
		}
		else
		{
			show("playerMenuS",false);
		}
	}
	else if(grid[i][j]['color']=="yellow")
	{
		if(selectedTroops==0)
		{
			show("allyMenu",true);
		}
		else
		{
			show("allyMenuS",false);
		}
	}	
	else if(grid[i][j]['color']=="red")
	{
		//console.log(selectedTroops);
		if(selectedTroops==0)
		{
			show("enemyMenu",false);
		}
		else
		{
			show("enemyMenuS",false);
		}
	}
	else
	{
		if(grid[i][j]['troops']>0)
			scout();
		if(selectedTroops==0)
		{
			show("neutralMenu",true);
		}
		else
		{
			show("neutralMenuS",false);
		}
	}
}

function selectTroops() //pass max as 0 to remove select troops constraint
{
	hideAll();
	var row=document.getElementById("row").value;
	var col=document.getElementById("col").value;
	var quantity=document.getElementById("quantity").value;
	if(quantity==null)
		quantity=1;
	if(grid[row][col]['color']=="blue")
	{
		if(quantity>grid[row][col]['troops'])
		{
			response("bottom_hint","you don't have those many troops");
		}
		else if(quantity!=max && max!=0)
		{
			response("bottom_hint","you are supposed to select"+max+"soldier(s) please comply");
			alert("you are supposed to select only ");	
		}
		else
		{
			selectedTroops=quantity;
			selectedRow=row;
			selectedCol=col;
			document.getElementById("action").value="selectTroops";
			response("bottom_hint","selected "+selectedTroops+" troops");
		}
	}
	else if(grid[row][col]['color']=="yellow")
	{
		if(quantity>grid[row][col]['troops']+2)
		{
			response("bottom_hint","you don't have those many troops stationed here");
		}
		else if(quantity!=max && max!=0)
		{
			response("bottom_hint","you are supposed to select"+max+"soldier(s) please comply");
			alert("you are supposed to select only ");	
		}
		else
		{
			selectedTroops=quantity;
			selectedRow=row;
			selectedCol=col;
			response("bottom_hint","selected "+selectedTroops+" troops");
		}	
	}
}

function scout()
{
	hideAll();
    if(queryResource("food",scoutCostFood) && queryResource("water",scoutCostWater))
	{
		deductResource("food",scoutCostFood);
		deductResource("water",scoutCostWater);
		updateResources();
	}
	updateResources();
	document.getElementById("action").value="scout";
    var i=document.getElementById("row").value;
	var j=document.getElementById("col").value;
	var output;
	var srcRow=selectedRow;
	var srcCol=selectedCol;
	if(grid[i][j]['color']=="blue")
	{
		output="Occupant:player<br>troops:"+grid[i][j]['troops']+"<br>fortification:"+grid[i][j]['fortification'];
	}
	else if(grid[i][j]['color']=="yellow")
	{
		output="Occupant:ally<br>troops:"+grid[i][j]['troops']+"<br>fortification:"+grid[i][j]['fortification'];
	}
	else if(grid[i][j]['color']=="red")
	{
		output="Occupant:enemy<br>troops:"+grid[i][j]['troops']+"<br>fortification:"+grid[i][j]['fortification'];
		if(selectedRow!=null && selectedCol!=null && selectedTroops>0)
		{
			output=output+"<br>Win chance : "+simBattle(i,j)+"%";
		}
	}
	else
	{
		output="Occupant:unoccupied<br>troops:"+grid[i][j]['troops']+"<br>fortification:"+grid[i][j]['fortification'];	
	}
	response("bottom_hint",output);

}

function response(id,message)
{
	document.getElementById(id).innerHTML=message;
}

function createTroops()
{
	hideAll();
	var quantity=document.getElementById("quantity").value;
	var row=document.getElementById("row").value;
	var col=document.getElementById("col").value;
	if(quantity==null)
		quantity=1;
	foodCost=quantity*createTroopCostFoodBase;
	waterCost=quantity*createTroopCostWaterBase;
	powerCost=quantity*createTroopCostPowerBase;
	if(queryResource("food",foodCost) && queryResource("water",waterCost) && queryResource("power",powerCost))
	{
		deductResource("food",foodCost);
		deductResource("water",waterCost);
		deductResource("power",powerCost);
		updateResources();
	}
	if(quantity>max && max!=0 && max!=null)
	{
		response("prompt","you are supposed to select"+max+"soldier(s) please comply");
		alert("you are supposed to create only "+max+" troops");	
	}
	else
	{
		quantity=parseInt(quantity,10);
		grid[row][col]['troops']+=quantity;
		document.getElementById("action").value="createTroops";
		response("bottom_hint","created "+quantity+" troops");
	}	
}

function fortify()
{
	hideAll();
	document.getElementById("action").value="fortify";
	var row=document.getElementById("row").value;
	var col=document.getElementById("col").value;
	var lvl=grid[row][col]['fortification'];
	var woodCost=fortifyWoodCost[lvl-1];
	var metalCost=fortifyMetalCost[lvl-1];
	var powerCost=fortifyPowerCost[lvl-1];
	if(queryResource("wood",woodCost) && queryResource("metal",metalCost) && queryResource("power",powerCost))
	{
		deductResource("wood",woodCost);
		deductResource("metal",metalCost);
		deductResource("power",powerCost);
		updateResources();
	}
	if(grid[row][col]['fortification']<8)
	{
		grid[row][col]['fortification']+=1;	
	}
	else
	{
		response("bottom_hint","you already have maximum fortification");
	}
}

function settle()
{
	var row=document.getElementById("row").value;
	var col=document.getElementById("col").value;
	if(grid[row][col]['color']!="green")
	{
		return;
	}
	if(queryResource("wood",settleWoodCost) && queryResource("metal",settleMetalCost) && queryResource("power",settlePowerCost))
	{
		deductResource("wood",settleWoodCost);
		deductResource("metal",settleMetalCost);
		deductResource("power",settlePowerCost);
		updateResources();
	}
	grid[row][col]['color']="blue";
	renderGrid();
}

function queryResource(resource,value)
{
	if(resource=="food")
	{
		if(food>value)
			return true;
		else
			return false;
	}
	if(resource=="water")
	{
		if(water>value)
			return true;
		else
			return false;
	}
	if(resource=="wood")
	{
		if(wood>value)
			return true;
		else
			return false;
	}
	if(resource=="metal")
	{
		if(metal>value)
			return true;
		else
			return false;
	}
	if(resource=="power")
	{
		if(power>value)
			return true;
		else
			return false;
	}
}

function deductResource(resource,value)
{
	if(resource=="food")
	{
		if(food>value)
			food-=value;
	}
	if(resource=="water")
	{
		if(water>value)
			water-=value;
	}
	if(resource=="wood")
	{
		if(wood>value)
			wood-=value;
	}
	if(resource=="metal")
	{
		if(metal>value)
			metal-=value;
	}
	if(resource=="power")
	{
		if(power>value)
			power-=value;
	}
}

function start()
{
	/*pending*/
	window.location="../world-map/canvas1.html";
}

function attack()
{
	hideAll();
	document.getElementById("action").value="attack";
	var destRow=document.getElementById("row").value;
	var destCol=document.getElementById("col").value;
	var srcRow=selectedRow;
	var srcCol=selectedCol;
	var distance=Math.max(Math.abs(destRow-srcRow),Math.abs(destCol-srcCol));
	var foodCost=moveCostFood*distance;
	var waterCost=moveCostWater*distance;
	var powerCost=moveCostPower*distance;
	/*query resources*/
	if(queryResource("food",foodCost) && queryResource("water",waterCost) && queryResource("power",powerCost))
	{
		deductResource("food",foodCost);
		deductResource("water",waterCost);
		deductResource("power",powerCost);
		updateResources();
	}
	else
	{
		response("bottom_hint","not enough resources to move");
		return;
	}
	var winChance=simBattle(destRow,destCol);
	var num=Math.floor(Math.random()*100);
	var result=false;
	if(num>winChance)
		result=false;
	else
		result=true;
	if(result) //battle won
	{
		food+=50;
		water+=50;
		wood+=50;
		metal+=50
		power+=50;
		grid[srcRow][srcCol]['troops']-=selectedTroops;
		selectedTroops-=selectedTroops*winChance/100;
		grid[destRow][destCol]['troops']+=selectedTroops;
		grid[destRow][destCol]['color']="blue";
		response("bottom_hint","you won!");
	}
	else
	{
		grid[srcRow][srcCol]['troops']-=selectedTroops;
		response("bottom_hint","you lost!");
	}
	selectedRow=null;
	selectedCol=null;
	selectedTroops=0;
}

function find()
{
	alert("fuck yeah");
}

function simBattle(row,col)
{
	var destRow=row;
	var destCol=col;
	var srcRow=selectedRow;
	var srcCol=selectedCol;
	var troopProbability=3;
	var defTroopProbability=0.5;
	troops=parseInt(selectedTroops,10);
	troops/=grid[destRow][destCol]['fortification'];
	var attackProb=troops*troopProbability;
	var defProb=grid[destRow][destCol]['troops']*defTroopProbability;
	var winChance=attackProb-defProb;
	//console.log(attackProb+"   "+defProb);
	if(winChance>100)
		winChance=100;
	return winChance;
}

function move()
{
	hideAll();
	document.getElementById("action").value="move";
	var destRow=document.getElementById("row").value;
	var destCol=document.getElementById("col").value;
	var srcRow=selectedRow;
	var srcCol=selectedCol;
	selectedRow=null;
	selectedCol=null;
	if(srcCol==destCol && srcRow==destRow)
	{
		selectedTroops=0;
		response("bottom_hint","cannot move troops to the same tile.Move cancelled");
		return;
	}
	var distance=Math.max(Math.abs(destRow-srcRow),Math.abs(destCol-srcCol));
	if(grid[destRow][destCol]['color']=="blue" || grid[destRow][destCol]['color']=="yellow")
		distance/=2;
	var foodCost=moveCostFood*distance;
	var waterCost=moveCostWater*distance;
	var powerCost=moveCostPower*distance;
	/*query resources*/
	if(queryResource("food",foodCost) && queryResource("water",waterCost) && queryResource("power",powerCost))
	{
		deductResource("food",foodCost);
		deductResource("water",waterCost);
		deductResource("power",powerCost);
	}
	else
	{
		response("bottom_hint","not enough resources to move");
		return;
	}
	/*deduct resources*/
	selectedTroops=parseInt(selectedTroops,10);
	grid[srcRow][srcCol]['troops']-=selectedTroops;
	grid[destRow][destCol]['troops']+=selectedTroops;
	if(grid[destRow][destCol]['color']=="white")
		grid[destRow][destCol]['color']="green";
	renderGrid();
	//console.log(typeof(grid[destRow][destCol]['troops']));
	response("bottom_hint","moved "+selectedTroops+" by "+distance+" blocks<br>Cost<br>food:"+foodCost+"<br>water:"+waterCost+"<br>power:"+powerCost);
	selectedTroops=0;
}

function introduce()
{
	alert("Hi ,There ,welcome to the mega event!");
	var prompt="           INTRODUCTION             <br>"+
		  "All the colorful tiles you see is the area you are gonna play on<br>"+ 
		  "-your objective is to occupy as many of these tiles as possible<br>"+ 
		  "-blue tiles are the tiles on which you have settle you will start"+ 
		   "your game with one tile occupied<br>"+ 
		  "-yellow tiles belong to your allies,they are the ones who belong"+ 
		   "to the same faction as you do <br>"+ 
		  "-red tiles belong to all the players who are in the other faction"+ 
		   "your enemies<br>"+
		  "-the white tiles are unoccupied and you can send your troops to occupy them<br>"+
		  "-You will get to choose your faction once you actually start the game<br>"+ 
		   "but for now you could fiddle with the game to learn it or u could"+
		   "select the tutorial options on the right to let the game teach you<br>"+ 
		  "- YOU HAVEN'T STARTED THE GAME YET<br>"+
		  "- HAVE FUN"
	response("prompt",prompt);
}

function basics()
{
	var prompt="                        BASICS                     <br>"+
			   "At the top left are the resources you need to play the game<br>"+
			   "They look like   '1000/0'  so the number on the left of '/'' is the<bR>"+
			   "amount of a resource you have while the number on the right is <br>"+
			   "regeneration rate of those resources per minute<br>"+
			   "At the bottom right are the local map navigation buttons which you<br>"+
			   " could use to shift the local map to right,left,up or down by one <br>"+
			   " column/row"
	response("prompt",prompt);
}

function scouting()
{
	var prompt="     SCOUTING      <br>"+
			   "-scouting allows you to find out who is occupying a tile and more importantly"+
			   " the number of troops stationed in them<br>"+
			   "-you get all details of tiles occupied by you or unoccupied tiles on which"+
			   " you have stationed your troops<br>"+
			   "-click any tile not occupied by you and not having any of your troops,"+
			   " then click the scout option on the bottom right<br>"+
			   "**IF YOU CAN'T GET SCOUT OPTION YOU MIGHT BE DOING SOMETHING WRONG <br>**";
	response("prompt",prompt);
}

function selMove()
{
	
	var prompt="          SELECTING TROOPS and MOVING               <br>"+
			   "-To move or attack using your troops you have to first select them<br>"+
			   "-to select troops first click on a tile which has your troops stationed"+
			   " in it<br>"+
			   "-Enter the number of troops you want to select in the textbox in bottom left<br>"+
			   "-Then click on any tile except the tile you selected your troops from to get move option<br>"+
			   "-select one soldier in tile (5,3)<br>"+
		       "-Then click on tile (3,4) and move your troops there<br>"+
			   "-Notice the resource cost below?Also the green tiles have your troops stationed<br>"+
		       "-Now move your troops from (5,3) to (3,3)<br>"+
		       "-look at the resource cost for moving the same distance<br>The distance was<br>"+
			   "halved because your troops moved within friendly tiles which are connected<br>"+
			   "now move your soldier(s) in (3,3) to (3,4) and then start settle and attack tutorial<br>"+
			   "MOVE COST IS BASED ON THE NUMBER OF TILES YOUR SOLDIERS MOVE, NOT THE NUMBER OF SOLDIERS<br>";
	response("prompt",prompt);
}

function creAttack()
{
	var prompt=" SETTLING,CREATING TROOPS AND ATTACKING <br>"+
			   " I would be assuming at this point that you have moved atleast one of your soldiers<br>"+
			   " to an occupied (white)tile .If not then DO IT<br>"+
			   " Click on the green colored tile then select settle<br>"+
			   " Now select all troops in your newly settled tiles<br>"+
			   " click on an enemy(red) tile and click on scout option NOT ATTACK<br>"+
			   " You can see the probability of attack success at the bottom of the page<br>"+
			   " Clearly you don't have enough troops, so make atleast 20 troops and then try<br>"+
			   " to attack your enemy";
	response("prompt",prompt);
}

function research()
{
	var prompt=" you can upgrade various aspects of your civilisation in the research section";
	response("action",prompt);
}
window.onload=function renderLocal()
{
	hideAll();
	canvas=document.getElementById("canvas");
	ctx = canvas.getContext("2d");
	for(var i=0;i<9;i++)
	{
		grid[i]=[];
	}
	for(var i=0;i<9;i++)
	{
		for(var j=0;j<9;j++)
		{
			grid[i][j]=[];
		}
	}
	assignGrid();
	renderGrid();
	introduce();
	updateResources();
	canvas.setAttribute("onmousemove","getCursorPosition(canvas,event)");
	canvas.setAttribute("onclick","getActions(event)");
	document.getElementById("sTroops").setAttribute("onclick","selectTroops()");
	document.getElementById("sTroops1").setAttribute("onclick","selectTroops()");
	document.getElementById("sTroops2").setAttribute("onclick","selectTroops()");
	document.getElementById("cTroops").setAttribute("onclick","createTroops()");
	document.getElementById("scout").setAttribute("onclick","scout()");
	document.getElementById("scout1").setAttribute("onclick","scout()");
	document.getElementById("scout2").setAttribute("onclick","scout()");
	document.getElementById("scout3").setAttribute("onclick","scout()");
	document.getElementById("scout4").setAttribute("onclick","scout()");
	document.getElementById("scout5").setAttribute("onclick","scout()");
	document.getElementById("attack").setAttribute("onclick","attack()");
	document.getElementById("fortify").setAttribute("onclick","fortify()");
	document.getElementById("move").setAttribute("onclick","move()");
	document.getElementById("move1").setAttribute("onclick","move()");
	document.getElementById("move2").setAttribute("onclick","move()");
	document.getElementById("scouting").setAttribute("onclick","scouting()");	
	document.getElementById("selMove").setAttribute("onclick","selMove()");
	document.getElementById("creAttack").setAttribute("onclick","creAttack()");
	document.getElementById("settle").setAttribute("onclick","settle()");
	document.getElementById("research").setAttribute("onclick","research()");
	document.getElementById("basics").setAttribute("onclick","basics()");
}