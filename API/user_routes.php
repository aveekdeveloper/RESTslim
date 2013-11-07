<?php
include_once 'userdbQueries.php';
 
function unique_md5() {
    mt_srand(microtime(true)*100000 + memory_get_usage(true));
    return md5(uniqid(mt_rand(), true));
}

function SetErrorMessage($error)
{
	$array = array();
	$array['error']=$error;
	return $array; 
}

function GenerateAccessToken($key)
{
	return crypt($key , unique_md5());
}

function IsEmailRegistered($email)
{
	$user = GetUserByEmail($email);
	//If user with email is in database
	if(!empty($user))
	{
		return True;
	} else
	{
		return False;
	}
}

function RegisterUser($email , $password , $name) {
	
	//check if Email is valid
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		return SetErrorMessage("Invalid email provided for registration");
	}
	//check if name provided
	if($name == '')
	{
		return SetErrorMessage("Name not provided for registration");
	}
	//check if password provided
	if($password == '')
	{
		return SetErrorMessage("Password not provided for registration");
	}
	
	//Password hash
	$password_hash =  hash('sha1' , $password);
	//Access token
	$access_token = GenerateAccessToken($password);
	//When the user first registers he is inactive
	$is_active = False; 
	
	$result = InsertNewUser($email,$password_hash,$name,$is_active,$access_token);
	
	if($result == 0 )
	{
		return(SetErrorMessage("User with email id already registered"));
	} else
	{
		//TODO : Send the user's access_token to his email to verify his password
		return(GetUserById($result));
	}
	
}

function LoginUser($email , $password) {
	$password_hash =  hash('sha1' , $password);
	$user = GetUserByEmail($email);
	
	if(empty($user))
	{
		return SetErrorMessage("User with email not registered");
	}
	
	if($user['is_active'] ==0)
	{
		//Access token will only be given after one time activation
		$user['access_token'] = '';
	}
	if(strcmp($user['password_hash'] ,$password_hash)==0)
	{
		return $user;
	} else
	{
		return SetErrorMessage("Invalid Password");
	}
}

function AuthenticateUser($id ='', $email ='', $access_token)
{
	$user = NULL;
	if($id != '')
	{
		$user = GetUserById($id);
	}
	if($email != '')
	{
		$user = GetUserByEmail($email);
	}
	
	if($user == NULL)
	{
		return SetErrorMessage("The user could not be authenticated");
	}
	
	if(strcmp($user['access_token'] , $access_token))
	{
		if($user['is_active'] == 0)
		{
			//Since the user activated itself set him/her to be active
			UpdateUser($user['id'] ,'','','', 1);
		}
		return True;
	}
	
	return False;
}

function RegenerateAccessToken($email , $password)
{
	$user = LoginUser($email , $password);
	
	if($user['error'])
	{
		return $user;
	}
	
	//Generate new access token
	$access_token = GenerateAccessToken($password);
	UpdateUser($user['id'] ,'','','', '' , $access_token);
	//TODO :Send email to user with the new access token
	
	return SetErrorMessage("Access token successfully regenerated");
}

//User Registration
$app->post('/api/users' , function() use ($app)	{
		$email = $app->request->post('email');
		$name = $app->request->post('name');
		$password = $app->request->post('password');
		
		//Insert into table and set the user as inactive
		echo json_encode(RegisterUser($email , $password , $name));
	});
	
//User Login
$app->get('/api/users/login' , function() use ($app)	{
		$email = $app->request->get('email');
		$password = $app->request->get('password');
		
		echo json_encode(LoginUser($email , $password));
	});
	
//User Authenticate
$app->post('/api/users/authenticate',function() use ($app){
	$email = $app->request->post('email');
	$id = $app->request->post('id');
	$access_token = $app->request->post('access_token');
	
	echo json_encode(AuthenticateUser($id , $email , $access_token));
});

//User Update
$app->post('/api/user/:id' , function(){
	$email = $app->request->post('email');
	$name = $app->request->post('name');
	$password = $app->request->post('password');
	
	if($password != '')
	{
	//Generate new access token
	}
	
	if($email != '')
	{
	//User is changing email , so account needs to be verified again , generate new access token , deactivate the account till reactivation
	}
} )
?>