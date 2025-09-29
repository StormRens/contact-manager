<?php
	require_once 'cors.php';
	#this is the login page
	$inData = getRequestInfo();
	
	$id = 0;
	$firstName = "";
	$lastName = "";

	#Get the connection to the database.
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331"); 	
	if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );	#if there is a connection error return it
	}
	else
	{
		#prepare and bind the sql statement
		$stmt = $conn->prepare("SELECT ID,firstName,lastName FROM Users WHERE Login=? AND Password =?");
		$stmt->bind_param("ss", $inData["login"], $inData["password"]);
		$stmt->execute();
		$result = $stmt->get_result();

		#If there is a row that matches the login and password then return the first name, last name, and id
		if( $row = $result->fetch_assoc()  )
		{
			returnWithInfo( $row['firstName'], $row['lastName'], $row['ID'] );
		}
		#otherwise return no records found
		else 
		{
			returnWithError("No Records Found");
		}

		$stmt->close();
		$conn->close();
	}
	
	#Get the input data from the user
	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	#send the result back as a json object
	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	#return with an error message
	function returnWithError( $err )
	{
		$retValue = '{"id":0,"firstName":"","lastName":"","error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	#return with the first name, last name, and id
	function returnWithInfo( $firstName, $lastName, $id )
	{
		$retValue = '{"id":' . $id . ',"firstName":"' . $firstName . '","lastName":"' . $lastName . '","error":""}';
		sendResultInfoAsJson( $retValue );
	}
	
?>
