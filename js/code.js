const urlBase = 'http://www.cop4331-stormrens.com/LAMPAPI';
const extension = 'php';

let userId = 0;
let firstName = "";
let lastName = "";
let contactIdDelete = null;
let contactIdEdit = null;

//changing from theFrom.addEvent to editFrom.addEvent 
document.addEventListener('DOMContentLoaded', () => {

    const editForm = document.getElementById("editContactForm");

    if (editForm) {
        editForm.addEventListener("submit", editContact);
    } else {
        console.error("event never triggered");
    }
    
});

function doLogin()
{
	userId = 0;
	firstName = "";
	lastName = "";
	
	let login = document.getElementById("loginName").value;
	let password = document.getElementById("loginPassword").value;
//	var hash = md5( password );
	
	document.getElementById("loginResult").innerHTML = "";

	let tmp = {login:login,password:password};
//	var tmp = {login:login,password:hash};
	let jsonPayload = JSON.stringify( tmp );
	
	let url = urlBase + '/Login.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				let jsonObject = JSON.parse( xhr.responseText );
				userId = jsonObject.id;
		
				if( userId < 1 )
				{		
					document.getElementById("loginResult").innerHTML = "User/Password combination incorrect";
					return;
				}
		
				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

				saveCookie();
	
				window.location.href = "homepage.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		document.getElementById("loginResult").innerHTML = err.message;
	}

}

function addContact() {
	let firstName = document.getElementById("firstname").value.trim();
	let lastName = document.getElementById("lastname").value.trim();
	let email = document.getElementById("email").value.trim();
	let phone = document.getElementById("phone-number").value.trim();

	if(!firstName || !lastName || !email || phone.length != 10) {
		document.getElementById("add-contact").innerHTML = "Please enter all fields";
		return;
	}

	document.getElementById("add-contact").innerHTML = "";

	readCookie(); // need to set userId to the global variable

	let temp = {FirstName:firstName, LastName:lastName, UserID:userId, Phone:phone, Email:email};
	let jsonPayload = JSON.stringify(temp);
	let url = urlBase + '/AddContactsTwo.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

	try {
		xhr.onreadystatechange = function()
		{
			if(this.readyState == 4 && this.status == 200) {
				let jsonObject = JSON.parse(xhr.responseText);

				if(jsonObject.error) {
					document.getElementById("add-contact").innerHTML = "Something went wrong";
					return;
				}

				// need to make this look better
				document.getElementById("success").innerHTML = "User added, try searching for them!";
			}
		};
		xhr.send(jsonPayload);
		console.log(jsonPayload);
		
	} catch (error) {
		document.getElementById("add-contact").innerHTML = error.message;
		
	}

}

async function editContact(event) {
	console.log("In editcontact function");
    event.preventDefault();

    let firstName = document.querySelector("input[name='edit_firstname']").value.trim();
    let lastName = document.querySelector("input[name='edit_lastname']").value.trim();
    let email = document.querySelector("input[name='edit_email']").value.trim();
    let phone = document.querySelector("input[name='edit_number']").value.trim();

    if(!firstName || !lastName || !email || phone.length != 10) {
		// do something idk
        return;
    }

    readCookie();
		//capitalizing Id to ID
    let temp = {
        ContactID: contactIdEdit,
        UserID: userId,
        FirstName: firstName,
        LastName: lastName,
        Email: email,
        Phone: phone
    };
    let jsonPayload = JSON.stringify(temp);

    let url = urlBase + '/EditContacts.' + extension;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {
                let jsonObject = JSON.parse(xhr.responseText);

                if(jsonObject.error) {
					// do something find a place to put these errors
					console.log("Error in edit contact");
                    return;
                }

                hideEditPopup();
				// do something here also
				console.log(jsonObject);
				//window.location.href = "homepage.html"
            }
        }
        xhr.send(jsonPayload);
    } catch (error) {
        // do something here
		console.log("Error in edit contact")
    }
}

function register() {
	// get values from html
	firstName = document.getElementById("firstname").value.trim();
	lastName = document.getElementById("lastname").value.trim();
	let userName = document.getElementById("loginName").value.trim();
	let password = document.getElementById("loginPassword").value.trim();

	if(!firstName || !lastName || !userName || !password) {
		document.getElementById("registerResult").innerHTML = "Please enter all fields";
		return;
	}

	document.getElementById("registerResult").innerHTML = ""; // no error just yet

	// prepare to send data to backend
	let temp = {firstName:firstName, lastName:lastName, login:userName, password:password};
	let jsonPayload = JSON.stringify(temp);

	let url = urlBase + '/SignUp.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

	try {
		xhr.onreadystatechange = function()
		{
			// response is recieved and with good status code
			if(this.readyState == 4 && this.status == 200) {
				let jsonObject = JSON.parse(xhr.responseText);

				if(jsonObject.error) {
					document.getElementById("registerResult").innerHTML = "Username taken";
					return;
				}

				// get first and last name to use in cookie and id
				userId = jsonObject.id;
				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

				saveCookie();

				window.location.href = "homepage.html"
			}
		};
		xhr.send(jsonPayload);

		
	} catch (error) {
		document.getElementById("registerResult").innerHTML = error.message;
	}
	
}

function deleteContact(contactId) {
	readCookie();

	document.getElementById("deleteResult").innerHTML = "";

	let temp = {userId:userId, contactId: contactId};
	let jsonPayload = JSON.stringify(temp);

	let url = urlBase + '/DeleteContact.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

	try {
		xhr.onreadystatechange = function()
		{
			if(this.readyState == 4 && this.status == 200) {
				let jsonObject = JSON.parse(xhr.responseText);

				if(jsonObject.error) {
					document.getElementById("deleteResult").innerHTML = "Failed to delete user";
					return;
				}

				// figure out better way to show successfuly deleting contact
				window.location.href = "homepage.html";
			}
		}
		xhr.send(jsonPayload);
		
	} catch (error) {
		document.getElementById("deleteResult").innerHTML = error.message;
	}
}

function searchContact() {
  readCookie();
  let searchFirstName = document.getElementById("firstname").value.trim();
  let searchLastName = document.getElementById("lastname").value.trim();
  let searchTerm = searchFirstName + searchLastName;

  if (!searchTerm) {
    document.getElementById("searchResultError").innerHTML =
      "Please enter a first or last name to search";
    return;
  }

  document.getElementById("searchResultError").innerHTML = "";

  let temp = { search: searchTerm, userId: userId };
  let jsonPayload = JSON.stringify(temp);

  let url = urlBase + "/SearchContacts." + extension;

  let xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

  try {
    xhr.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        let jsonObject = JSON.parse(xhr.responseText);

        if (jsonObject.error) {
          document.getElementById("searchResultError").innerHTML =
            "No users found";
          return;
        }
        // if we get here, we found users
        const users = jsonObject.results;
        let contactsHtml = "";

        users.forEach((user) => {
          contactsHtml += `
      <div class="contact-card">
        <div class="contact-info">
          <div class="contact-name">
            <strong>${user.FirstName} ${user.LastName}</strong>
          </div>
          <div class="contact-details">
            First: ${user.FirstName}<br>
            Last: ${user.LastName}<br>
            Email: ${user.EmailAddress}<br>
            phone: ${displayNumber(user.PhoneNumber)}
          </div>
        </div>

        <div class="contact-actions">
      	  <button class="action-btn edit-btn" onclick="showEditPopup(${user.ID}, '${user.FirstName}', '${user.LastName}', '${user.EmailAddress}', '${user.PhoneNumber}')">Edit</button>
          <button class="action-btn delete-btn" onclick="showDeletePopup(${user.ID})">Delete</button>
        </div>
      </div>
			`;
        });

		document.getElementById("contacts-list").innerHTML = contactsHtml;
      }
    };
    xhr.send(jsonPayload);
  } catch (error) {
    document.getElementById("searchResultError").innerHTML = error.message;
  }
}

function displayNumber(phone) {
    return `(${phone.substring(0,3)})-${phone.substring(3,6)}-${phone.substring(6)}`;

}

function showDeletePopup(contactId) {
    contactIdDelete = contactId;
    document.getElementById("delete-contact").style.display = "block";
}

function hideDeletePopup() {
    contactIdDelete = null;
    document.getElementById("delete-contact").style.display = "none";
}

function confirmDeleteContact() {
    if (contactIdDelete !== null) {
        deleteContact(contactIdDelete);
        hideDeletePopup();
    }
}

function showEditPopup(id, firstName, lastName, email, phone) {
    contactIdEdit = id;
    document.querySelector("input[name='edit_firstname']").value = firstName;
    document.querySelector("input[name='edit_lastname']").value = lastName;
    document.querySelector("input[name='edit_email']").value = email;
	document.querySelector("input[name='edit_number']").value = phone;
    document.querySelector("input[name='contact_id']").value = id;
    document.getElementById("edit-contact").style.display = "block";
}

function hideEditPopup() {
    contactIdEdit = null;
    document.getElementById("edit-contact").style.display = "none";
}

function saveCookie()
{
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime()+(minutes*60*1000));	
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie()
{
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for(var i = 0; i < splits.length; i++) 
	{
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if( tokens[0] == "firstName" )
		{
			firstName = tokens[1];
		}
		else if( tokens[0] == "lastName" )
		{
			lastName = tokens[1];
		}
		else if( tokens[0] == "userId" )
		{
			userId = parseInt( tokens[1].trim() );
		}
	}
	
	if( userId < 0 )
	{
		window.location.href = "index.html";
	}
	else
	{
//		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
	}
}

function doLogout()
{
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}
