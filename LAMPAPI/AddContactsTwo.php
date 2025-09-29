<?php
// AddContacts.php — Inserts a new contact owned by a specific user (Contacts table)

//Expected keys from front-end : FirstName, LastName, UserID, Phone, Email
//Keys to be send: FirstName, LastName, ContactID, UserID Error

//ID from the contacts table is written as ID, but using ContactID for clarity 

require_once 'cors.php';

header("Content-Type: application/json");

//Get front-end request
$data = getRequestInfo();

//Sanitize data (trim accidental spaces)
$userId    = isset($data['UserID'])    ? (int)$data['UserID']             : 0;
$firstName = isset($data['FirstName']) ? trim($data['FirstName']) : '';
$lastName  = isset($data['LastName'])  ? trim($data['LastName'])  : '';
$email     = isset($data['Email'])     ? trim($data['Email'])     : '';
$phone     = isset($data['Phone'])     ? trim($data['Phone'])     : '';

//Depends in what data we consider mandatory 
if ($userId <= 0 || $firstName === '' || $lastName === '') {
    returnWithError("Missing required fields (UserId, FirstName, LastName)");
}

//DB connection values
$db_host = "localhost";
$db_user = "TheBeast";
$db_pass = "WeLoveCOP4331";
$db_name = "COP4331";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    returnWithError("DB connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");//standard charset

//Insert contact
//Assumed expected table: Contacts(ID PK auto (omitted), UserID FK, FirstName, LastName, Email, Phone)
$sql = "INSERT INTO Contacts (UserID, FirstName, LastName, Email, Phone) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    returnWithError("Query prep failed");
}

$stmt->bind_param("issss", $userId, $firstName, $lastName, $email, $phone);

//Common to fail here due to foreign key, length
if (!$stmt->execute()) {
    returnWithError("Insert failed");
}

//Retrieve ContactID
$newContactId = $conn->insert_id;

//Close connections
$stmt->close();
$conn->close();

//Success, and send the data
returnWithInfo($newContactId, $userId, $firstName, $lastName, $email, $phone);


//Functions:
function getRequestInfo()
{
    // Front end sends JSON; decode as associative array.
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

function sendResultInfoAsJson($obj)
{
    echo $obj;
    exit;
}

function returnWithError($err)
{
    $retValue = json_encode([
        "ContactID" => 0,
        "UserID"    => 0,
        "FirstName" => "",
        "LastName"  => "",
        "Email"     => "",
        "Phone"     => "",
        "Error"     => $err
    ]);
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($contactId, $userId, $firstName, $lastName, $email, $phone)
{
    $payload = [
        "ContactID" => $contactId,
        "UserID"    => $userId,
        "FirstName" => $firstName,
        "LastName"  => $lastName,
        "Email"     => $email,
        "Phone"     => $phone,
        "Error"     => ""
    ];
    sendResultInfoAsJson(json_encode($payload));
}

// Expected JSON from front end (example):
// {
//   "UserID": 123,
//   "FirstName": "Ada",
//   "LastName": "Lace",
//   "Email": "ada@math.org",        // optional
//   "Phone": "555-123-4567"         // optional
// }
//
// Returns JSON:
// {
//   "ContactID": 456,
//   "UserID": 123,
//   "FirstName": "Ada",
//   "LastName": "Lovelace",
//   "Email": "ada@math.org",
//   "Phone": "555-123-4567",
//   "Error": ""
// }