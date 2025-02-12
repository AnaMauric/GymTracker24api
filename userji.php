<?php

$DEBUG = true;						
include("orodja.php"); 					
$zbirka = dbConnect();

header('Content-Type: application/json');	
header('Access-Control-Allow-Origin: *');  
header('Access-Control-Allow-Headers: Content-Type');  

 
switch($_SERVER["REQUEST_METHOD"])		
{
	case 'GET':
		if(!empty($_GET["userVzdevek"]))
		{
			pridobi_user($_GET["userVzdevek"]);		
		}
		else
		{
			http_response_code(400);				
		}
		break;
 


	case 'POST':
		dodaj_user();
		break;
 


	case 'PUT':
		posodobi_user();
		break;



	case 'DELETE':
		izbrisi_user();
		break;

 
	default:
		http_response_code(405);		
		break;
}
 
mysqli_close($zbirka);					



function pridobi_user($userVzdevek)
{
	global $zbirka; 
	$odgovor=array();

    if (user_obstaja($userVzdevek))
    {
        $poizvedba="SELECT birthday, weight FROM user WHERE username='$userVzdevek'";

        $result=mysqli_query($zbirka, $poizvedba);

        while($vrstica=mysqli_fetch_assoc($result))
		{
			$odgovor=$vrstica;
		}

        http_response_code(200);		
		echo json_encode($odgovor);
    }
	
    else
	{
		http_response_code(404);	
	}  


}
 


function dodaj_user()
{
	global $zbirka, $DEBUG;
	$podatki = json_decode(file_get_contents("php://input"), true);

	if (isset($podatki["username"], $podatki["password"])) {
		$userVzdevek = mysqli_real_escape_string($zbirka, $podatki["username"]);
		$userPassword = mysqli_real_escape_string($zbirka, $podatki["password"]);

		if (!user_obstaja($userVzdevek)) {	
			$userPasswordHash = password_hash($userPassword, PASSWORD_DEFAULT);
			$poizvedba = "INSERT INTO user (username, password) VALUES ('$userVzdevek', '$userPasswordHash')";

			if (mysqli_query($zbirka, $poizvedba)) {
				http_response_code(201);
				$odgovor = URL_vira($userVzdevek);
				echo json_encode($odgovor);
			} else {
				http_response_code(500);
				if ($DEBUG) {
					pripravi_odgovor_napaka(mysqli_error($zbirka));
				}
			}
		} else {
			$poizvedba = "SELECT password FROM user WHERE username='$userVzdevek'";
			$rezultat = mysqli_query($zbirka, $poizvedba);

			if ($rezultat && mysqli_num_rows($rezultat) > 0) {
				$vrstica = mysqli_fetch_assoc($rezultat);
				$hashGeslo = $vrstica["password"];

				if (password_verify($userPassword, $hashGeslo)) {
					http_response_code(200);
				} else {
					http_response_code(401);
				}
			} else {
				http_response_code(404);
			}
		}
	}
}
 



function posodobi_user()
{
	global $zbirka, $DEBUG;
	$podatki = json_decode(file_get_contents("php://input"),true);

	if (isset($podatki["username"], $podatki["password"], $podatki["birthday"], $podatki["weight"]))
	{
		$userVzdevek = mysqli_escape_string($zbirka, $podatki["username"]);
		$userPassword = mysqli_escape_string($zbirka, $podatki["password"]);
		$birthday = mysqli_escape_string($zbirka, $podatki["birthday"]);
		$weight = mysqli_escape_string($zbirka, $podatki["weight"]);	

		$userPassword= password_hash($userPassword, PASSWORD_DEFAULT);

		if(user_obstaja($userVzdevek)){
			$poizvedba = "UPDATE user SET password = '$userPassword', birthday = '$birthday', weight = '$weight' WHERE username = '$userVzdevek'";
			
			if(mysqli_query($zbirka, $poizvedba))
			{
				http_response_code(200);	
			} else {
				http_response_code(500);
				if($DEBUG)
				{
					pripravi_odgovor_napaka(mysqli_error($zbirka));
				}
			}
	
		}else {
			http_response_code(404);	
		}
	} else {
		http_response_code(400);	
	}
}



function izbrisi_user()
{
	global $zbirka, $DEBUG;
	$podatki = json_decode(file_get_contents("php://input"),true);

	if (isset($podatki["username"]))
	{
		$userVzdevek = mysqli_escape_string($zbirka, $podatki["username"]);

		if(user_obstaja($userVzdevek))
		{
			$poizvedba = "DELETE FROM user WHERE username='$userVzdevek'";
			if(mysqli_query($zbirka, $poizvedba))
			{
				http_response_code(200);
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
}




 
?>