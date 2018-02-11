<?php 
	
	require(__DIR__.'/source/main.php');
	
	$dialog_no_areas = FALSE;	
	
	// Page caching.
	$page_obj = new class_page_cache();
	ob_start();	
			
	// Main navigaiton.
	$obj_navigation_main = new class_navigation();
	$obj_navigation_main->generate_markup_nav();
	$obj_navigation_main->generate_markup_footer();	
				
	// Set up database.
	$db_conn_set = new class_db_connect_params();
	$db_conn_set->set_name(DATABASE::NAME);
	
	$db = new class_db_connection($db_conn_set);
	$query = new class_db_query($db);		
			
	// Record navigation.
	$obj_navigation_rec = new class_record_nav();	
	
	// Prepare redirect url with variables.
	$url_query	= new url_query;
	$url_query->set_data('action', $obj_navigation_rec->get_action());
	$url_query->set_data('id', $obj_navigation_rec->get_id());
		
	// User access.
	$access_obj = new class_access_status();
	$access_obj->get_settings()->set_authenticate_url(APPLICATION_SETTINGS::DIRECTORY_PRIME);
	$access_obj->set_redirect($url_query->return_url());
	
	$access_obj->verify();	
	$access_obj->action();
	
	// Start page cache.
	$page_obj = new class_page_cache();
	ob_start();
	
	// Initialize our data objects. This is just in case there is no table
	// data for any of the navigation queries to find, we are making new
	// records, or copies of records. It also has the side effect of enabling 
	// IDE type hinting.
	$_main_data = new mendeleev_class_area_data();	
		
	// Populate main data. 
			
	// Command action handling.	
	switch($obj_navigation_rec->get_action())
	{		
	
		default:		
		case RECORD_NAV_COMMANDS::NEW_BLANK:
		
			break;
			
		case RECORD_NAV_COMMANDS::NEW_COPY:			
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();			
			break;
			
		case RECORD_NAV_COMMANDS::LISTING:
			
			// Direct to listing.				
			//header('Location: inspection_list.php');
			break;
			
		case RECORD_NAV_COMMANDS::DELETE:						
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();
				
			// Call and execute delete SP.
			$query->set_sql('{call area_delete(@code = ?)}');			
			
			$query->set_params(array(array($_main_data->get_code(), SQLSRV_PARAM_IN)));
			$query->query();
			
			// Refrsh page to the previous record.				
			header('Location: '.$_SERVER['PHP_SELF']);			
				
			break;				
					
		case RECORD_NAV_COMMANDS::SAVE:
					
			// If this is set to false, data is not saved.
			$valid = TRUE;			
			
			// Stop errors in case someone tries a direct command link.
			if($obj_navigation_rec->get_command() != RECORD_NAV_COMMANDS::SAVE) break;
									
			// Save the record. Saving main record is straight forward. We’ll run the populate method on our 
			// main data object which will gather up post values. Then we can run a query to merge the values into 
			// database table. We’ll then get the id from saved record (since we are using a surrogate key, the ID
			// should remain static unless this is a brand new record). 
			
			// If necessary we will then save any sub records (see each for details).
			
			// Finally, we redirect to the current page using the freshly acquired id. That will ensure we have 
			// always an up to date ID for our forms and navigation system.			
		
			// Populate the object from post values.			
			$_main_data->populate_from_request();
			
			// Not used for accounts.
			$room_List = NULL;
			
			if(is_array($_main_data->get_area_list()) == TRUE)
			{
				$room_List = implode(', ', $_main_data->get_area_list());
			}
			else
			{
				$dialog_no_areas = TRUE;
			}
		
			// Call update stored procedure.
			$query->set_sql('{call mendeleev_area_update(@account	= ?,
													@update_type	= ?,
													@pi_name_f		= ?, 
													@pi_name_l 		= ?,														 
													@areas			= ?,
													@details		= ?,
													@department		= ?)}');
													
			$params = array(array($access_obj->get_account(), 			SQLSRV_PARAM_IN),
						array($_main_data->get_update_type(), 			SQLSRV_PARAM_IN),
						array($_main_data->get_pi_name_f(), 			SQLSRV_PARAM_IN),
						array($_main_data->get_pi_name_l(), 			SQLSRV_PARAM_IN),						
						array($_main_data->get_areas(),					SQLSRV_PARAM_IN),
						array($_main_data->get_details(),				SQLSRV_PARAM_IN),
						array($_main_data->get_department(),			SQLSRV_PARAM_IN));
			
			$query->set_params($params);
			$query->query();
			
			// Let's get account info fromt he active directory system. We'll need to put
			// names int our own database so we can control ordering of output.
			$account_lookup = new class_access_lookup();
			$account_lookup->lookup($access_obj->get_account());
			
			// Set up and send email alert.
				$address  = 'dvcask2@uky.edu';
													
				$subject = MAILING::SUBJECT;
				$body = 'An update has been posted to Mendeleev by '.$account_lookup->get_account_data()->get_name_l().', '.$account_lookup->get_account_data()->get_name_f().'. <a href="http://ehs.uky.edu/apps/mendeleev/area_update_list.php">Click here</a> to view details.';
						
				$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/html; charset=iso-8859-1";
				if(MAILING::FROM)	$headers[] = "From: ".MAILING::FROM;
				if(MAILING::BCC)	$headers[] = "Bcc: ".MAILING::BCC;
				if(MAILING::CC) 	$headers[] = "Cc: ".MAILING::CC;	
				
				// Run mail function.
				mail($address, MAILING::SUBJECT.' - Updated Posted', $body, implode("\r\n", $headers));
			
			// Repopulate main data object with results from merge query.
			//$query->get_line_params()->set_class_name('blair_class_area_data');
			//$_main_data = $query->get_line_object();
			
			// Now that save operation has completed, reload page using ID from
			// database. This ensures the ID is always up to date, even with a new
			// or copied record.
			header('Location: area_update_posted.php');
			
			break;			
	}
	
		
	// Populate main navigation with ID's from main data stored procedure.
	//$obj_navigation_rec->set_id_first($nav_first);
	//$obj_navigation_rec->set_id_previous($nav_previous);
	//$obj_navigation_rec->set_id_next($nav_next);
	//$obj_navigation_rec->set_id_last($nav_last);

	$obj_navigation_rec->generate_button_list();

	// List queries
		
		
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Area Details</title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
                
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        
        <!-- Place inside the <head> of your HTML -->
		<script type="text/javascript" src="http://ehs.uky.edu/libraries/vendor/tinymce/tinymce.min.js"></script>
        
        <style>
						
			.incident {
				font-size:larger;			
			}
			
			ul.checkbox  { 
				
			 	-webkit-column-count: auto;  				
				-moz-column-count: auto;				
			  column-count: auto;			 
			  margin: 0; 
			  padding: 0; 
			  margin-left: 20px; 
			  list-style: none;			  
			} 
			
			ul.checkbox li input { 
			  margin-right: .25em; 
			  cursor:pointer;
			} 
			
			ul.checkbox li { 
			  border: 1px transparent solid; 
			  display:inline-block;
			  width:12em;			  
			} 
			
			ul.checkbox li label { 
			  margin-left: ;
			  cursor:pointer;			  
			} 
			
		</style>
        
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $obj_navigation_main->get_markup_nav(); ?>                                                                                
            <div class="page-header">           
                <h1>Chemical Inventory Update</h1>
                <p class="lead">The Department of Homeland Security is requesting a current campus wide chemical inventory. Please complete the following form to verify that the chemical inventory for your lab is current and accurate in <a href="www.etrax.uky.edu/chematix">Chematix</a> or that you have emailed an updated spreadsheet of your inventory to <a href="mailto:mailto:trobert@uky.edu">Robert Thomas</a>.</p>
                <p class="lead"><a href="media/chematix_template.xlsx" target="_blank">Chematix Template</a></p>
                <p class="lead"><a href="media/chematix_user_guide.pdf" target="_blank">Chematix Quick Guide</a></p>
            </div>
            
            <?php 	if($dialog_no_areas === TRUE)
					{
			?>
            	<p class="bg-danger lead">You need to add at least one Building/Room before submitting.</p>
            <?php
					}
			?> 
                        
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<div class="form-group">
                	<div class="col-sm-12">
                		<?php echo $obj_navigation_rec->get_markup_cmd_save_block(); ?>
                	</div>
                </div>                
    			
                <div class="form-group">
                	<label class="control-label col-sm-2" for="">Update method:</label>
                	<div class="col-sm-10">
                    	<div class="radio">
                        	<label><input type="radio" name="update_type" value="1" required>Chematix Application</label>
                        </div>
                        <div class="radio">
                        	<label><input type="radio" name="update_type" value="2" required>E-mailed Spreadsheet</label>
                        </div>
                	</div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="pi_name_f">PI Name, First:</label>
                    <div class="col-sm-10">
                        <input 
                        	required
                            type	="text" 
                            class	="form-control"  
                            name	="pi_name_f" 
                            id		="pi_name_f" 
                            placeholder="PI's First Name." 
                            value="<?php //echo trim($_main_data->get_label()); ?>">
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="pi_name_l">PI Name, Last:</label>
                    <div class="col-sm-10">
                        <input 
                        	required
                            type	="text" 
                            class	="form-control"  
                            name	="pi_name_l" 
                            id		="pi_name_l" 
                            placeholder="PI's Last Name." 
                            value="<?php //echo trim($_main_data->get_label()); ?>">
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="pi_name_l">Department:</label>
                    <div class="col-sm-10">
                        <input 
                        	required
                            type	="text" 
                            class	="form-control"  
                            name	="department" 
                            id		="department" 
                            placeholder="Department" 
                            value="">
                    </div>
                </div> 
                           
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="areas">Building/Room:</label>
                    <div class="col-sm-10">
                        <input 
                        	required
                            type	="text" 
                            class	="form-control"  
                            name	="areas" 
                            id		="areas" 
                            placeholder="Type areas here." 
                            value="<?php //echo trim($_main_data->get_label()); ?>">
                    </div>
                </div>
                
                <div class="form-group">       
                    <label class="control-label col-sm-2" for="details">Notes:</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" rows="5" name="details" id="details"></textarea>
                    </div>
                </div>
                
                <!--Save button-->
                <div class="form-group">
                	<div class="col-sm-12">
                		<?php echo $obj_navigation_rec->get_markup_cmd_save_block(); ?>
                	</div>
                </div>              
            </form>
            
            <p>Questions regarding this process can be directed to <a href="mailto:mailto:trobert@uky.edu">Robert Thomas</a> or call <a href="tel:+8592574016">859-257-4016</a>.
            
            <?php echo $obj_navigation_main->get_markup_footer(); ?>
        </div><!--container-->        
	
    	<script src="../../libraries/javascript/options_update.js"></script>
		<script src="source/javascript/dc_guid.js"></script>
		<script>
        
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
            
            ga('create', 'UA-40196994-1', 'uky.edu');
            ga('send', 'pageview');
          
		  	// Textbox WYSIWYG
            tinymce.init({
            selector: "textarea",
			plugins: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});
			                          
        </script>
	</body>
</html>

<?php
	// Collect and output page markup.
	$page_obj->markup_from_cache();	
	$page_obj->output_markup();
?>