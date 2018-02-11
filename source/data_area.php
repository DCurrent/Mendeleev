<?php

	require_once(__DIR__.'/data_common.php');
	
	class mendeleev_class_area_data extends mendeleev_class_common_data
	{
		private			
			$account				= NULL,
			$facility				= NULL,
			$pi_name_f				= NULL,
			$pi_name_l				= NULL,
			$update_type			= NULL,
			$area_list				= NULL,
			$areas					= NULL,
			$department				= NULL;		
			
		// Accessors
		public 
			function get_account()
			{
				return $this->account;
			}
			
			function get_facility()
			{
				return $this->facility;
			}
			
			function get_pi_name_f()
			{
				return $this->pi_name_f;
			}
			
			function get_pi_name_l()
			{
				return $this->pi_name_l;
			}
			
			function get_update_type()
			{
				return $this->update_type;
			}
			
			function get_areas()
			{
				return $this->areas;
			}	
			
			function get_area_list()
			{
				return $this->area_list;
			}
			
			function get_department()
			{
				return $this->department;
			}	
		
		// Mutators
		public 
			function set_account($value)
			{
				$this->account = $value;
			}
			
			function set_facility($value)
			{
				$this->facility = $value;
			}
			
			function set_pi_name_f($value)
			{
				$this->pi_name_f = $value;
			}
			
			function set_pi_name_l($value)
			{
				$this->pi_name_l = $value;
			}	
			
			function set_update_type($value)
			{
				$this->update_type = $value;
			}	
			
			function set_areas($value)
			{
				$this->areas = $value;
			}
			
			function set_area_list($value)
			{
				$this->area_list = $value;
			}
			
			function set_department($value)
			{
				$this->department = $value;
			}
	}	
	
?>