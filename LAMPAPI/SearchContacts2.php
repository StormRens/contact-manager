<?php
// SearchContacts.php : Returns contacts owned by a specific user (Contacts table),
// matching a string Search term. It looks over FirstName, LastName, Email, or Phone

// Expected keys from front-end: Search, UserID
// Keys to be sent : Search, UserID, Results array containing (ContactID, FirstName, LastName, Email, Phone)


header("Content-Type: application/json");

// Get front-end request
$data   = getRequestInfo();
$userId = isset($data['UserID']) ? (int)$data['UserID'] : 0;
$search = isset($data['Search']) ? trim((string)$data['Search']) : "";

// Validate input (keep fixed response shape even on error)
if ($userId <= 0) {
    returnWithError($userId, $search, "Missing or invalid UserID");
}
if ($search === "") {
    returnWithError($userId, $search, "Missing Search term");
}

//DB connection values
$db_host = "localhost";
$db_user = "TheBeast";
$db_pass = "WeLoveCOP4331";
$db_name = "COP4331";

// $db_host = "localhost";
// $db_user = "root";
// $db_pass = "";
// $db_name = "cop4331";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    returnWithError($userId, $search, "DB connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4"); //standard charset

// Build wildcard term
$term = '%' . $search . '%';

// Assumed table/columns (as in AddContacts): Contacts(ID, UserID, FirstName, LastName, Email, Phone)
$sql = "SELECT ID, UserID, FirstName, LastName, Email, Phone
        FROM Contacts
        WHERE UserID = ? AND ( FirstName LIKE ? OR LastName  LIKE ? OR Email LIKE ? OR Phone LIKE ?)
        ORDER BY LastName ASC, FirstName ASC, ID ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    returnWithError($userId, $search, "Query prep failed");
}

$stmt->bind_param("issss", $userId, $term, $term, $term, $term);

// Execute and collect results
if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    returnWithError($userId, $search, "Search failed");
}

$result = $stmt->get_result();
$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        "ContactID" => (int)$row["ID"],
        "UserID"    => (int)$row["UserID"],
        "FirstName" => $row["FirstName"] ?? "",
        "LastName"  => $row["LastName"]  ?? "",
        "Email"     => $row["Email"]     ?? "",
        "Phone"     => $row["Phone"]     ?? ""
    ];
}

$stmt->close();
$conn->close();

//Return payload
if (empty($items)) {
    returnWithError($userId, $search, "No Records Found");
} else {
    returnWithResults($userId, $search, $items);
}


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

function returnWithError($userId, $search, $err)
{
    $payload = [
        "Search"  => (string)$search,
        "UserID"  => (int)$userId,
        "Results" => [],  //fixed key present even on error
        "Error"   => $err
    ];
    sendResultInfoAsJson(json_encode($payload));
}

function returnWithResults($userId, $search, $items)
{
    $payload = [
        "Search"  => (string)$search,
        "UserID"  => (int)$userId,
        "Results" => $items, //array of contacts with your based on search string
        "Error"   => ""
    ];
    sendResultInfoAsJson(json_encode($payload));
}

// Example expected JSON from front end:
// {
//   "UserID": 2,
//   "Search": "Joe"
// }

// Example that will to be send (from postman):
// {
//     "Search": "Joe",
//     "UserID": 2,
//     "Results": [
//         {
//             "ContactID": 3,
//             "UserID": 2,
//             "FirstName": "Joel",
//             "LastName": "Miller",
//             "Email": "testing@change.org",
//             "Phone": 555
//         }
//     ],
//     "Error": ""
// }