<?php

$DEBUG = true;							
include("orodja.php"); 					
$zbirka = dbConnect();		
$datum = date("Y-m-d"); 
 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');  


 
switch($_SERVER["REQUEST_METHOD"])		
{
	case 'GET':
		if(!empty($_GET["username"]))
		{
			user_exercise($_GET["username"]);		
		}
		else
		{
			http_response_code(400);				
		}
		break;
 

	case 'POST':
		dodaj_ali_posodobi_exercise();		
		break;


	case 'DELETE':
		izbrisi_exercise();		
		break;

 
	default:
		http_response_code(405);		
		break;
}
 
mysqli_close($zbirka);					



function user_exercise($userVzdevek)
{
	global $zbirka;
	$odgovor=array();

    if (user_obstaja($userVzdevek))
    {
        $poizvedba="SELECT exercise_name, date, weight, sets, reps FROM exercise WHERE username='$userVzdevek' ORDER BY date";
            
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



function dodaj_ali_posodobi_exercise()
{
    global $zbirka, $DEBUG, $datum;
    
    $podatki = json_decode(file_get_contents('php://input'), true);

    if (isset($podatki["username"], $podatki["exercise_name"], $podatki["weight"], $podatki["sets"], $podatki["reps"]))
    {
        
        $userVzdevek = mysqli_escape_string($zbirka, $podatki["username"]);
        $exercise_name = mysqli_escape_string($zbirka, $podatki["exercise_name"]);
        $weight = mysqli_escape_string($zbirka, $podatki["weight"]);
        $sets = mysqli_escape_string($zbirka, $podatki["sets"]);
        $reps = mysqli_escape_string($zbirka, $podatki["reps"]);
        
        if (user_obstaja($userVzdevek))
        {
            $preveriPoizvedba = "SELECT 1 FROM exercise 
                                 WHERE username = '$userVzdevek' 
                                 AND exercise_name = '$exercise_name' 
                                 AND date = '$datum' 
                                 LIMIT 1";
            
            $rezultat = mysqli_query($zbirka, $preveriPoizvedba);

            if (mysqli_num_rows($rezultat) > 0)
            {
                $posodobiPoizvedba = "UPDATE exercise 
                                      SET weight = '$weight', sets = '$sets', reps = '$reps' 
                                      WHERE username = '$userVzdevek' 
                                      AND exercise_name = '$exercise_name' 
                                      AND date = '$datum'";
                
                if (mysqli_query($zbirka, $posodobiPoizvedba))
                {
                    http_response_code(200); 
                }
                else
                {
                    http_response_code(500); 
                    if ($DEBUG)
                    {
                        pripravi_odgovor_napaka(mysqli_error($zbirka));
                    }
                }
            }
            else
            {
                $dodajPoizvedba = "INSERT INTO exercise (username, exercise_name, date, weight, sets, reps) 
                                   VALUES ('$userVzdevek', '$exercise_name', '$datum', '$weight', '$sets', '$reps')";

                if (mysqli_query($zbirka, $dodajPoizvedba))
                {
                    http_response_code(201); 
                }
                else
                {
                    http_response_code(500); 
                    if ($DEBUG)
                    {
                        pripravi_odgovor_napaka(mysqli_error($zbirka));
                    }
                }
            }
        }
        else
        {
            http_response_code(409); 
            pripravi_odgovor_napaka("User doesn't exist!");
        }
    }
    else
    {
        http_response_code(400); 
        pripravi_odgovor_napaka("Missing data");
    }
}



function izbrisi_exercise()
{
    global $zbirka, $DEBUG;

    $podatki = json_decode(file_get_contents('php://input'), true);

    if (isset($podatki["username"], $podatki["exercise_name"], $podatki["date"]))
    {
        if (user_obstaja($podatki["username"]))
        {
            $userVzdevek = mysqli_escape_string($zbirka, $podatki["username"]);
            $exercise_name = mysqli_escape_string($zbirka, $podatki["exercise_name"]);
            $date = mysqli_escape_string($zbirka, $podatki["date"]);

            $brisanjePoizvedba = "DELETE FROM exercise 
                                  WHERE username = '$userVzdevek' 
                                  AND exercise_name = '$exercise_name' 
                                  AND date = '$date'";
            
            if (mysqli_query($zbirka, $brisanjePoizvedba))
            {
                if (mysqli_affected_rows($zbirka) > 0)
                {
                    http_response_code(204); 
                }
                else
                {
                    http_response_code(404); 
                }
            }
            else
            {
                http_response_code(500); 
                if ($DEBUG)
                {
                    pripravi_odgovor_napaka(mysqli_error($zbirka));
                }
            }
        }
        else
        {
            http_response_code(409); 
            pripravi_odgovor_napaka("User doesn't exist!");
        }
    }
    else
    {
        http_response_code(400); 
        pripravi_odgovor_napaka("Missing data");
    }
}

 
?>