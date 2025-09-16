<?php
  $inData = getRequestInfo();

  // Makes sure the search that the user input is valid and not null
  $search = trim($inData["search"] ?? "");
  // gets which user is sending this data
  $userId = $inData["userId"] ?? null;

  // Makes sure nothing is null
  if ($userId === null || $userId === "" || !is_numeric($userId)) {
    returnWithError("Invalid userId");
  }

  // if search is null then return with nothing
  if ($search === "") {
    returnWithError("No Records Found");
  }

  // connect to web server
  $conn = new mysqli("localhost", "root", "", "api_testing");
  if ($conn->connect_error)
  {
    returnWithError($conn->connect_error);
  }
  else
  {
    // this enables it to search anytihng that has the term we are looking for
    $term = "%" . $search . "%";

    $stmt = $conn->prepare("SELECT ID, FirstName, LastName, PhoneNumber, EmailAddress, UserID FROM contacts WHERE (FirstName LIKE ? OR LastName LIKE ?) AND UserID = ?");
    $userIdInt = (int)$userId;           
    $stmt->bind_param("ssi", $term, $term, $userIdInt);
    $stmt->execute();

    $result = $stmt->get_result();

    // Make an array
    $items = [];
    while ($row = $result->fetch_assoc())
    {
      $items[] = [
        "FirstName"    => $row["FirstName"],
        "LastName"     => $row["LastName"],
        "PhoneNumber"  => $row["PhoneNumber"],
        "EmailAddress" => $row["EmailAddress"],
        "UserID"       => (int)$row["UserID"],
        "ID"           => (int)$row["ID"]
      ];
    }

    $stmt->close();
    $conn->close();

    if (empty($items)) {
      returnWithError("No Records Found");
    } else {
      returnWithResults($items);
    }
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
    sendResultInfoAsJson(json_encode(["results" => [], "error"   => $err]));
  }

  function returnWithResults($items)
  {
    sendResultInfoAsJson(json_encode(["results" => $items, "error"   => ""]));
  }

