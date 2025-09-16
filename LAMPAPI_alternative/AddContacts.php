<?php
// AddContacts.php — Inserts a new contact owned by a specific user (Contacts table)

header("Content-Type: application/json");

//Get front-end request
$data = getRequestInfo();

//Sanitize data (trim accidental spaces)
$userId    = isset($data['userId'])    ? (int)$data['userId']             : 0;
$firstName = isset($data['firstName']) ? trim($data['firstName']) : '';
$lastName  = isset($data['lastName'])  ? trim($data['lastName'])  : '';
$email     = isset($data['email'])     ? trim($data['email'])     : '';
$phone     = isset($data['phone'])     ? trim($data['phone'])     : '';

//Depends in what data we consider mandatory 
if ($userId <= 0 || $firstName === '' || $lastName === '') {
    returnWithError("Missing required fields (userId, firstName, lastName)");
}

//DB connection values
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "api_testing";

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
        "contactId" => 0,
        "userId"    => 0,
        "firstName" => "",
        "lastName"  => "",
        "email"     => "",
        "phone"     => "",
        "error"     => $err
    ]);
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($contactId, $userId, $firstName, $lastName, $email, $phone)
{
    $payload = [
        "contactId" => $contactId,
        "userId"    => $userId,
        "firstName" => $firstName,
        "lastName"  => $lastName,
        "email"     => $email,
        "phone"     => $phone,
        "error"     => ""
    ];
    sendResultInfoAsJson(json_encode($payload));
}

// Expected JSON from front end (example):
// {
//   "userId": 123,
//   "firstName": "Ada",
//   "lastName": "Lovelace",
//   "email": "ada@math.org",        // optional
//   "phone": "555-123-4567"         // optional
// }
//
// Returns JSON:
// {
//   "contactId": 456,
//   "userId": 123,
//   "firstName": "Ada",
//   "lastName": "Lovelace",
//   "email": "ada@math.org",
//   "phone": "555-123-4567",
//   "error": ""
// }