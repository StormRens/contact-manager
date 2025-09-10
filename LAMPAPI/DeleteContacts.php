<?php
    $inData = getRequestInfo();

    #get the userId, firstName, and lastName from the input data
    $userId = $inData["userId"];
    $firstName = $inData["firstName"];
    $lastName = $inData["lastName"];

    #Create our connection to the database, return an error if the connection fails
    $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
    if($conn->connect_error)
    {
        returnWithError( $conn->connect_error );
    }

    #If the connection is successful, delete the contact from the database
    else
    {
        $stmt = $conn->prepare("DELETE FROM Contacts WHERE FirstName = ? AND LastName = ? AND UserID = ?");
        $stmt->bind_param("ssi", $firstName, $lastName, $userId);
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