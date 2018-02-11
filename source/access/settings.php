<?php

	require_once(__DIR__.'/interface.php');

	class class_access_settings
	{
		private
			$administrator		= NULL,
			$ldap_host_bind		= NULL,
			$ldap_host_dir		= NULL,
			$ldap_base_dn		= NULL,
			$dn_prefix			= NULL,
			$authenticate_url	= NULL,
			$diagnostic			= NULL,
			$diagnostic_mail	= NULL,
			$use_local			= NULL;
		
		public function __construct()
		{		
			$this->administrator 	= ACCESS_DEFAULTS::ADMINISTRATOR;
			$this->ldap_host_bind	= ACCESS_DEFAULTS::LDAP_HOST_BIND;
			$this->ldap_host_dir	= ACCESS_DEFAULTS::LDAP_HOST_DIR;
			$this->ldap_base_dn		= ACCESS_DEFAULTS::LDAP_BASE_DN;
			$this->dn_prefix		= ACCESS_DEFAULTS::DOMAIN_PREFIX;
			$this->authenticate_url	= ACCESS_DEFAULTS::AUTHENTICATE_URL;
			$this->diagnostic		= ACCESS_DEFAULTS::DIAGNOSTIC;
			$this->diagnostic_mail	= ACCESS_DEFAULTS::DIAGNOSTIC_MAIL;
			$this->use_local		= ACCESS_DEFAULTS::USE_LOCAL;		
		}
		
		// Accessors	
		public function get_administrator()
		{
			return $this->administrator;
		}
		
		public function get_ldap_host_bind()
		{
			return $this->ldap_host_bind;
		}
		
		public function get_ldap_host_dir()
		{
			return $this->ldap_host_dir;
		}
		
		public function get_ldap_base_dn()
		{
			return $this->ldap_base_dn;
		}
		
		public function get_dn_prefix()
		{
			return $this->dn_prefix;
		}
		
		public function get_authenticate_url()
		{
			return $this->authenticate_url;
		}
		
		public function get_diagnostic()
		{
			return $this->diagnostic;
		}
		
		public function get_diagnostic_mail()
		{
			return $this->diagnostic_mail;
		}
		
		public function get_use_local()
		{
			return $this->use_local;
		}
		
		// Mutators
		public function set_administrator($value)
		{
			$this->administrator = $value;
		}
		
		public function set_ldap_host_bind($value)
		{
			$this->ldap_host_bind = $value;
		}
		
		public function set_ldap_host_dir($value)
		{
			$this->ldap_host_dir = $value;
		}
			
		public function set_ldap_base_dn($value)
		{
			$this->ldap_base_dn = $value;
		}
		
		public function set_authenticate_url($value)
		{
			$this->authenticate_url = $value;
		}
		
		public function set_diagnostic($value)
		{
			$this->diagnostic = $value;
		}
		
		public function set_diagnostic_mail($value)
		{
			$this->diagnostic_mail = $value;
		}
		
		public function set_use_local($value)
		{
			$this->use_local = $value;
		}
	}


?>