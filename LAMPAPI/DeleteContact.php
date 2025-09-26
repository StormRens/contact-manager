<?php

  $inData = getRequestInfo();

  $userId = $inData["userId"] ?? null;
  $contactId  = $inData["contactId"] ?? ($inData["contactId"] ?? null);

  // Makes sure nothing is null
  if ($userId === null || $userId === "" || !is_numeric($userId)) {
    returnWithError("Invalid userId");
  }
  // if contactid is null or empty or is not a number then return with nothing
  if ($contactId === null || $contactId === "" || !is_numeric($contactId)) {
    returnWithError("Invalid contact id");
  }

  // connect to web server
  $conn = new mysqli("localhost", "TheBeast", "WeLoveCOP4331", "COP4331");
  if ($conn->connect_error)
  {
    returnWithError($conn->connect_error);
  }
  else
  {
    // delete only if the contact belongs to this user
    $stmt = $conn->prepare("DELETE FROM Contacts WHERE ID = ? AND UserID = ? LIMIT 1");
    $userIdInt = (int)$userId;
    $contactIdInt = (int)$contactId;

    $stmt->bind_param("ii", $contactIdInt, $userIdInt);
    $stmt->execute();

    // check if anything was deleted
    if ($stmt->affected_rows > 0) {
      // if we are in here it means that we deleted something, so now return it
      returnWithError("");
    } 
    else {
      // found nothing, return nothing
      returnWithError("No Records Found");
    }

    $stmt->close();
    $conn->close();
  }

  

  // Helper Functions

  function getRequestInfo()
  {
    return json_decode(file_get_contents('php://input'), true);
  }

  function sendResultInfoAsJson($obj)
  {
    header('Content-type: application/json');
    echo $obj;
    exit;
  }

  function returnWithError($err)
  {
    sendResultInfoAsJson(json_encode([
      "error"   => $err
    ]));
  }

?>
