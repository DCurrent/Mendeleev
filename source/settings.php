<?php

	abstract class APPLICATION_SETTINGS
	{
		const
			VERSION 		= '0.1',
			NAME			= 'Mendeleev',
			DIRECTORY_PRIME	= '/apps/mendeleev',
			TIME_FORMAT		= 'Y-m-d H:i:s',
			PAGE_ROW_MAX	= 25;
	}

	abstract class DATABASE
	{
		const NAME	= 'ehsinfo';
	}

	abstract class MAILING
	{
		const
			TO		= '',
			CC		= '',
			BCC		= '',
			SUBJECT = 'Mendeleev Alert',
			FROM 	= 'ehs_noreply@uky.edu';
	}
	
	abstract class SESSION_ID
	{
		const
			LAST_BUILDING	= 'id_last_building';	// Last building choosen by user.
	}

?>