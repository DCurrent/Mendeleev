<?php

	require_once(__DIR__.'/data_common.php');
	
	class mendeleev_class_group_data extends mendeleev_class_common_data
	{
		private
			$type = NULL;
			
		public function get_type()
		{
			return $this->type;
		}
		
		public function set_type($value)
		{
			$this->type = $value;
		}
	}
	
	class mendeleev_class_group_member_data extends mendeleev_class_common_data
	{
		private
			$member	= NULL;
		
		// Accessors.	
		public function get_member()
		{
			return $this->member;
		}
		
		// Mutators.
		public function set_member($value)
		{
			$this->member = $value;
		}
	}
	
	class mendeleev_class_account_data extends mendeleev_class_common_data
	{
		protected			
			$account	= NULL,	
			$department	= NULL,
			$name_f		= NULL,			
			$name_l		= NULL,
			$name_m		= NULL,
			$notes		= NULL,
			$status		= NULL;	// UK Status
			
		// Accessors					
		public function get_account()
		{
			return $this->account;
		}
		
		public function get_department()
		{
			return $this->department;
		}
		
		public function get_name_f()
		{
			return $this->name_f;
		}		
		
		public function get_name_l()
		{
			return $this->name_l;
		}
		
		public function get_name_m()
		{
			return $this->name_m;
		}
		
		public function get_notes()
		{
			return $this->notes;
		}
		
		public function get_status()
		{
			return $this->status;
		}
		
		// Mutators
		public function set_account($value)
		{
			$this->account = $value;
		}
		
		public function set_department($value)
		{
			$this->department = $value; 
		}
		
		public function set_name_f($value)
		{
			$this->name_f = $value;
		}
		
		public function set_name_l($value)
		{
			$this->name_l = $value;
		}
		
		public function set_name_m($value)
		{
			$this->name_m = $value;
		}
		
		public function set_notes($value)
		{
			$this->notes = $value;
		}
		
		public function set_status($value)
		{
			$this->status = $value;
		}				
	}	
	
?>