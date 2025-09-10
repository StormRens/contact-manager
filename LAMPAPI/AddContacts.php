<?php
	$inData = getRequestInfo();
	
    #Set variables to the input data
	$firstName = $inData["firstName"];
    $lastName = $inData["lastName"];
    $phoneNumber = $inData["phoneNumber"];
    $emailAddress = $inData["emailAddress"];
    $userId = $inData["userId"];


    #Create our connection to the database, return an error if the connection fails
	$conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
	if ($conn->connect_error)
	{
			returnWithError( $conn->connect_error );
	}

    #If the connection is successful, insert the contact into the database
	else
	{
			$stmt = $conn->prepare("INSERT into Contacts (FirstName,LastName,PhoneNumber,EmailAddress, UserID) VALUES(?,?,?,?,?)");
			$stmt->bind_param("ssssi", $firstName, $lastName, $phoneNumber, $emailAddress, $userId);
			$stmt->execute();
			$stmt->close();
			$conn->close();
			returnWithError("");
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
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}

?>