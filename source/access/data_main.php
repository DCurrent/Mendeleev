<?php

	require_once(__DIR__.'/settings.php');

	class class_access_common_data
	{	
		// Populate members from $_REQUEST.
		public function populate_from_request()
		{		
			// Interate through each class method.
			foreach(get_class_methods($this) as $method) 
			{		
				$key = str_replace('set_', '', $method);
				
				//echo '<br />$key: '.$key;
							
				// If there is a request var with key matching
				// current method name, then the current method 
				// is a set mutator for this request var. Run 
				// it (the set method) with the request var. 
				if(isset($_REQUEST[$key]))
				{					
					$this->$method($_REQUEST[$key]);					
				
					//echo ', _REQUEST:'.$_REQUEST[$key];				
				}
			}			
		}
	}
	
	class class_access_account_data extends class_access_common_data
	{	
		private
			$id			= NULL,	// ID as assigned by application account database.
			$account	= NULL,		
			$account_id	= NULL,	// Natural ID - i.e., given by organization.
			$credential	= NULL,
			$email		= NULL,
			$name_f		= NULL,
			$name_l		= NULL,
			$name_m		= NULL;			
		
		// Send account data to session.
		public function session_save()
		{
			$_SESSION[ACCESS_SES_KEY::ID] 			= $this->id;
			$_SESSION[ACCESS_SES_KEY::ACCOUNT] 		= $this->account;
			$_SESSION[ACCESS_SES_KEY::ACCOUNT_ID]	= $this->account_id;
			$_SESSION[ACCESS_SES_KEY::EMAIL]		= $this->email;
			$_SESSION[ACCESS_SES_KEY::NAME_F]		= $this->name_f;
			$_SESSION[ACCESS_SES_KEY::NAME_L]		= $this->name_l;
			$_SESSION[ACCESS_SES_KEY::NAME_M]		= $this->name_m;
		}
		
		// Get account data from session.
		public function session_get()
		{
			if(isset($_SESSION[ACCESS_SES_KEY::ID])) 			$this->id 			= $_SESSION[ACCESS_SES_KEY::ID];
			if(isset($_SESSION[ACCESS_SES_KEY::ACCOUNT])) 		$this->account 		= $_SESSION[ACCESS_SES_KEY::ACCOUNT];
			if(isset($_SESSION[ACCESS_SES_KEY::ACCOUNT_ID]))	$this->account_id	= $_SESSION[ACCESS_SES_KEY::ACCOUNT_ID];
			if(isset($_SESSION[ACCESS_SES_KEY::EMAIL])) 		$this->email 		= $_SESSION[ACCESS_SES_KEY::EMAIL];
			if(isset($_SESSION[ACCESS_SES_KEY::NAME_F])) 		$this->name_f 		= $_SESSION[ACCESS_SES_KEY::NAME_F];
			if(isset($_SESSION[ACCESS_SES_KEY::NAME_L])) 		$this->name_l 		= $_SESSION[ACCESS_SES_KEY::NAME_L];
			if(isset($_SESSION[ACCESS_SES_KEY::NAME_M])) 		$this->name_m 		= $_SESSION[ACCESS_SES_KEY::NAME_M];
		}
		
		// Clear all data members.
		public function clear()
		{
			// Clear data members.	
			foreach ($this as $key => $value) 
			{
				$this->$key = NULL;
			}	
			
			// Record empty data members to session.
			$this->session_save();
		}
		
		public function name_full()
		{
			return $this->get_name_f().' '.$this->get_name_l();
		}
		
		public function name_proper()
		{
			$result = NULL;
			
			// Last name found? Ad last name.
			if($this->get_name_l()) $result .= $this->get_name_l();
			
			// First name found?
			if($this->get_name_f())
			{
				// Last name found? If so we add the comma. Otherwise just first name.
				if($this->get_name_l()) $result .= ', ';
				
				$result .= $this->get_name_f();							
			}
			
			// Middle name found? Add it as well.
			//if($this->get_name_m()) $result .= ' '.$this->get_name_m();		
			
			return $result;			
		}
		
		public function get_id()
		{
			return $this->id;
		}
		
		public function get_account()
		{
			return $this->account;
		}
				
		public function get_name_f()
		{
			return $this->name_f;
		}
		
		public function get_name_m()
		{
			return $this->name_m;
		}
		
		public function get_name_l()
		{
			return $this->name_l;
		}
		
		public function get_credential()
		{
			return $this->credential;
		}
		
		public function get_account_id()
		{
			return $this->account_id;
		}
		
		public function get_email()
		{
			return $this->email;
		}
		
		public function set_id($value)
		{
			$this->id = $value;
		}
		
		public function set_credential($value)
		{
			$this->credential = $value;
		}
		
		public function set_account($value)
		{
			$this->account = $value;
		}
		
		public function set_name_f($value)
		{
			$this->name_f = $value;
		}
		
		public function set_name_m($value)
		{
			$this->name_m = $value;
		}		
		
		public function set_name_l($value)
		{
			$this->name_l = $value;
		}
		
		public function set_account_id($value)
		{
			$this->account_id = $value;
		}
		
		public function set_email($value)
		{
			$this->email = $value;
		}
	}
	
	class class_access_request_data extends class_access_common_data
	{
		private		
			$access_action = NULL;
			
		public function __construct()
		{
			$this->populate_from_request();
		}
		
		public function get_access_action()
		{
			return $this->access_action;
		}	
	}
?>