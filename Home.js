//*note to self
//- set up hyperlinks for 'sign up' button
//- set up onclick action for 'login' button

var url = 'https://www.mycontactsucf.com/mainPHP.php';

function login()
{
	//gets the value inside username textbox
	var user = document.getElementById("user").value;
	
	//gets the value inside password textbox
	var pass = document.getElementById("pass").value;
	
	if((user == "") || (pass == ""))
	{
	    document.getElementById("login_failed").innerHTML = "All fields must be filled in!";
	    document.getElementById("pass").value = "";
	    return;
	}
	
	//create javascript object to convert to JSON string
	var jsonObj = {};
	jsonObj["apiRequestType"] = "verifyValidGetContactListForUser";
	jsonObj["UserName"] = user;
	jsonObj["Password"] = pass;
	
	//convert object into JSON string format
	var jsonStr = JSON.stringify(jsonObj);
	
//	alert(jsonStr);
	
	//create new http request object and specify its type
	var xml = new XMLHttpRequest();
	
	xml.open("POST", url, false);
	xml.setRequestHeader("Content-type", "application/json; charset=utf-8");
	
	//attempt to send JSON string to API and throw exception if it fails
	try
	{
		xml.send(jsonStr);
		
	//	alert(xml.responseText);
		
		console.log(xml.responseText);
		
		if(xml.responseText == "Password and Hash do not match.\n")
		    throw new Error("The username/password is incorrect");
		
		// Note: the reason for the parse error { was because it was taking in the { of the 2nd contact
		var jsonObjReceived = JSON.parse(xml.responseText);
		
		if(jsonObjReceived.id == 0)
			throw new Error("The username/password is incorrect");
		
		var nbrContacts = jsonObjReceived.contactList.length;
		// Adding sorting feature
		jsonObjReceived.contactList.sort(function(a,b)
		{
		    return (a.FName.toLowerCase() > b.FName.toLowerCase()) ? 1 : ((b.FName.toLowerCase() > a.FName.toLowerCase()) ? -1 : 0);
		});
		
		//console.log(JSON.stringify(jsonObjReceived));
		
        Cookies.set("nbrContacts", nbrContacts);
        Cookies.set("linkedID", jsonObjReceived.id);
		for (var i = 0; i < nbrContacts; i++)
		{
		    Cookies.set(i + "FName", jsonObjReceived.contactList[i]["FName"]);
		    Cookies.set(i + "LName", jsonObjReceived.contactList[i]["LName"]);
		    Cookies.set(i + "ContactID", jsonObjReceived.contactList[i]["ContactID"]);
		}
		
		window.open('./contact.html', "_self");
		
	}
	catch(err)
	{
		document.getElementById("pass").value = "";
		document.getElementById("login_failed").innerHTML = err.message;
	}
}

function signUp()
{
	location.assign("https://www.mycontactsucf.com/signup.html");
}
