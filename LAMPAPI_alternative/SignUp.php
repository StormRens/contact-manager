<?php
//SignUp.php > Creates a new user in Users table (ID, firstName, lastName, Login, Password)


//Read request JSON
$data = getRequestInfo();

//Sanitize data (trim accidental spaces)
$firstName = isset($data['firstName']) ? trim($data['firstName']) : '';
$lastName  = isset($data['lastName'])  ? trim($data['lastName'])  : '';
$username  = isset($data['Login'])     ? trim($data['Login'])     : '';
$password  = isset($data['Password'])  ? trim($data['Password'])  : '';

//Don’t hit DB if missing fields
if ($firstName === '' || $lastName === '' || $username === '' || $password === '') {
    returnWithError("Missing required fields");
}

//DB values for connection
$db_host = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "api_testing";

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
$sql = "INSERT INTO Users (firstName, lastName, Login, Password) VALUES (?, ?, ?, ?)"; //DATABASE VALUES HERE
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
        "id" => 0,
        "firstName" => "",
        "lastName" => "",
        "error" => $err
    ]);
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($firstName, $lastName, $id)
{
    $payload = [
        "id" => $id,
        "firstName" => $firstName,
        "lastName" => $lastName,
        "error" => ""
    ];
    sendResultInfoAsJson(json_encode($payload));
}
