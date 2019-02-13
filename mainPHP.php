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
    
    function get_result(\mysqli_stmt $statement)
    {
    $result = array();
    $statement->store_result();
    for ($i = 0; $i < $statement->num_rows; $i++)
    {
        $metadata = $statement->result_metadata();
        $params = array();
        while ($field = $metadata->fetch_field())
        {
            $params[] = &$result[$i][$field->name];
        }
        call_user_func_array(array($statement, 'bind_result'), $params);
        $statement->fetch();
    }
    return $result;
    }
    
    function deleteContact($requestedContact, $connection)
    {
        $inputId = $requestedContact["ContactID"];
        
        //$sql = "delete from Contacts where ContactID='$inputId'";
        
        $stmt = $connection->prepare("DELETE FROM Contacts WHERE ContactID=?");
        $stmt->bind_param('i', $inputId);
        
        if($stmt->execute())
        {
            echo "Contact has been deleted.";
        }
        else
        {
            echo "Error";
        }
        
        $stmt->close();
        $connection->close();

    }
	
	function getSignUp($newUser, $connection)
	{
	    
        $username = $newUser["Username"];
        $password = $newUser["Password"];
        
        $password = password_hash($password, PASSWORD_DEFAULT);
        
        $fname = $newUser["FName"];
        $lname = $newUser["LName"];
      
        $returnedObject = new stdClass();
        $returnedObject->valid = 0;
        
        //$sql = "insert into User(UserName, Password, FName, LName) VALUES ('$username', '$password', '$fname', '$lname')";

        $stmt = $connection->prepare("INSERT INTO User(UserName, Password, FName, LName) VALUES (?,?,?,?)");
        
        $stmt->bind_param('ssss', $username, $password, $fname, $lname);
        
        if(!$stmt->execute())
        {
            //$returnedObject->id = 0;
            echo json_encode($returnedObject);
        }
        else
        {
            $returnedObject->valid = 1;

            //$sql = "select ID from User where UserName='$username' and Password='$password'";
            
            $stmt2 = $connection->prepare("SELECT ID FROM User WHERE UserName = ? AND Password = ?");
            
            $stmt2->bind_param('ss', $username, $password);
            $stmt2->execute();

            $id = get_result($stmt2);
            
            $returnedObject->linkedID = $id[0]['ID'];
            
            echo json_encode($returnedObject);
            $stmt2->close();
        }
        
        
        
        $stmt->close();
        $connection->close();
	}

	function getContactDetails($requestedContact, $connection)
	{	
		$inputId = $requestedContact["ContactID"];
		// contactID is not accessible by user input. no need to prevent sql injection
		
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
        
        
        /*
        $stmt = $connection->prepare("SELECT * FROM Contacts WHERE ContactID = ?");
        
        $stmt->bind_param("i", $inputId);
        
        $stmt->execute();
        $contact = get_result($stmt);
        
        //echo "contact id <<<";
        //echo $contact[0]['ContactID'];
        
        $currentContact = array(
        'contactID' => $contact[0]['ContactID'],
		'fName' => $contact[0]['FName'],
		'lName' => $contact[0]['LName'],
		'PhoneNum' => $contact[0]['PhoneNum'],
		'Email' => $contact[0]["Email"],
		'Company' => $contact[0]["Company"],
		'LinkedUser' => $contact[0]["LinkedUser"],
		'Occupation' => $contact[0]["Occupation"]
        );
        
        
        $stmt->close();
        $connection->close();
        /*
        $returnedObject->ContactID = $contact[0]['ContactID'];
        $returnedObject->FName = $contact[0]['FName'];
        $returnedObject->LName = $contact[0]['LName'];
        $returnedObject->PhoneNum = $contact[0]['PhoneNum'];
        $returnedObject->Email = $contact[0]['Email'];
        $returnedObject->Company = $contact[0]['Company'];
        $returnedObject->LinkedUser = $contact[0]['LinkedUser'];
        $returnedObject->Occupation = $contact[0]['Occupation'];
        */
        
	    
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
	    
	    
	    $stmt = $connection->prepare("UPDATE Contacts SET FName=?, LName=?, PhoneNum=?, Email=?, Company=?, LinkedUser=?, Occupation=? WHERE ContactID=?");
	    
	    $stmt->bind_param('sssssisi', $fname, $lname, $phonenum, $email, $company, $linkeduser, $occupation, $ID);
	    
	    
	    /*
	    $sql = "insert into CONTACTS(ContactID, FNAME, LNAME, PHONENUM, EMAIL, COMPANY, LINKEDUSER, OCCUPATION) VALUES('$ID', '$fName', '$lName', '$phonenum', '$email', '$company', '$linkeduser', '$occupation')";
        
        $sql = "update Contacts set FName='$fname', LName='$lname', PhoneNum='$phonenum', Email='$email', Company='$company', LinkedUser='$linkeduser', Occupation='$occupation' where ContactID='$ID'"; 
        */
        if(!$stmt->execute())
        {
            $connection->close();
            $stmt->close();            
            return null;
        }
        else // Query for all contacts for the user, with 
        {
            $stmt->close();            
            getContactListForUser($requestedContact, $connection);
        }
        
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
         /*
	    $sql = "insert into Contacts(FName, LName, PhoneNum, Email, Company, LinkedUser, Occupation) VALUES('$fname', '$lname', '$phonenum', '$email', '$company', '$linkeduser', '$occupation')";
	    $result = $connection->query($sql);
	    */
	    $stmt = $connection->prepare("INSERT INTO Contacts(FName, LName, PhoneNum, Email, Company, LinkedUser, Occupation) VALUES (?,?,?,?,?,?,?)");
	    
	    $stmt->bind_param('sssssis', $fname, $lname, $phonenum, $email, $company, $linkeduser, $occupation);
	    
        if(!$stmt->execute())
        {
            $connection->close();
            $stmt->close();            
            return null;
        }
        else // Query for all contacts for the user, with 
        {
            $stmt->close();            
            getContactListForUser($newContact, $connection);
        }
}
		
/*	
	function verifyValidGetContactListForUser($newUser, $connection)
	{
	    $Linked_User_ID = $newUser["UserName"];
	    echo json_encode($Linked_User_ID);
	    $Linked_User_ID = mysqli_real_escape_string($connection, $Linked_User_ID);
	    $Password = $newUser["Password"];
	    echo json_encode($Password);
	    $Password = mysqli_real_escape_string($connection, $Password);
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
*/
	
	function verifyValidGetContactListForUser($newUser, $connection)
	{
	    //echo "start";
	    $Linked_User_ID = $newUser["UserName"];
	    //echo json_encode($Linked_User_ID);
	    $Linked_User_ID = mysqli_real_escape_string($connection, $Linked_User_ID);
	    
	    $Password = $newUser["Password"];
	    $Password = mysqli_real_escape_string($connection, $Password);
	    
	    $sql = $connection->prepare("SELECT * FROM User WHERE UserName = ?");
	    $sql->bind_param('s', $Linked_User_ID);
	    $sql->execute();
	    
        $vpw = get_result($sql);
	    $hash = $vpw[0]['Password'];
	        
        if(password_verify($Password, $hash))
	    {
	        //echo "Password and Hash verified.\n";
	        $Password = $hash;
            $returnedObject = new stdClass();
        	$returnedObject->id = 0;
        	$returnedObject->contactList = array(array());
        
          
        	// Query DB to check if user is valid
        	$query = "select * from User where UserName = '$Linked_User_ID' and Password ='$Password'";
        	    
        	$userSearch = $connection->query($query);
        	if($userSearch->num_rows === 0)
        	{
        	    //echo json_encode($returnedObject);
        	    $connection->close();
        	    return;
        	}
        	
        	$tempArray = $userSearch->fetch_array(MYSQLI_ASSOC);
        	$returnedObject->id = $tempArray["ID"];
        	//echo "made it past, and id is " . $returnedObject->id;
        	    
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
	        //echo "Contact List:\n";
            echo json_encode($returnedObject);
	    }
	    else
	    {
	        echo "Password and Hash do not match.\n";
	    }
        
        $sql->close();
	    $connection->close();
	}
	
	function getContactListForUser($userDetails, $connection)
	{
	    $LinkedUser = $userDetails["LinkedUser"];
	    //LinkedUser isn't accessible from inputs. no need to prevent sql injection.
	    $returnedObject = new stdClass();
	    $returnedObject->contactList = array(array());

	    // Query DB to check if user is valid
	    $query = "select * from Contacts where LinkedUser = '$LinkedUser'";
	    
	    $allContacts = $connection->query($query);
	    $i = 0;
	    $returnedObject->id = $LinkedUser;
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
	
	function protect($variable)
	{
	    //$variable = mysqli_real_escape_string($connection, $variable);
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