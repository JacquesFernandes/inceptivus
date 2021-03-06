<?php
	session_start();
$row = $_SESSION["settleRow"];
$col = $_SESSION["settleCol"];

/* 
0-mud  -- +2 water
1-grass -- no bonus
2-sand -- +1 power ; -1 water
3-water -- no bonus
4-mountain -- +1 metal ; +1 wood
	
	*/
	
?>

<html>
<head>
	<title>Regeneration Allocation</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">    
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">  
	<link type="text/css" rel="stylesheet" href="../../maincss/mainstyle.css"> 
	<link type="text/css" rel="stylesheet" href="../css/regen.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
	<script src="./modify.js"></script>
</head>
<body>
	<div class="container">
		
		<div class="page-header">
			<h1>Regeneration Allocation</h1>
		</div>	

		<p>You have successfully settled. "Max Points" shows the total points you can allocate to the different resources.<br>
			You are allocating the regeneration points. This will get added to your current regeneration points. Choose wisely.<p>

		<span id="max" style="display: none"></span><br>

		<?php
			$var = $_SESSION['regen_points'];
			echo '<script type="text/javascript">document.getElementById("max").value='.$var.' </script>';
			unset($_SESSION['regen_points']);
		?>

		<p>Max points: <span id="disp">0</span></p>


		<div class="row">
			<div class="well innerpart col-md-4 col-md-offset-4">
				<table class="table">
					<tbody>
						<tr>
							<td class="col-md-1"><p>Food</p></td>
							<td class="col-md-1"><button class="btn btn-default" type="submit" name="food" onclick='down("fooddiv")'>Minus</button></td>
							<td class="col-md-1"><span id="fooddiv">0</span></td>
							<td class="col-md-1"><button class="btn btn-default" type="submit" name="food" onclick='up("fooddiv")'>Plus</button></td>
						</tr>
						<tr>
							<td><p>Water</p></td>
							<td><button class="btn btn-default" type="submit" name="water" onclick='down("waterdiv")'>Minus</button></td>
							<td><span id="waterdiv">0</span></td>
							<td><button class="btn btn-default" type="submit" name="water"  onclick='up("waterdiv")'>Plus</button> </td>
						</tr>
						<tr>
							<td><p>Power</p></td>
							<td><button class="btn btn-default" type="submit" name="power" onclick='down("powerdiv")'>Minus</button></td>
							<td><span id="powerdiv">0</span></td>
							<td><button class="btn btn-default" type="submit" name="power"  onclick='up("powerdiv")'>Plus</button> </td>
						</tr>
						<tr>
							<td><p>Metal</p></td>
							<td><button class="btn btn-default" type="submit" name="metal" onclick='down("metaldiv")'>Minus</button></td>
							<td><span id="metaldiv">0</span></td>
							<td><button class="btn btn-default" type="submit" name="metal"  onclick='up("metaldiv")'>Plus</button> </td>
						</tr>
						<tr>
							<td><p>Wood</p></td>
							<td><button class="btn btn-default" type="submit" name="wood" onclick='down("wooddiv")'>Minus</button></td>
							<td><span id="wooddiv">0</span></td>
							<td><button class="btn btn-default" type="submit" name="wood"  onclick='up("wooddiv")'>Plus</button> </td>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td><button class="btn btn-default" type="submit" name="resourceregen" onclick='sendback(<?php $var ?>)'>Confirm</button></td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>	
	
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
	
</body>
</html>
