<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require(APPPATH.'/libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Wscontroller extends REST_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('Authorization_Token');
		$this->load->model('UserModel');
	}

	public function validate_access_token($headers)
	{
		try{
			if(array_key_exists('Authorization', $headers))
			{
				
			    $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
		        if($decodedToken['status'] != 1)
		        {
		        	$data = ERROR( -1,  'Token time Expire.');
					$this->response($data);
		        }
		        $data = json_decode(json_encode($decodedToken), true);   
		        return $data['data'];
			}
			else
			{
				$data = ERROR(-1, 'Token not found.');
				$this->response($data);

			}
		}catch(Exception $e)
	    {
	        return $e->getMessage();
	    }
	}

	public function register_post()
	{
		try{
			$name = $this->input->post('name');
			$email = $this->input->post('email');
			$phone = $this->input->post('phone');
			$password = $this->input->post('password');
			$role_id = $this->input->post('role_id');
			$registration_type = $this->input->post('registration_type');
			$registration_id = $this->input->post('registration_id');

			// validation
			if(empty($name)){
				$data = ERROR( 0, 'Please enter the name');

				$this->response($data);
			}

			if(empty($email)){
				$data = ERROR( 0, 'Please enter the email');
				$this->response($data);
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$data = ERROR( 0, 'Please enter valid email');
			  	$this->response($data);
			}

			if(empty($phone)){
				$data = ERROR( 0, 'Please enter the phone');
				$this->response($data);
			}

			$is_exist = $this->UserModel->email_exist($email);

			if(!empty($is_exist)){
				$data = ERROR( 0, 'User already exist this email');
				$this->response($data);
			}

			if(empty($role_id)){
				$data = ERROR( 0, 'Please enter the role_id');
				$this->response($data);
			}

			if(empty($registration_type)){
				$data = ERROR( 0, 'Please enter the registration_type');
				$this->response($data);
			}

			if(!empty($registration_type) && $registration_type != 'Other')
			{
				$data = ERROR( 0, 'Please enter the registration_id');
				$this->response($data);
			}

			$user_data['vName'] = $name;
			$user_data['vEmail'] = $email;
			$user_data['vPhone'] = $phone;
			$user_data['iRoleId'] = $role_id;

			$user_data['eRegistrationType'] = $registration_type;
			$user_data['vRegistrationId'] = $registration_id;

			$user_data['vPassword'] = password_hash($password, PASSWORD_DEFAULT);
			$result = $this->UserModel->register_user($user_data);

			if(!empty($result))
			{
				$data = SUCCESS(1, 'User Register successfully.',[]);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0,  'Something went wrong...please try again.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function login_get()
	{
		try{
			$email = $this->input->get('email');
			$password = $this->input->get('password');

			if(empty($email)){
				$data = ERROR( 0, 'Please enter the email.');
				$this->response($data);
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$data = ERROR( 0, 'Please enter valid email');
			  	$this->response($data);
			}

			if(empty($password)){
				$data = ERROR( 0, 'Please enter the password');
				$this->response($data);
			}

			$user_data['vEmail'] = $email;		
			$user_data['vPassword'] = $password;

			$is_match = $this->UserModel->login_action($user_data);
			$record = json_decode(json_encode($is_match), true);

			if(!empty($is_match))
			{
				if (password_verify($password, $record[0]['password'])) 
				{
					$token['user_id'] = $record[0]['user_id'];
					$token['name'] = $record[0]['name'];
					$token['email'] = $record[0]['email'];
					$enc_token = $this->authorization_token->generateToken($token);
					$this->UserModel->update_token($enc_token,$token['user_id']);

					$user_details = $this->UserModel->get_user($token['user_id']);
					
					$data = SUCCESS( 1,  'You have logged in successfully. ', $user_details);
					$this->response($data);
				}else{
					$data = ERROR( 0,  'Please enter valid password');
					$this->response($data);
				}
			}else{
				$data = ERROR( 0,  'Account not found with this email.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function my_profile_get()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);			

			if(!empty($this->input->get('user_id')))
			{	
				$user_id = $this->input->get('user_id');
			}
			else
			{
				$user_id = $token['user_id'];
			}

			$user = $this->UserModel->get_user($user_id);

			if(!empty($user))
			{
				$data = SUCCESS( 1, 'User data found successfully.',$user);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'User not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	public function update_profile_post()
	{
		try{
			$headers = $this->input->request_headers(); 
			$token = $this->validate_access_token($headers);			
			$user_id = $token['user_id'];
			
			$name = $this->input->post('name');
			$phone = $this->input->post('phone');

			// validation
			if(empty($name)){
				$data = ERROR( 0, 'Please enter the name');

				$this->response($data);
			}

			if(empty($phone)){
				$data = ERROR( 0, 'Please enter the phone');
				$this->response($data);
			}

			$user_data['vName'] = $name;
			$user_data['vPhone'] = $phone;

			$user = $this->UserModel->update_user($user_id,$user_data);

			if(!empty($user))
			{
				$user_details = $this->UserModel->get_user($user_id);
				$data = SUCCESS( 1, 'User updated successfully.',$user_details);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'User not updated.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}

	
	public function get_roles_get()
	{
		try{

			$roles = $this->UserModel->user_roles();

			if(!empty($roles))
			{
				$data = SUCCESS( 1, 'Roles found successfully.',$roles);
				$this->response($data);
			}
			else
			{
				$data = ERROR( 0, 'Roles not found.');
				$this->response($data);
			}
		}catch(Exception $e){
			$data = ERROR( 0, $e->getMessage());
			$this->response($data);
		}
	}
}
