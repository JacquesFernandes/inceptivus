<?php
//This file serves as a temporary re-directing transition page...
//include "../db_access/db.php";
include "./player.php";
?>
<html>

<head>
	<link rel="stylesheet" type="text/css" href="./css/style.css">
	<title>Test page</title>
	<script src="action.js"></script>
</head>

<body>
	<div id="playerinfo" >
				<div id="leftward">					
					<span>Food : </span>
					<span>Water :</span>
					<span>Power :</span>
					<span>Metal :</span>
					<span>Wood : </span>
					
				</div>
				<span>
					<form action="resources.php" method="POST"> 
						<button type="submit" id="resource">Collect Resources</button>
					</form>
				</span>
		
	</div>
	<br><br>
	<div id="playgroup">
		<div id="localplay" class="playarea" align="center">
			<canvas id="canvas" width="450" height="450" style="border:1px solid #c3c3c3;">
							Your browser does not support the canvas element.
			</canvas>
		</div>
	</div>
	<hr>
	<div id="bottomgroup" border=1>
		<table id="bottomTable"> 
			<tr>
				<td>
					<div id="bottom_action" style="float:left">
						<form action="player.php" method="POST">
							<div>
								<input type="hidden" name="topLeft" id="topLeft">
								<input type="hidden" name="row" id="row">
								<input type="hidden" name="col" id="col">
								<input type="number" name="quantity" id="quantity">
							</div>
							<div id="action">
							</div>
							<div id="cost">
							</div>
						</form>
					</div>
				</td>
				<td>
					<div id="bottom_hint">
						<?php
							if(isset($_SESSION['response']))
							{
								echo $_SESSION['response'];
								unset($_SESSION['response']);
							}
						?>
					</div>
				</td>
				<td>
					<div style="float:right">
						<form method="POST" action="shift.php">
							<table id="nav_buttons" border=0 align="center">
								<tr>
									<td></td>
									<td align="center"><button type="submit" name="up">UP</button></td>
									<td></td>
								</tr>
								<tr>
									<td align="center"><button name="left">LEFT</button></td>
									<td align="center"><button name="world-map">WORLD MAP</button></td>
									<td align="center"><button name="right">RIGHT</button></td>
								</tr>
								<tr>
									<td></td>
									<td align="center"><button name="down">DOWN</button></td>
									<td></td>
								</tr>
							</table>
						</form>
					</div>
				</td>
			</tr>		
		</table>
	</div>
	<div id="contextMenu">
		<form id="ctxForm" action="player.php" method="post">
		</form>
	</div>
	<?php 
		//some request method
		$res=$_SESSION['coord'];
		$x=$res[0];
		$y=$res[1];
		echo "<script>document.getElementById('topLeft').value='$x,$y'</script>" 
	?>
</body>
</html>
=======
redirect("./index-grid.php");
?>
>>>>>>> e50f5d0ca4cf4639e6e5c2b50c4592f692d22cd8
