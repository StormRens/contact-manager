<?php
//EditContacts.php -> Update a contact owned by a user 

//As of know, we are not implementing Email and Phone as part of the Contacts table,
//but I also included their implementation as comments in case we find its mandatory for the project
//To switch btw w/ Phone/Email (V_PE) , and without (X_PE)

//Expecting Front-End Request names to be : ContactId, UserId, FirstName, LastName, Error, (Email, Phone)commented

header("Content-Type: application/json");

//Read request
$data = getRequestInfo();

//Pull required keys and sanitize
$userId    = isset($data['UserId'])    ? (int)$data['UserId']    : 0;
$contactId = isset($data['ContactId']) ? (int)$data['ContactId'] : 0;

//Determine which fields to update
//array_key_exists ensures empty string ("") is a valid new value
$updatable = []; //array so we know which fields to update
if (array_key_exists('FirstName', $data)) $updatable['FirstName'] = trim((string)$data['FirstName']);
if (array_key_exists('LastName',  $data)) $updatable['LastName']  = trim((string)$data['LastName']);
//if (array_key_exists('Email',     $data)) $updatable['Email']     = trim((string)$data['Email']);//(V_PE)
//if (array_key_exists('Phone',     $data)) $updatable['Phone']     = trim((string)$data['Phone']);//(V_PE)

//If we don't have the required info for the specific contact, return w/ error
if ($userId <= 0 || $contactId <= 0) {
    returnWithError("Missing or invalid userId/contactId");
}
//Make sure there is an update to be made
if (count($updatable) === 0) {
    returnWithError("No fields to update (send at least one of firstName/lastName)");
    //returnWithError("No fields to update (send at least one of firstName/lastName/email/phone)");
}

//DB connection
$db_host = "localhost";
$db_user = "TheBeast";
$db_pass = "WeLoveCOP4331";
$db_name = "COP4331";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    returnWithError("DB connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");//standard char

//Because we might not change all of the fields all of the time, we need to build the sql string
//in parts to allow flexibility. Sql string command will depend on what are we updating.
//For that, make SET, PARAMETERS, and TYPE. (those will be converted in strings)

//Array to build sql string part1
$setParts = []; //set for sql
$params   = []; //parameters for sql
$types    = ""; //store the type of each ? (parameter place holder) 

//Fill each array given each 
foreach ($updatable as $key => $value) {
    $setParts[] = "$key = ?";               //used in SET for sql prepare
    $params[]   = $value;                   //parameters to use in sql bind_param
    $types     .= "s";                      //type of parameters (all string here)
}


//Concatenate the array by a separator string: ", " 
$setSql = implode(", ", $setParts); //e.g: FirstName = ?, LastName = ?,

//setSql will be used in SET of sql, IDs won't be changed
$sql = "UPDATE Contacts SET $setSql WHERE ID = ? AND UserID = ? LIMIT 1";

//sql prepare
$stmt = $conn->prepare($sql);
if (!$stmt) {
    returnWithError("Query prep failed");
}

//Add id/userId at the end of params and their type
$params[] = $contactId;
$params[] = $userId;
$types   .= "ii";

//parameters and type are ready, sql bind
$stmt->bind_param($types, ...$params);
if (!$stmt->execute()) {
    returnWithError("Update failed");
}
$stmt->close();

//Update sent to database, now read back from database to send to front-end
$readBack = $conn->prepare("SELECT ID AS ContactId, UserId, FirstName, LastName FROM Contacts WHERE ID = ? AND UserId = ? LIMIT 1");//(X_PE)
//$readBack = $conn->prepare("SELECT ID AS ContactId, UserId, FirstName, LastName, Email, Phone FROM Contacts WHERE ID = ? AND UserId = ? LIMIT 1");//(V_PE)
if (!$readBack) {
    returnWithError("Query prep (select) failed");
}
$readBack->bind_param("ii", $contactId, $userId);
$readBack->execute();
$result = $readBack->get_result();

//if result not found
if ($result === false || $result->num_rows === 0) {
    returnWithError("Contact not found for this user");
}

$row = $result->fetch_assoc();
$readBack->close();
$conn->close();

returnWithInfo($row);




//FUNCTIONS:
function getRequestInfo()
{
    // Front end sends JSON; decode as associative array
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    echo $obj;
    exit; // keep same behavior you’ve been using
}

function returnWithError($err)
{
    // Keep your style: return a consistent error payload
    $retValue = json_encode([
        "ContactId" => 0,
        "UserId"    => 0,
        "FirstName" => "",
        "LastName"  => "",
        //"Email"     => "",//(V_PE)
        //"Phone"     => "",//(V_PE)
        "Error"     => (string)$err
    ]);
    sendResultInfoAsJson($retValue);
}

function returnWithInfo(array $row)
{
    $payload = [
        "ContactId" => (int)$row["ContactId"],
        "UserId"    => (int)$row["UserId"],
        "FirstName" => (string)$row["FirstName"],
        "LastName"  => (string)$row["LastName"],
        //"Email"     => (string)$row["Email"],//(V_PE)
        //"Phone"     => (string)$row["Phone"],//(V_PE)
        "Error"     => ""
    ];
    sendResultInfoAsJson(json_encode($payload));
}


// Input JSON (any subset of fields):
// {
//   "UserId": 123,
//   "ContactId": 456,
//   "FirstName": "Ada",         
//   "LastName": "Lovelace",     
//   "Email": "ada@math.org",    //implemented as comment
//   "Phone": "555-123-4567"     //implemented as comment
// }
// Returns JSON:
// {
//   "ContactId": 456,
//   "UserId": 123,
//   "FirstName": "Ada",
//   "LastName": "Lovelace",
//   "Email": "ada@math.org",   //implemented as comment
//   "Phone": "555-123-4567",   //implemented as comment
//   "Error": ""
// }