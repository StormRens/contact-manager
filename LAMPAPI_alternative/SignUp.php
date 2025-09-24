<?php
//SignUp.php > Creates a new user in Users table (ID, firstName, lastName, Login, Password)

//Expected keys from front-end : FirstName, LastName, Login, Password
//Keys to be send: FirstName, LastName, ID, Error


//Read request JSON
$data = getRequestInfo();

//Sanitize data (trim accidental spaces)
$firstName = isset($data['FirstName']) ? trim($data['FirstName']) : '';
$lastName  = isset($data['LastName'])  ? trim($data['LastName'])  : '';
$username  = isset($data['Login'])     ? trim($data['Login'])     : '';
$password  = isset($data['Password'])  ? trim($data['Password'])  : '';

//Don’t hit DB if missing fields
if ($firstName === '' || $lastName === '' || $username === '' || $password === '') {
    returnWithError("Missing required fields");
}

//DB values for connection
$db_host = "localhost";
$db_username = "TheBeast";
$db_password = "WeLoveCOP4331";
$db_name = "COP4331";

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    returnWithError("DB connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4"); //standard charset

//1) Check if Login (username) already exists
$sql = "SELECT ID FROM Users WHERE Login = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    returnWithError("Query prep failed (username check)");
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// If rows are found, username already exists
if ($result && $result->num_rows > 0) {
    returnWithError("Username already exists");
}

//2) Else, create the user
$sql = "INSERT INTO Users (FirstName, LastName, Login, Password) VALUES (?, ?, ?, ?)"; //DATABASE VALUES HERE
$stmt = $conn->prepare($sql);
if (!$sql) {
    returnWithError("Query prep failed (insert)");
}
$stmt->bind_param("ssss", $firstName, $lastName, $username, $password);
$inserted = $stmt->execute();

if (!$inserted) {
    returnWithError("Insert failed");
}

$newId = $conn->insert_id;
$stmt->close();
$conn->close();

returnWithInfo($firstName, $lastName, $newId);

//Functions:
function getRequestInfo()
{
    //Front end sends JSON, decode as associative array
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

function sendResultInfoAsJson($obj)
{
    header('Content-Type: application/json');
    echo $obj;
    exit;
}

function returnWithError($err)
{
    $retValue = json_encode([
        "ID" => 0,
        "FirstName" => "",
        "LastName" => "",
        "Error" => $err
    ]);
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $id)
{
    $payload = [
        "ID" => $id,
        "FirstName" => $firstName,
        "LastName" => $lastName,
        "Error" => ""
    ];
    sendResultInfoAsJson(json_encode($payload));
}
