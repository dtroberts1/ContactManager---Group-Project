//*note to self
//- some id's are needed to define in html
//- set up hyperlinks for 'cancel' button
//- set up onclick action for 'sign up' button

var url = 'https://www.mycontactsucf.com/mainPHP.php';

function signUp()
{
	//gets the value inside password verification textbox
	var fname = document.getElementById("fname").value;
	
	//gets the value inside password verification textbox
	var lname = document.getElementById("lname").value;
	
	//gets the value inside username textbox
	var user = document.getElementById("user").value;
	
	//gets the value inside password textbox
	var pass = document.getElementById("pass").value;
	
	//gets the value inside password confirmation textbox
	var repass = document.getElementById("pass2").value;
	
	if((fname == "") || (lname == "") || (user == "") || (pass == "") || (repass == ""))
	{
	    document.getElementById("signup_failed").innerHTML = "All fields must be filled in!";
	    document.getElementById("pass").value = "";
		document.getElementById("pass2").value = "";
	    return;
	}
	
	if(pass != repass)
	{
		document.getElementById("signup_failed").innerHTML = "Passwords do not match!";
		document.getElementById("pass").value = "";
		document.getElementById("pass2").value = "";
		return;
	}
	
	var jsonObj = {};
	jsonObj["apiRequestType"] = "signUp";
	jsonObj["FName"] = fname;
	jsonObj["LName"] = lname;
	jsonObj["Username"] = user;
	jsonObj["Password"] = pass;
	
	var jsonStr = JSON.stringify(jsonObj);
	
	/*
	alert(jsonStr); //verify data is in JSON format
	*/
	
	var xml = new XMLHttpRequest();
	
	xml.open("POST", url, false);
	xml.setRequestHeader("Content-type", "application/json; charset=utf-8");
	
	//attempt to send JSON string to API and throw exception if it fails
	try
	{
		xml.send(jsonStr);
		
		//alert(xml.responseText);
		
		//store reponse from API into a variable
		var jsonObjReceived = JSON.parse(xml.responseText);
		
		//if the username is taken throw an exception otherwise user is
		//redirected to login page and alerted of success
		if(jsonObjReceived.valid == 0)
		{
			throw new Error( "Username is already taken.");
		}
		else
		{
			alert("Your registration is complete.");
			Cookies.set('linkedID', jsonObjReceived.linkedID);
			Cookies.set('nbrContacts', 0);
		//	location.replace("http://www.mycontactsucf.com/index.html");
		window.open("./contact.html", "_self");
		}
	}
	catch(err)
	{
		document.getElementById("user").value = "";
		document.getElementById("pass").value = "";
		document.getElementById("pass2").value = "";
		
		document.getElementById("signup_failed").innerHTML = err.message;
	}
	
	return;
}

function backToLogin()
{
	location.assign("http://www.mycontactsucf.com/index.html");
}
