<?php

//Expected keys from front-end : Login, Password
//Keys to be send: FirstName, LastName, ID, Error

//Set header to return JSON (deciding whether or not to just keep it inside the function)
//header("Content-Type: application/json");

//The front-end is sending JSON, taking the request and sanitizing the data
$data = getRequestInfo();


//Sanitize and validate data
$username = isset($data['Login']) ? trim($data['Login']) : '';
$password = isset($data['Password']) ? trim($data['Password']) : '';

//Do not request access to database if we do not have both username and password
if ($username === '' || $password === '')
{
    returnWithError( "Username or Password missing" );
}


//database variables
$db_host = "localhost";
$db_username = "TheBeast";
$db_password = "WeLoveCOP4331";
$db_name = "COP4331";


//connection to the database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

//checking connection, if unsuccessful send message
if ($conn->connect_error) 
{
    returnWithError( $conn->connect_error );
} 
else
{
	$conn->set_charset("utf8mb4");	//ensures it can also handle weird chars (extra)

    $sql = "SELECT ID,FirstName,LastName FROM Users WHERE Login=? AND Password =?";//DATABASE TABLES HERE
    $stmt = $conn->prepare($sql);

    //If the query fails, send a message
    if (!$stmt)
    {
        returnWithError( "Query prep failed" );
    }
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if( $row = $result->fetch_assoc()  )
	{
		returnWithInfo( $row['FirstName'], $row['LastName'], $row['ID'] );
	}
	else
	{
		returnWithError("No Records Found");
	}

	$stmt->close();
	$conn->close();
}
    

//Takes the input from the frontEnd
function getRequestInfo()
{
	return json_decode(file_get_contents('php://input'), true) ?: [];
}
//Sends the message with the appropriate formatting
function sendResultInfoAsJson( $obj )
{
	header('Content-type: application/json');
	echo $obj;
	exit; // always stop after responding
}

//Error message return
function returnWithError( $err )
{
	$retValue = [
        "ID" => 0,
        "FirstName" => "",
        "LastName" => "",
        "Error" => $err
    ];
    sendResultInfoAsJson(json_encode($retValue));
}
	
//Return with info (first, last and ID)
function returnWithInfo( $firstName, $lastName, $id )
{
	$payload = [
        "ID" => $id,
        "FirstName" => $firstName,
        "LastName" => $lastName,
        "Error" => ""
    ];
    sendResultInfoAsJson(json_encode($payload));
}

