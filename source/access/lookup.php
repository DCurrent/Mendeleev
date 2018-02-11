<?php

	require_once(__DIR__.'/settings.php');
	require_once(__DIR__.'/data_main.php');

	// Look up a user
	class class_access_lookup
	{
		private
			$access_action			= NULL,
			$account_data	= NULL,	// Object containing acount data (name, account etc.)
			$login_result	= NULL, // Result of login attempt.
			$settings 		= NULL,	// Settings object.
			$feedback		= NULL, // Feedback.
			$redirect		= NULL;	// URL user came from and should be sent back to after login.
			
		public function __construct(class_access_settings $settings = NULL)
		{
			// If settings object provided, we'll use it. Otherwise
			// create a new object with default values.
			if(is_object($settings) === TRUE)
			{		
				$this->settings = $settings;			
			}
			else
			{
				$this->settings = new class_access_settings();			
			}
			
			$this->account_data = new class_access_account_data();		
		}	
		
		
		public function get_settings()
		{
			return $this->settings;
		}
		
		public function get_account_data()
		{
			return $this->account_data;
		}
		
		public function set_account_data($value)
		{
			$this->account_data = $value;
		}
			
		public function lookup($account)
		{		
			/*
			login
			Damon Vaughn Caskey
			2012-02-03
			
			Process login attempt.		
			*/
						
			$result			= NULL;		// Active directory account search result.
			$entries		= NULL;		// Active directory entry array.
			
			$bind			= FALSE;	// Result of bind attempt.
			$ldap			= NULL;		// ldap connection reference.
				
			// No account? Get out before we cause a nasty error.
			if ($account === NULL) return;
									
			// Connect to LDAP EDIR.
			$ldap = ldap_connect($this->settings->get_ldap_host_dir());
			
			if(!$ldap) trigger_error("Cannot connect to LDAP: ".$this->settings->get_ldap_host_dir(), E_USER_ERROR); 
			
			// Search for account name.
			$result = ldap_search($ldap, $this->settings->get_ldap_base_dn(), 'uid='.$account);			
			
			// Trigger error if no result located.			
			if (!$result) trigger_error("Could not locate entry in EDIR.", E_USER_ERROR);
			
			// Get user info array.
			$entries = ldap_get_entries($ldap, $result);
			
			// Trigger error if entries array is empty.
			if($entries["count"] < 0) trigger_error("Entry found but contained no data.", E_USER_ERROR);
			
			// Populate account object members with user info.
			if(isset($entries[0]['cn'][0])) 			$this->account_data->set_account($entries[0]['cn'][0]);
			if(isset($entries[0]['givenname'][0])) 		$this->account_data->set_name_f($entries[0]['givenname'][0]);
			if(isset($entries[0]['initials'][0]))		$this->account_data->set_name_m($entries[0]['initials'][0]);
			if(isset($entries[0]['sn'][0]))				$this->account_data->set_name_l($entries[0]['sn'][0]);					
			if(isset($entries[0]['workforceid'][0]))	$this->account_data->set_account_id($entries[0]['workforceid'][0]);
			if(isset($entries[0]['mail'][0]))			$this->account_data->set_email($entries[0]['mail'][0]);				
									
			// Release ldap query result.
			ldap_free_result($result);		
								
			// Close ldap connection.
			ldap_close($ldap);						
		}			
	}

	

?>