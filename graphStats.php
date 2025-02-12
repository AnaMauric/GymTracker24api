<?php

$DEBUG = true;	 					
include("orodja.php"); 					
$zbirka = dbConnect();		
$datum = date("Y-m-d"); 
 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


switch($_SERVER["REQUEST_METHOD"])		
{
	case 'GET':
		if(!empty($_GET["username"]) && !empty($_GET["exercise"]))
		{
			user_exercise_weight($_GET["username"], $_GET["exercise"]);		
		}
		else
		{
			http_response_code(400);				
		}
		break;

        default:
		http_response_code(405);		
		break;
}

mysqli_close($zbirka);	


function user_exercise_weight($userVzdevek, $exercise)
{
	global $zbirka;
	$odgovor=array();

    if (user_obstaja($userVzdevek))
    {
        $poizvedba="SELECT date, weight FROM exercise WHERE username='$userVzdevek' AND exercise_name='$exercise' ORDER BY date";

            $result=mysqli_query($zbirka, $poizvedba);

            while($vrstica=mysqli_fetch_assoc($result))
		    {
			    $odgovor[]=$vrstica;
		    }

            http_response_code(200);		
		    echo json_encode($odgovor);
    }
	
    else
	{
		http_response_code(404);	
	}  
	
}


?>