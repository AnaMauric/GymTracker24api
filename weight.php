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
		if(!empty($_GET["username"]))
		{
			user_weight($_GET["username"]);		
		}
		else
		{
			http_response_code(400);				
		}
		break;

    case 'POST':
        dodaj_weight();
        break;

    default:
		http_response_code(405);		
		break;
}

mysqli_close($zbirka);	


function user_weight($userVzdevek)
{
	global $zbirka, $datum;
	$odgovor=array();

    if (user_obstaja($userVzdevek))
    {
        $poizvedba="SELECT date, weight FROM uWeight WHERE username='$userVzdevek' ORDER BY date";

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


function dodaj_weight()
{
	global $zbirka, $DEBUG, $datum;;
	$podatki = json_decode(file_get_contents("php://input"),true);

	if (isset($podatki["username"], $podatki["weight"]))
	{
		$userVzdevek = mysqli_escape_string($zbirka, $podatki["username"]);
		$userWeight = mysqli_escape_string($zbirka, $podatki["weight"]);
		
		if(user_obstaja($userVzdevek))
		{	
			$poizvedba = "INSERT INTO uWeight (username, weight, date) VALUES ('$userVzdevek', '$userWeight', '$datum')";
 
			if(mysqli_query($zbirka, $poizvedba))
			{
				http_response_code(201);
				$odgovor = URL_vira($userVzdevek);
				echo json_encode($odgovor);
			}
			else
			{
				http_response_code(500);
				if($DEBUG)
				{
					pripravi_odgovor_napaka(mysqli_error($zbirka));
				}
			}
		}
		else
		{
			http_response_code(404);
	
		}
	}  
    else{
        http_response_code(400);
    }

}


?>