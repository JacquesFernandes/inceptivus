<?php
    if (isset($_GET['var_PHP_data'])) {
      session_start();

      //echo "hi";
    // echo $_GET['var_PHP_data'];
//echo "<script type='text/javascript'>alert('$message');</script>";
  $data=$_GET['var_PHP_data'];
    $_SESSION["battery"] = $data;

  /*
$servername = 'localhost';
$username = 'root';
$password = '';
$databasename = 'teknack';
 
$conn = mysqli_connect($servername, $username, $password, $databasename);
$tek_username='queeny';
$land='city';
if(!$conn)
{
die("connection failed".mysqli_connect_error());
}
else{//echo "connected successfully";
$res=mysqli_query($conn, "SELECT * FROM `resource` WHERE `tek_username`= '$tek_username' and land='$land';");
$row=mysqli_fetch_array($res);
  if(!$row)
  {
    $sql="INSERT INTO `resource`(`tek_username`, `battery`, `land`) VALUES ('$tek_username',$data,'$land')";
if(mysqli_query($conn,$sql)){
*/ 
echo " Hi! Your changes have been saved.";
/*
}
  }
  else
  {
   echo "Database entry exists.";
//header('location:index.php');
    }
  }
   // put your redirect html here!
  */
    }
     else {
    ?>


<html>
<head>
          <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script>
<script>

            $(document).ready(function() {
                               $('#sub').click(function() {

                   
                    $.ajax({
                        url: 'indexcity.php',
                        type: 'GET',
                         data: { var_PHP_data: tot }, 
                         success: function(data) {
                           alert(data);
                           window.location="../transitionToAlloc.php";
                           // $('#result').html(data)
                         }
                     });
                 });
                 $('#game').hide();
                                $("#hide").click(function(){
        $("p").hide(600);
        $('#game').show();
    });
             });

        </script>

<style>
div
{
 /*display: block; */

  width: 6em;
height: 4em;
 border:1px solid;
border-color: transparent;  
  text-align: center;
  font-weight: bold;
   
    
  background:rgba(255,255,255,0);
  vertical-align: top;
    float: left;




}
#sub {
width: 100px;
 height:25px;
  background: #39ded6;
  background-image: -webkit-linear-gradient(top, #39ded6, #02215e);
  background-image: -moz-linear-gradient(top, #39ded6, #02215e);
  background-image: -ms-linear-gradient(top, #39ded6, #02215e);
  background-image: -o-linear-gradient(top, #39ded6, #02215e);
  background-image: linear-gradient(to bottom, #39ded6, #02215e);
  -webkit-border-radius: 28;
  -moz-border-radius: 28;
  border-radius: 28px;
  font-family: Georgia;
  color: #ffffff;
  font-size: 15px;
  padding: 10px 20px 10px 20px;
  border: solid #1f218c 2px;
  text-decoration: none;
}

#sub:hover {
  background: #55cfbf;
  background-image: -webkit-linear-gradient(top, #55cfbf, #6124d4);
  background-image: -moz-linear-gradient(top, #55cfbf, #6124d4);
  background-image: -ms-linear-gradient(top, #55cfbf, #6124d4);
  background-image: -o-linear-gradient(top, #55cfbf, #6124d4);
  background-image: linear-gradient(to bottom, #55cfbf, #6124d4);
  text-decoration: none;
}

#click{

 font-family: Baskerville, "Baskerville old face", "Hoefler Text", Garamond, "Times New Roman", serif;
  color: #ffffff;
  font-size: 25;
  
  font-weight: bold;
  text-shadow: -5px 3px 3px rgba(50, 12, 200, 0.9);

}

section
{
  width:1200px; 
height:468px;
  background-image: url(city.jpg);
  background-repeat: no-repeat;
  background-position: center;
}

#div1
{
width: 900px;
height:100px;
}

#option
{
width: 1200px;
height:100px;

}
</style>
<script type="text/javascript" src="jquery-1.12.0.js"></script>
<script type="text/javascript">
function result(){

document.getElementById('s2').innerHTML = tot+"%" ;
document.getElementById('click').innerHTML = "Tries left= " + clicks ;


} 
</script>
<script>
var correct=0;var clicks=15;
	var incorrect=0;
  var bflag=0; var wflag=0;var wflag2=0; var tflag=0; var gflag=0; var hflag=0;var sflag2=0; var sflag=0; var tot=0;
function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
if(clicks===0){
  alert("sorry! You have exhausted your clicks. Click on Save changes.");
  $.ajax({
                        url: 'indexcity.php',
                        type: 'GET',
                         data: { var_PHP_data: tot }, 
                         success: function(data) {
                           alert(data);
                           // $('#result').html(data)
                         }
                     });
window.location="../transitionToAlloc.php";
return;
  }
    ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev,el) {
	
	
		clicks--;
    ev.preventDefault();
    var data = ev.dataTransfer.getData("text");
    
    var data2=data.toLowerCase();
    //alert(data2);
	//alert(el.id);
    ev.target.appendChild(document.getElementById(data));

    var content = el.id.toLowerCase();
	//alert(content);
	if (content==data2)
	{
		correct++;
    if (data2=="biogas")
      {if(bflag==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });
          bflag=1; tot=tot+20;
 // $(document).ready(function() {
 //$.ajax({
  //alert("hi");
                     //  url: 'http://localhost/w3.php',
                       // type: 'GET',
                       //  data: { var_PHP_data: tot },
                       //  success: function(data2) {
                          //   alert(data);
                           
                         //}
                  //   }); }

        }}
    else if (data2=="wind")
       {
if (data2==data){
        if(wflag==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });wflag=1; tot=tot+20;}}

else{
if(wflag2==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });wflag2=1; tot=tot+20;}
}

}
    else if (data2=="hydro")
 {
if (data2==data){
  if(hflag==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });
hflag=1; tot=tot+20;}}
else{

 if(hflag2==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });
hflag2=1; tot=tot+20;}}}






    else if (data2=="solar")
 {if (data2==data){
  if(sflag==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });
sflag=1; tot=tot+20;}}
else{

 if(sflag2==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });
sflag2=1; tot=tot+20;}}}
    else if (data2=="geo")
 {if(gflag==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });

      gflag=1; tot=tot+20;}}
   else if (data=="thermal")
 {

  if(tflag==0)
        {
$("#s2").animate({
           
            height: '-=12px',
        });
      tflag=1; tot=tot+20;}} 

      result();  //    alert("incorrect="+incorrect);

    
    
	}


	else {
    incorrect++;
    if(data2=="biogas")
    {
     if(bflag==1){
       correct--;
       bflag=0;
       tot=tot-20;

       $("#s2").animate({
           
            height: '+=12px',
        });
     }
    }
    else if (data2=="wind"){
      if (data2!=data){
      if(wflag2==1){
       correct--;
       wflag2=0;
       tot=tot-20;

       $("#s2").animate({
           
            height: '+=12px',
        });
}
     }

   else { if(wflag==1){
       correct--;
       wflag=0;
       tot=tot-20;

       $("#s2").animate({
           
            height: '+=12px',
        });
}
     }



    
    }
        else if (data2=="hydro"){
     if (data2==data){

     if(hflag==1){
       correct--;
       hflag=0;
       tot=tot-20;

        $("#s2").animate({
           
            height: '+=12px',
        });
     }}
      else{
        if(hflag2==1){
       correct--;
       hflag2=0;
       tot=tot-20;

        $("#s2").animate({
           
            height: '+=12px',
        });
     }
    }}
    else if (data2=="solar"){
      if (data2==data){
     if(sflag==1){
       correct--;
       sflag=0;
       tot=tot-20;

        $("#s2").animate({
           
            height: '+=12px',
        });
     }
    }
    else{
        if(sflag2==1){
       correct--;
       sflag2=0;
       tot=tot-20;

        $("#s2").animate({
           
            height: '+=12px',
        });
     }
    }}
   else if (data=="thermal"){
     if(tflag==1){
       correct--;
       tflag=0;
       tot=tot-20;
         

        $("#s2").animate({
           
            height: '+=12px',
        });
     }
    }
 

  }

   // alert("correct="+correct);
  result(); 
  //display(); //    alert("incorrect="+incorrect);
	 //end of else
} // end of function
  //document.write("<div> correct="+correct+"<br><br> incorrect="+incorrect+"</div>");


  // ev.target.append(document.getElementById(data));
//var parent = el.querySelector("div");
//  alert(parent.id);

//var correct = content.text().toLowerCase();
//	alert(correct);



</script>

</head>
<body style=" background-image: url(back.jpg);width: 1400px; height:4em; display: block; border: none; ">
<p id="hide"><img src="front.png">

</p>


<div id="game">
<section>

<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div4" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>


<div id="s1" style="background-image: url(batterygreen.png);height:60px;width:100px;position:relative;  border:3px solid #000;   vertical-align: bottom;">
<div  id="s2" style="background-image: url(battery.png);height:59px;width:100px;position:absolute;  border:2px #000; margin:0em;">
 </div>
 </div> 




<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="Wind" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="Solar" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>




<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div6" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>






<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="Solar" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div7" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>






<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="Thermal" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div3" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>






<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>

<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div8" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>

  
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="Hydro" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>
<div id="div5" ondrop="drop(event,this)" ondragover="allowDrop(event)"></div>



  






<br><br><br><br>
</section>
<div id="option"> 
<div id="div1" ondrop="drop(event,this)" ondragover="allowDrop(event)" style="background-color: rgba(250,240,230,0.3);">
<br>
  
  <img src="wind.png" draggable="true" ondragstart="drag(event)" id="wind" style="width: 6em;
  height: 5em;">
  <img src="solar.png" draggable="true" ondragstart="drag(event)" id="solar" style="width: 6em;
  height: 5em;">
 
  <img src="tidal.png" draggable="true" ondragstart="drag(event)" id="hydro" style="width: 6em;
  height: 5em;">
  <img src="thermal.png" draggable="true" ondragstart="drag(event)" id="thermal" style="width: 6em;
  height: 5em;">
   <img src="solar.png" draggable="true" ondragstart="drag(event)" id="SoLar" style="width: 6em;
  height: 5em;">

</div>
 
<div id="click" style="color:#fff;width: 9em; height: 2em;"> Tries left = 15 </div>
<br>
<div id="sub" style="margin-top:7; margin-left:40;"> Save</div> 


       </div>

 
    </div>    
</body>
</html>
<?php } ?>