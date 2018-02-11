<?php

	require_once(__DIR__.'/data_main.php');
		
	class class_access_status extends class_access_account_data
	{
		private
			$settings		= NULL,
			$access_list	= array(),
			$authorized		= NULL,
			$redirect		= NULL;
		
		public function dialog()
		{
			if(isset($_SESSION[ACCESS_SES_KEY::DIALOG]))
			{
				return $_SESSION[ACCESS_SES_KEY::DIALOG];
			}
		}
		
		public function get_settings()
		{
			return $this->settings;
		}
		
		public function get_access_list()
		{
			return $this->access_list;
		}
		
		public function get_authorized()
		{
			return $this->authorized;
		}
		
		public function get_redirect()
		{
			return $this->redirect;
		}
		
		public function get_ip()
		{
			return $_SERVER['REMOTE_ADDR'];
		}
		
		// Mutators	
		public function set_settings(class_access_settings $value)
		{
			$this->settings = $value;
		}
		
		public function set_access_list($value)
		{
			$this->access_list = $value;
		}
		
		public function set_authorized($value)
		{
			$this->authorized = $value;
		}
		
		public function set_redirect($value)
		{
			$this->redirect = $value;
		}
					
		public function __construct(class_access_settings $settings = NULL)
		{
			// Make sure we have an active session
			if(session_status() == PHP_SESSION_NONE)
			{			
				session_start();
			}
			
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
			
			// Account session get.
			$this->session_get();
			
			// Default redirect to calling script.
			if(isset($_SESSION[ACCESS_SES_KEY::REDIRECT]))
			{
				$this->redirect = $_SESSION[ACCESS_SES_KEY::REDIRECT];
			}
			else
			{			
				$this->redirect = $_SERVER['PHP_SELF'];					
			}
		}
		
		private function is_guid($guid)
		{
			$result;
			
			$result = !empty($guid) && preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $guid);
			
			return TRUE;
		}
		
		public function verify()
		{	
			/*
			Damon Vaughn Caskey
			verify
			2015-07-16
			~Redesigned from verify created 2011-11-10
			
			Verify user is logged into session with AD account, and is part of authorized group.
			*/		
			
			$val			= NULL;								// Value extracted from array loop.
			$result 		= ACCESS_AUTHORIZED_RESULT::NO;		// Authorized?
			$access_list	= NULL;								// List of authorized users.
			$access_item	= NULL;								// Indivdiual item from list.	
			$is_guid		= NULL;
			
			// 2016-09-29
			//
			// This is a stopgap. The old account system for other sites
			// shares session database, and uses the workforce ID as
			// an account name. That can result in serious errors
			// since this application needs GUID IDs. To solve,
			// We'll check to verify the ID is a guid here.
			// If not, user will have to log in through this
			// aplication, ensuring the ID is in fact a GUID.
			$is_guid = $this->is_guid($this->get_id());
			
			// Verify account is set at all. If so, then evaluate against provided list (if any).			
			if($this->get_account() === NULL || $is_guid == FALSE)											
			{	
				$result	= ACCESS_AUTHORIZED_RESULT::NONE;
			}
			else
			{	
				
				// Defreference access list.
				$access_list = $this->access_list;
				
				// Is access_list a double linked list object?
				if(is_object($access_list) === TRUE)
				{
					// Loop over each item in double linked list and look for a match vs.
					// current account.
					for($access_list->rewind(); $access_list->valid(); $access_list->next())
					{						
						$access_item = $access_list->current();
						
						// If current account matches an item in the list, we can allow access.
						if($access_item->get_account() === $this->get_account() || $access_item->get_account() === ACCESS_SETTINGS::ADMINISTRATOR)			
						{
							// Set result to allow access and break out of loop.
							$result = ACCESS_AUTHORIZED_RESULT::YES;
							break;	
						}
					}
				}
				else
				{
					// No list of users provided, so we allow access to any legit user.
					$result = ACCESS_AUTHORIZED_RESULT::YES;	
				}				
			}
			
			$this->authorized = $result;
				
			// Return result.
			return $result;
		}	
		
		public function action()
		{		
			// Now we'll take action based on authorization result.
			switch ($this->authorized)
			{
				// Client is logged in with sufficiant access.
				case ACCESS_AUTHORIZED_RESULT::YES:			
					
					break;
				
				// Client is logged in but lacks sufficiant access.
				case ACCESS_AUTHORIZED_RESULT::NO:				
					
					// Start caching page contents.
					ob_start();
					?>
						<span class="text-warning">We're sorry <?php echo $this->name_f_m; ?>, but you are not permitted to access this resource. Please log in with an authorized account.</span>	
					<?php
					
					// Collect contents from cache and then clean it.
					$_SESSION[ACCESS_SES_KEY::DIALOG] = ob_get_contents();
					ob_end_clean();			
				
					
				
				// No client is logged in.	
				default:
				case ACCESS_AUTHORIZED_RESULT::NONE:
					
					$_SESSION[ACCESS_SES_KEY::REDIRECT] = $this->redirect;	
					
					// If headers are not sent, redirect to login page. Otherwise we'll just have
					// to settle for an inline message.
					if(headers_sent())
					{
						echo $this->access_post_header();
					}
					else
					{	
						header('Location: '.$this->settings->get_authenticate_url());
					}				
					
					// Exit the script here. This stops bots that ignore headers and prevents 
					// users from proceeding even if headers had already been sent.
					exit;
					break;			
			}
		}
		
		// Output content for user if headers already sent at time of access denial.
		private function access_post_header()
		{
			$markup = NULL;
			
			ob_start();
			?>
			
			<!DOCtype html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<meta http-equiv="refresh" content="10;URL=<?php echo $this->settings->get_authenticate_url(); ?>"> 
					<title>UK - Environmental Health And Safety Login</title>        
				</head>
				
				<body>        
					<?php echo $_SESSION[ACCESS_SES_KEY::DIALOG]; ?>
					
					Click <a href="<?php echo $this->settings->get_authenticate_url(); ?>">here</a> to log in as an authorized user.
				</body>
			</html>
							
			<?php
					
			// Collect contents from cache and then clean it.
			$markup = ob_get_contents();
			ob_end_clean();
			
			return $markup;
		}
	}

?>
