var APIRoot = "http://COP4331-3.com/IceCreams/API";
var fileExtension = ".php";
var userId = 0;
var firstName = '', lastName = '';

function doLogin()
{
	var login = document.getElementById("loginName").value;
	var password = document.getElementById("password").value;

	var jsonPayload = '{"login" : "' + login + '", "password" : "' + password + '"}';
	var url = APIRoot + '/Login' + fileExtension;
	
	var xhr = new XMLHttpRequest();
	xhr.open("POST", url, false);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.send(jsonPayload);
		var jsonObject = JSON.parse( xhr.responseText );
		userId = jsonObject.id;
		if( userId < 1 )
		{
			return;
		}
		
		firstName = jsonObject.firstName;
		lastName = jsonObject.lastName;
		
		document.getElementById("loginUI").style.visibility = 'hidden';
		document.getElementById("flavorUI").style.visibility = 'visible';
	}
	catch(err)
	{
		alert(err.message);
	}

}

function addFlavor()
{
	var flavorToAdd = document.getElementById("flavorToAdd").value;
	
	var jsonPayload = '{"flavor" : "' + flavorToAdd + '", "userId" : ' + userId + '}';
	var url = APIRoot + '/AddFlavor' + fileExtension;
	
	var xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				alert("Flavor has been added");
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		alert(err.message);
	}
}

function searchFlavor()
{
	var searchCriteria = document.getElementById("searchCriteria").value;
	
	var jsonPayload = '{"search" : "' + searchCriteria + '"}';
	var url = APIRoot + '/SearchFlavors' + fileExtension;
	
	var xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try
	{
		xhr.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
			{
				var values = '';
				var jsonObject = JSON.parse( xhr.responseText );
				var i;
				for( i=0; i<jsonObject.results.length; i++ )
				{
					values += jsonObject.results[i];
					values += "\r";
				}
				alert( values );
			}
		};
		xhr.send(jsonPayload);
	}
	catch(err)
	{
		alert(err.message);
	}
}

function doLogout()
{
	document.getElementById("loginUI").style.visibility = 'visible';
	document.getElementById("flavorUI").style.visibility = 'hidden';
}

