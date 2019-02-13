	// Dylan will implement these functions.
	/* For editField
		When pencil is selected, the field will become a textfield so user can change it 
		(no additional menu will pop up).  When the user clicks off the text field, no changes 
		will be made to database (only to the webpage) until "modify" is selected. 
		Also when pencil is selected, the textfield border will appear
	*/

	$("form").find(".input").blur(function()
	{
		var myClass = document.getElementsByClassName("input");
		$('document').ready(function() 
		{
			if(!($("form").find(".input").is(":focus")))
			{
				for(i = 0; i < myClass.length; i++)
				{
					myClass.item(i).disabled = "disabled"; // no-modify mode. 
					myClass.item(i).style.background = "#1d2731"; // dark
				}
			}
		});
	});
	function narrowSearch(listOb)
	{
	    
		var myIndex = [];
		
		$('document').ready(function() // have to make sure everything is in proper order.
		{		
			var searchParam = document.getElementById("searchParam").value;
			var allRows = document.getElementsByClassName("listRows");
			// Populate Array with First and Last Names
		
			// Identify which Names closely match the searchParam
			myIndex = identifyAllMatches(listOb.entireList, searchParam);
			// Display in the list only these names. 	

			// Will need an asynchronous server request. It will pass linkedID and get all contacts for that ID. 
			// So each time it will make the server call then narrow down list.
			var myTable = document.getElementById("cList");
			myTable.innerHTML = ""; // Clear Table. 
			document.getElementById("forTable").appendChild(myTable);
			for (var i = 0; i < myIndex.length; i++) // Go through each contact that has been identified as match. 
			{ 
				var firstAndLast = [];
				td = document.createElement("td");
				if ((i % 3) == 0) // multiple of three means append a new row to table (not enough horizontal space for 4 names). 
				{
					tr = document.createElement("tr");	
					tr.className = "listRows";
					myTable.appendChild(tr);
					td.className = "firstCol";
				}
				else
					td.className = "cTDLIST";
				td.setAttribute("onclick", "openContactDetails(this)");
				// [myIndex[i] * 2] is the string containing the matching first and last name with the 2 separated by " "
				// So separate the string into 2 elements and assign to firstAndLast
				firstAndLast = listOb.entireList[myIndex[i] * 2].split(" "); 
				for (var j = 0; j < 2; j++)
				{
					span = document.createElement("span");
					span.innerHTML = firstAndLast[j] + "&nbsp";
					td.appendChild(span);
				}
				span = document.createElement("span");
				span.innerHTML = listOb.contactIds[myIndex[i] * 2];
		//		span.innerHTML = listOb.contactIds[myIndex[i]];
				span.className = "spanID";
				span.setAttribute("hidden", true);			
				td.appendChild(span);
				// need to assign span to the subset of myIndex separated by the " ", then append to <Td>
				
				tr.appendChild(td);
			}
		console.log("displaying contents of myIndex\n");
		displayArrayContents(myIndex);			
		});
		
		function displayArrayContents(inArray)
		{
			var str = '\n';
			for (var i = 0; i < inArray.length; i++)
			{
				str += inArray[i];
				str += "\n";
			}
			console.log(str);
		}
	}
	function getContactsUsingID(inputString)
	{
			//var entireList = [];		
		var listOb = {entireList : [], contactIds : []};
		var url = "./mainPHP.php";
		var http_request = new XMLHttpRequest();	
		http_request.open("POST", url, true);
		
		http_request.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	
		var IDForQuery = {apiRequestType: "getContactListForUser", LinkedUser: document.getElementById("linkedIDContent").innerHTML};
		
		var jsonPayload = JSON.stringify(IDForQuery);
		try
		{
			http_request.onreadystatechange = function()
			{
				if(this.readyState == 4)
				{
					if (this.status == 200)
					{
						if (this.responseText !== null)
						{
							var returnedObject = JSON.parse(this.responseText);
                    		returnedObject.contactList.sort(function(a,b)
                    		{
                    		    return (a.FName.toLowerCase() > b.FName.toLowerCase()) ? 1 : ((b.FName.toLowerCase() > a.FName.toLowerCase()) ? -1 : 0);
                    		});
							for (var i = 0; i < returnedObject.contactList.length; i++)
							{
									listOb.entireList.push(returnedObject.contactList[i]["FName"] + " " + 
									returnedObject.contactList[i]["LName"]);
									listOb.contactIds.push(returnedObject.contactList[i]["ContactID"]);
							}
							
							narrowSearch(listOb);				
						}
					}
				}
			}
			http_request.send(jsonPayload);
		}
		catch(error)
		{
			console.log(error.message);
		}
		
	}
	function identifyAllMatches(array, inputString)
	{
		var arrayForReturn = [];
		for (var i = 0; i < array.length; i++)
		{
			if (isSubset(inputString, array[i]) == 1)
			{
				arrayForReturn.push(i / 2); // divide by 2 to account for the ID
			}
		}
		return arrayForReturn;
	}
	function isSubset(array1, array2)
	{
		// Now, need to make a function that calls this function, that function will 
		// build up an array of indices that correspond to matching entireList[i].
		
		var lenX = array1.length;
		var lenY = array2.length;
		var i;
		if (lenX > lenY) // if search param is larger than the entireList[i], it can't possibly be a subset
			return 0;
		for (i = 0; i < lenY; i++)
		{
			if (i >= lenX) // if made it this far and it's the end of searchParam, then it is a subset.
				return 1;
			
			if ((array1.charAt(i).toLowerCase()) != (array2.charAt(i).toLowerCase())) 
				return 0;
		}
		
		return 1;
	}
	
	function getObject(inputElement)
	{
		return document.getElementById(inputElement);
	}
	function editField(className) // will be called from Edit Info (Contact) Page
	{	  
		var myclass = document.getElementsByClassName(className);

	//	document.getElementById("signupDiv").innerHTML = "testing output";
		for (i = 0; i < myclass.length; i++)
		{
			myclass[i].disabled = false;
			myclass[i].style.background = "#394149"; // lighter
		}
		document.getElementById("edit_firstname").focus();
	}

	function makeModifiedChanges() // When modified button button is clicked inside "edit info" page
	{
		// go to "edit contact" page. Will be called from "contacts" page
		var url = "./mainPHP.php";
															//	alert('made it this far (-2)');

		var http_request = new XMLHttpRequest();	
		http_request.open("POST", url, false); 
		http_request.setRequestHeader("Content-type", "application/json; charset=UTF-8");
		// First make object of contact attributes
		// Note that span tags go by" innerHTML", inputs go by "value"
		var currentContactWithRequestType = {apiRequestType: "modifyContact", ContactID: document.getElementById("ContactID").innerHTML, LinkedUser: document.getElementById("LinkedUser").innerHTML, FName: document.getElementById("edit_firstname").value, LName: document.getElementById("edit_lastname").value, PhoneNum: document.getElementById("edit_phonenumber").value, Email: document.getElementById("email_address").value, Company: document.getElementById("edit_companyname").value, Occupation: document.getElementById("edit_occupation").value};
    		// make call to server to get information about the contact that user selected
    		var jsonPayload = JSON.stringify(currentContactWithRequestType);
            
    		try 
    		{
    		    
    			http_request.send(jsonPayload);
                console.warn(http_request.responseText)			
    			console.log(http_request.responseText);
    
        		// Note: the reason for the parse error { was because it was taking in the { of the 2nd contact
        		var jsonObjReceived = JSON.parse(http_request.responseText);
        		
        		var nbrContacts = jsonObjReceived.contactList.length;
        		// Adding sorting feature
        		
        		jsonObjReceived.contactList.sort(function(a,b)
        		{
        		    return (a.FName.toLowerCase() > b.FName.toLowerCase()) ? 1 : ((b.FName.toLowerCase() > a.FName.toLowerCase()) ? -1 : 0);
        		});
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
    			console.log(err.message);
    		}
	}
	function goBackToContactList()
	{
		    window.open("./contact.html", "_self");
	}
	function goToSignup()
	{
		document.location = "./signup.html";
	}
	
	function getCookie()
	{
	    alert("cookie is " + cookie.get('myKey'));
	}

	function openContactDetails(currentElement)
	{		

	    // with buttons to edit. 
		// go to "edit contact" page. Will be called from "contacts" page
		var returnedObject = null;
		var url = "./mainPHP.php";
		var http_request = new XMLHttpRequest();	
		http_request.open("POST", url, true); 
		http_request.setRequestHeader("Content-type", "application/json; charset=UTF-8");
		console.log("made it here111");
		if (currentElement.children[2].innerHTML == null)
			console.log("currentElement.children[2].innerHTML is null");
		else
			console.log("currentElement.children[2].innerHTML is " + currentElement.children[2].innerHTML);
		var currentContactWithRequestType = {apiRequestType: "getContactInfo", ContactID: currentElement.children[2].innerHTML};
		// make call to server to get information about the contact that user selected
		// First make object of contact attributes

		jsonPayload = JSON.stringify(currentContactWithRequestType);

		try
		{
			http_request.onreadystatechange = function() 
			{
				if (this.readyState == 4)//
				{
					if(this.status == 200)
					{
						if(this.responseText !== null)
						{
							// Note: the reason for the parse error { was because it was taking in the { of the 2nd contact
							returnedObject = JSON.parse(this.responseText);
						
							// Pass html page, elementID inside that page, and outputvalue
							// console.log just for debugging (devtools). 
							console.log(returnedObject["fName"]);
							console.log(returnedObject["lName"]);
							console.log(returnedObject["Email"]);
							console.log(returnedObject["PhoneNum"]);
							console.log(returnedObject["Company"]);
							console.log(returnedObject["Occupation"]);
							console.log(returnedObject["contactID"]);

							Cookies.set('fName', returnedObject["fName"]);
							Cookies.set('lName', returnedObject["lName"]);
							Cookies.set('Email', returnedObject["Email"]);
							Cookies.set('PhoneNum', returnedObject["PhoneNum"]);
							Cookies.set('Company', returnedObject["Company"]);
							Cookies.set('Occupation', returnedObject["Occupation"]);
							Cookies.set("contactID", returnedObject["contactID"]);
							Cookies.set("LinkedID", document.getElementById("linkedIDContent").innerHTML);
				    		var myWindow = window.open("./editContact.html", "_self");
					}
				}
			}
			}
			http_request.send(jsonPayload);	
		
		}
		catch(err)
		{
			alert(err.message);
		}

	}
    function nameModified()
    {
   //     document.getElementById("nameModified").innerHTML = 1;
   //     document.getElementById("nameModified").style="display : block;";
    //    alert("Name has been modified.");
    }
	function getContactList() // Will be called by login(), which will be called from login page
	{
		// sanitizeInput()
		// verifyRegisteredUser()
		// get contactList from user
	}
	
	function logoutApp() // will be called from "Contacts" Page
	{
		document.location = "./index.html";
	}
	
	function goToMakeNewContactPage()
	{
		Cookies.set("LinkedID", document.getElementById("linkedIDContent").innerHTML);
		var myWindow = window.open("./createNewContact.html", "_self");
	}
	function makeNewContact() // Will be called from "Contacts" Page
	{
		// go to "edit contact" page. Will be called from "contacts" page
		var url = "./mainPHP.php";
		var http_request = new XMLHttpRequest();	
		http_request.open("POST", url, false); 
		http_request.setRequestHeader("Content-type", "application/json; charset=UTF-8");
		// First make object of contact attributes
		// Note that span tags go by" innerHTML", inputs go by "value"
		var currentContactWithRequestType = {apiRequestType: "makeNewContact",  LinkedUser: document.getElementById("LinkedUser").innerHTML, FName: document.getElementById("edit_firstname").value, LName: document.getElementById("edit_lastname").value, PhoneNum: document.getElementById("edit_phonenumber").value, Email: document.getElementById("email_address").value, Company: document.getElementById("edit_companyname").value, Occupation: document.getElementById("edit_occupation").value};
		
		// make call to server to get information about the contact that user selected
		var jsonPayload = JSON.stringify(currentContactWithRequestType);
        
		try 
		{
		    
			http_request.send(jsonPayload);
            console.warn(http_request.responseText)			
			console.log(http_request.responseText);

    		// Note: the reason for the parse error { was because it was taking in the { of the 2nd contact
    		var jsonObjReceived = JSON.parse(http_request.responseText);
    		
    		var nbrContacts = jsonObjReceived.contactList.length;
    		// Adding sorting feature
    		
    		jsonObjReceived.contactList.sort(function(a,b)
    		{
    		    return (a.FName.toLowerCase() > b.FName.toLowerCase()) ? 1 : ((b.FName.toLowerCase() > a.FName.toLowerCase()) ? -1 : 0);
    		});
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
			console.log(err.message);
		}
	}