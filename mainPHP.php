<?php

    //Important Note about Input Variable Names**********
    // They will be the exact same as on the database

    // Set up Connection to SQL
	$server = "localhost";
	$DBUser = "andresco_andres";
	$DBPassword = "ucfteam201903";
	$DBName = "andresco_contacts";
    $queryRes = array();
	$connection = new MySqli($server, $DBUser, $DBPassword, $DBName);
	if ($connection->connect_error) die("Connection error");
	
	// Decode JSON
	$jDecoded = json_decode(file_get_contents('php://input'), true);
	
	// Get Variable for Switch Statement 
    $jsRequestType = $jDecoded["apiRequestType"];
  //  echo "apiRequestType is " . $jDecoded["apiRequestType"];
//	$jsRequestType = "getContactListForUser";
	switch($jsRequestType)
	{
		case "test": // just for basic connection tests
		{
			echo "test";
			break;
		}
		case "getContactInfo": // Already Completed, this just takes LinkedUser
		{                      // and a specific contact and returns all contact details for this user's contact
			getContactDetails($jDecoded, $connection);
			return;
		}
		case "modifyContact": // Will provide API with all contact details for a user. API returns nothing
		{
			modifyContact($jDecoded, $connection);
			break;
		}
		case "signUp": // Will provide API with all contact details for a user. API will return nothing
		{
			$queryRes = getSignUp($jDecoded, $connection);
			break;
		}
		case "makeNewContact": // Will provide API with all User related fields (not contact list). API returns nothing. 
		{
		    makeNewContact($jDecoded, $connection);
			// to implement
			break;
		}		
		case "verifyValidGetContactListForUser": // Will provide API with username and password, API will return true or false on if user has account
		{
			verifyValidGetContactListForUser($jDecoded, $connection);
			break;
		}
        case "getContactListForUser":
        {
            getContactListForUser($jDecoded, $connection);
            break;
        }
        case "deleteContact":
        {
            deleteContact($jDecoded, $connection);
            break;
        }
    }
    
    function deleteContact($requestedContact, $connection)
    {
        $inputId = $requestedContact["ContactID"];
        
        $sql = "delete from Contacts where ContactID='$inputId'";

        if($result = $connection->query($sql) != TRUE)
        {
            echo "Error";
        }
        else
        {
            echo "Contact has been deleted.";
        }
        $connection->close();

    }
	
	function getSignUp($newUser, $connection)
	{
	    
        $username = $newUser["Username"];
        $password = $newUser["Password"];
        $fname = $newUser["FName"];
        $lname = $newUser["LName"];
      
        $returnedObject = new stdClass();
        $returnedObject->valid = 0;

        $sql = "insert into User(UserName, Password, FName, LName) VALUES ('$username', '$password', '$fname', '$lname')";

        
        if($result = $connection->query($sql) != TRUE)
        {
            //$returnedObject->id = 0;
            echo json_encode($returnedObject);
        }
        else
        {
            $returnedObject->valid = 1;
            $sql = "select ID from User where UserName='$username' and Password='$password'";
            $result = $connection->query($sql);
            $returnedObject->linkedID = $result;
            echo json_encode($returnedObject);
        }
        $connection->close();
	}

	function getContactDetails($requestedContact, $connection)
	{	
		$inputId = $requestedContact["ContactID"];
		$query = "select * from Contacts where ContactID='$inputId'"; 
		$contactSearch = $connection->query($query);
		if(!$contactSearch) die("query returns null");

        $result = $contactSearch->fetch_array(MYSQLI_ASSOC);
		$currentContact = array(
		'contactID' => $result["ContactID"],
		'fName' => $result["FName"],
		'lName' => $result["LName"],
		'PhoneNum' => $result["PhoneNum"],
		'Email' => $result["Email"],
		'Company' => $result["Company"],
		'LinkedUser' => $result["LinkedUser"],
		'Occupation' => $result["Occupation"]
        );
		echo json_encode($currentContact);
	   // echo $currentContact;
	    
	}
	
	function modifyContact($requestedContact, $connection)
	{
	    $ID = $requestedContact["ContactID"];
	    $fname = $requestedContact["FName"];
	    $fname = protect($fname);
	    $lname = $requestedContact["LName"];
	    $lname = protect($lname);
	    $phonenum = $requestedContact["PhoneNum"];
	    $phonenum = protect($phonenum);
	    $email = $requestedContact["Email"];
	    $email = protect($email);
	    $company = $requestedContact["Company"];
	    $company = protect($company);
	    $linkeduser = $requestedContact["LinkedUser"];
	    $linkeduser = protect($linkeduser);
	    $occupation = $requestedContact["Occupation"];
	    $occupation = protect($occupation);
	    
	    /*
	    $sql = "insert into CONTACTS(ContactID, FNAME, LNAME, PHONENUM, EMAIL, COMPANY, LINKEDUSER, OCCUPATION) VALUES('$ID', '$fName', '$lName', '$phonenum', '$email', '$company', '$linkeduser', '$occupation')";
        */
        $sql = "update Contacts set FName='$fname', LName='$lname', PhoneNum='$phonenum', Email='$email', Company='$company', LinkedUser='$linkeduser', Occupation='$occupation' where ContactID='$ID'"; 
        if($result = $connection->query($sql) != TRUE)
        {
            echo "Error";
        }
        $connection->close();
	}
	
	function makeNewContact($newContact, $connection)
	{
	    //$ID = $newContact["ContactID"]; 
	    //echo " this is the ID";
	    //echo $ID;
	    $fname = $newContact["FName"];
	    $fname = protect($fname);
	    $lname = $newContact["LName"];
	    $lname = protect($lname);
	    $phonenum = $newContact["PhoneNum"];
	    $phonenum = protect($phonenum);
	    $email = $newContact["Email"];
	    $email = protect($email);
	    $company = $newContact["Company"];
	    $company = protect($company);
	    $linkeduser = $newContact["LinkedUser"];
	    $linkeduser = protect($linkeduser);
	    $occupation = $newContact["Occupation"];
	    $occupation = protect($occupation);
	    
	    /*$sql = "insert into Contacts where (FNAME, LNAME, PHONENUM, EMAIL, COMPANY, LINKEDUSER, OCCUPATION) VALUES
          ('" . $fname . "', '" . $lname . "', '" . $phonenum . "', '" . $email . "', '" . $company . "', '" . $linkeduser . "', '" . $occupation . "')";
         */
         
	    $sql = "insert into Contacts(FName, LName, PhoneNum, Email, Company, LinkedUser, Occupation) VALUES('$fname', '$lname', '$phonenum', '$email', '$company', '$linkeduser', '$occupation')";
	    
        if($result = $connection->query($sql) != TRUE)
        {
            echo "Error";
        }
        else
        {
            echo "Values are: $fname, $lname, $phonenum, $email, $company, $linkeduser, $occupation";
        }
        $connection->close();
	}
		
	
	function verifyValidGetContactListForUser($newUser, $connection)
	{
	    $Linked_User_ID = $newUser["Login"];
	    $Linked_User_ID = protect($Linked_User_ID);
	    $Password = $newUser["Password"];
	    $Password = protect($Password);
//	    echo "in api, id is" . $Linked_User_ID . "and password is " . $Password;
	    $returnedObject = new stdClass();
	    $returnedObject->id = 0;
	    $returnedObject->contactList = array(array());

  
	    // Query DB to check if user is valid
	    $query = "select * from User where UserName = '$Linked_User_ID' and Password ='$Password'";
	    
	    $userSearch = $connection->query($query);
	    if($userSearch->num_rows == 0)
	    {
	        echo json_encode($returnedObject);
	        $connection->close();
	        return;
	    }
	    $tempArray = $userSearch->fetch_array(MYSQLI_ASSOC);
	    $returnedObject->id = $tempArray["ID"];
	//    echo "made it past, and id is " . $returnedObject->id;
	    
	    $query = "select * from Contacts where LinkedUser='$returnedObject->id '";
	    $allContacts = $connection->query($query);
	    $i = 0;
	    while($tempArray = $allContacts->fetch_array(MYSQLI_ASSOC))
	    {
            $returnedObject->contactList[$i]["FName"] = $tempArray["FName"];
            $returnedObject->contactList[$i]["LName"] = $tempArray["LName"];
            $returnedObject->contactList[$i]["ContactID"] = $tempArray["ContactID"];
            $i++;
	    }

        echo json_encode($returnedObject);
	    $connection->close();
	}
	function getContactListForUser($userDetails, $connection)
	{
	    $LinkedUser = $userDetails["LinkedUser"];
	    $LinkedUser = protect($LinkedUser);
	    $returnedObject = new stdClass();
	    $returnedObject->contactList = array(array());

	    // Query DB to check if user is valid
	    $query = "select * from Contacts where LinkedUser = '$LinkedUser'";
	    
	    $allContacts = $connection->query($query);
	    $i = 0;
	    while($tempArray = $allContacts->fetch_array(MYSQLI_ASSOC))
	    {
            $returnedObject->contactList[$i]["FName"] = $tempArray["FName"];
            $returnedObject->contactList[$i]["LName"] = $tempArray["LName"];
            $returnedObject->contactList[$i]["ContactID"] = $tempArray["ContactID"];
            $i++;
	    }
    //    echo($returnedObject);
        echo json_encode($returnedObject);

	    $connection->close();
	}
	
	function protect($variable)
	{
	//    $variable = strip_tags(mysql_real_escape_string(trim($variable)));
	    return $variable;
	}
	
	// MySQL injection protection
	// sanitize data when interacting with the database
    // mysql_real_escape_string($variable) = Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection
    // Example: mysql_real_escape_string($user)
    // Example: mysql_query("select * from user where id='".mysql_real_escape_string($_GET[id])."'");
    
    // XSS protection
    // sanitize user input to prevent xss output
    // part of this is also not allowing special characters eg <>&''"" within input fields
    // strip_tags($variable) = This function tries to return a string with all NULL bytes, HTML and PHP tags stripped from a given str.
    // Example: strip_tags($user)

?>