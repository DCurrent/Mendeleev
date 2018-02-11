<?php

	require_once(__DIR__.'/settings.php');
	require_once(__DIR__.'/data_main.php');

	// Process log on and log off.
	class class_access_process
	{
		private
			$action			= NULL,
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

			session_start();

			if(isset($_SESSION[ACCESS_SES_KEY::REDIRECT])) $this->redirect = $_SESSION[ACCESS_SES_KEY::REDIRECT];
		
		}	
		
		// Populate members from $_REQUEST.
		public function populate_from_request()
		{		
			// Interate through each class method.
			foreach(get_class_methods($this) as $method) 
			{		
				$key = str_replace('set_', '', $method);
							
				// If there is a request var with key matching
				// current method name, then the current method 
				// is a set mutator for this request var. Run 
				// it (the set method) with the request var. 
				if(isset($_REQUEST[$key]))
				{										
					$this->$method($_REQUEST[$key]);				
				}
			}			
		}	
		
		public function dialog()
		{
			if(isset($_SESSION[ACCESS_SES_KEY::DIALOG]))
			{
				return $_SESSION[ACCESS_SES_KEY::DIALOG];
			}
		}
		
		public function get_redirect()
		{
			return $this->redirect;
		}
		
		public function get_login_result()
		{
			return $this->login_result;
		}
		
		public function get_feedback()
		{
			return $this->feedback;
		}
		
		public function get_settings()
		{
			return $this->settings;
		}
		
		public function get_account_data()
		{
			return $this->account_data;
		}
		
		public function set_redirect($value)
		{
			// Temporarly disabled so we don't kill redirect. Will need to
			// change namgin convention for mutators to get around this.
			
			//$this->redirect = $value;
		}
		
		public function set_account_data($value)
		{
			$this->account_data = $value;
		}
			
		public function set_access_action($value)
		{
			$this->action = $value;
		}
		
		// Shortcut controller for login process.
		public function process_control()
		{
			$this->populate_from_request();
			
			switch($this->action)
			{
				case ACCESS_ACTION::LOGIN;				
					
					// Populate account data from request.
					$this->account_data->populate_from_request();
					
					// First try local.
					// $this->login_local();
									
					// If local fails, try LDAP.
					//if($this->login_result != ACCESS_LOGIN_RESULT::LOCAL)
					//{
						$this->login_ldap();
					//}
					
					$this->action();
					break;
					
				case ACCESS_ACTION::LOGOFF;
					
					$this->logoff();
					break;
			}
		}
		
		// Take action based on result of login attempt.
		public function action()
		{		
			//echo '<br />$this->login_result: '.$this->login_result;
			
			switch($this->login_result)
			{				
				case ACCESS_LOGIN_RESULT::LOCAL:      			
				case ACCESS_LOGIN_RESULT::LDAP:			
				
					// Get account information from
					// application database.
					$this->login_application();
				
					// Record client information information into session.
					$this->account_data->session_save();				
										
					// Set dialog.					
					// Start caching page contents.
					ob_start();
					?>
						<span class="text-success">Hello <?php echo $this->account_data->get_name_f(); ?>, your log in was successful.</span>	
					<?php
					
					// Collect contents from cache and then clean it.
					$_SESSION[ACCESS_SES_KEY::DIALOG] = ob_get_contents();
					ob_end_clean();	
					
					// Redirect URL passed?		
					if($this->redirect)
					{					
						// If headers are not sent, redirect to user requested page.
						if(headers_sent())
						{							
						}
						else
						{						
							header('Location: '.$this->redirect);
						}				
					}
					
					break;
				
				case ACCESS_LOGIN_RESULT::NO_BIND:
				
					// Set dialog.					
					// Start caching page contents.
					ob_start();
					?>
						<span class="text-danger">Bad user name or password.</span>	
					<?php
					
					// Collect contents from cache and then clean it.
					$_SESSION[ACCESS_SES_KEY::DIALOG] = ob_get_contents();
					ob_end_clean();
					
					break;			
				
				case ACCESS_LOGIN_RESULT::NO_INPUT:
				default: 				
				
					// Default log in dialog.
					$_SESSION[ACCESS_SES_KEY::DIALOG] = NULL;
			}
		}
		
		// Log the current user.
		public function logoff()
		{	
			// Remove all session data.
			session_unset();
			
			if(session_status() === PHP_SESSION_ACTIVE)
			{
				session_destroy();
			}
			
			// Clear account object data.
			$this->account_data->clear();
			
									
			// If headers are not sent, redirect to the authenticate url.
			if(headers_sent())
			{						
			}
			else
			{						
				header('Location: '.$this->settings->get_authenticate_url());
			}		
		}
		
			
		public function login_ldap()
		{		
			/*
			login
			Damon Vaughn Caskey
			2012-02-03
			
			Process login attempt.		
			*/
						
			$principal		= NULL;		// Active directory principal (account).
			$result			= NULL;		// Active directory account search result.
			$entries		= NULL;		// Active directory entry array.
			
			$bind			= FALSE;	// Result of bind attempt.
			$ldap			= NULL;		// ldap connection reference.
			
			$req_account	= NULL;
			$req_credential	= NULL;					
								
			// Get values.
			$req_account 			= $this->account_data->get_account();
			$req_credential			= $this->account_data->get_credential();				
				
			// User provided credentials? 
			if ($req_account != NULL && $req_credential != NULL)
			{
											
				// Attempt to bind user through LDAP using all known domain prefixes.
				$bind = $this->ldap_bind_check();
				
				// If we were able to bind user through AD LDAP, we will then run search in EDIR to get their basic information. 
				// Otherwise the account doesn't exist or user entered bad credentials. 
				if($bind === TRUE)
				{									
					// Connect to LDAP EDIR.
					$ldap = ldap_connect($this->settings->get_ldap_host_dir());
					
					if(!$ldap) trigger_error("Cannot connect to LDAP: ".$this->settings->get_ldap_host_dir(), E_USER_ERROR); 
					
					// Search for account name.
					$result = ldap_search($ldap, $this->settings->get_ldap_base_dn(), 'uid='.$req_account);			
					
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
					
					// Save account data into session.
					$this->account_data->session_save();
					
					$this->login_result = ACCESS_LOGIN_RESULT::LDAP;			
									
					// Release ldap query result.
					ldap_free_result($result);		
										
					// Close ldap connection.
					ldap_close($ldap);									
				}
				else // No Bind.
				{
					$this->login_result = ACCESS_LOGIN_RESULT::NO_BIND;
				}														
			}
					
			// Return results.
			return $this->login_result;		
		}
		
		private function ldap_bind_check()
		{
			/*
			ldap_bind_check
			2013-11-13
			~2015-07-19: 
			
			Damon V. Caskey
			
			Attempt to bind ldap adding all possible prefixes. 
			*/
			
			$result			= FALSE;	// Final result.
			$account		= NULL;		// Prepared account string to attempt bind.
			$prefix_list 	= array();
			$prefix 		= NULL;		// Singular prefix value taken from array.
			$ldap 			= NULL;
			
			$ldap = ldap_connect($this->settings->get_ldap_host_bind());
			if(!$ldap) trigger_error("Cannot connect to LDAP: ".$this->settings->get_ldap_host_bind(), E_USER_ERROR);
									
			ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
			
			// Dereference account name and remove any domain prefixes. We'll add our own below.
			$account = str_ireplace($prefix, '', $account);
			
			//$prefix_list = explode(',', $this->settings->get_dn_prefix());
			$prefix_list = array(NULL, 'ad/', 'ad\\', 'mc/', 'mc\\');
						
			// Keep trying prefixes until there is a bind or we run out.
			foreach($prefix_list as $prefix)
			{		
				$account = $prefix.$this->account_data->get_account();
				
				// Attempt to bind with account (prefix included) and password.
				$result = @ldap_bind($ldap, $account, $this->account_data->get_credential());
				
				// If successfull bind.
				if($result === TRUE) break;
			}	
			
			// Close ldap connection.
			ldap_close($ldap);				
					
			// Return results.
			return $result;
		}
		
		
		// Process login attempt through local database.
		public function login_local()
		{					
			// Get values.
			$req_account 			= $this->account_data->get_account();
			$req_credential			= $this->account_data->get_credential();
						
			$db = new class_db_connection();
			$query = new class_db_query($db);			
		
			// Query the local account table using given account and password.
			$query->set_sql("{call account_login(@account 		= ?,														 
												@credential 	= ?)}");				
			
			$params = array(&$req_account, &$req_credential);
			
			$query->set_params($params);		
			$query->query();
			
			// If a row is returned, then provided credentials match a local login.
			if($query->get_row_exists())
			{
				// Populate account data object with datbase row.
				$query->get_line_params()->set_class_name('class_access_account_data');					
				$this->account_data = $query->get_line_object();
				
				// Email is not in the data base as a field, but accounts
				// ARE email, so just transpose it here.
				$this->account_data->set_email($this->account_data->get_account());
				
				// Set result to indicate a local login.				 														
				$this->login_result = ACCESS_LOGIN_RESULT::LOCAL;									
			}
			else
			{				
				$this->login_result = ACCESS_LOGIN_RESULT::NO_BIND;
			}
		}
		
		// Get account information from application specific database.
		public function login_application()
		{	
			/*				
			// Get values.
			$account = $this->account_data->get_account();
	
			$db_conn_set = new class_db_connect_params();
			$db_conn_set->set_name('inspection');
	
			$db = new class_db_connection($db_conn_set);
			$query = new class_db_query($db);			
		
			// Query the local account table using given account and password.
			$query->set_sql("{call account_lookup(@account = ?)}");				
			
			$params = array($account);
			
			$query->set_params($params);		
			$query->query();
			
			// If a row is returned, then provided credentials match a local login.
			if($query->get_row_exists())
			{
				// Populate account data object with datbase row.
				$query->get_line_params()->set_class_name('class_access_account_data');					
				$this->account_data = $query->get_line_object();									
			}
			else
			{				
			}
			*/
		}	
	}

	

?>