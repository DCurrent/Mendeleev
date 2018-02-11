<?php 
		
	require(__DIR__.'/source/main.php');
	
	// Prepare redirect url with variables.
	$url_query	= new url_query;
		
	// User access.
	$access_obj = new \dc\stoeckl\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::DIRECTORY_PRIME);
	$access_obj->set_redirect($url_query->return_url());
	
	$access_obj->verify();	
	$access_obj->action();
	
	// Looking up account names.
	$account_lookup = new \dc\stoeckl\lookup();
	
	// Start page cache.
	$page_obj = new class_page_cache();
	ob_start();
		
	// Set up navigaiton.
	$navigation_obj = new class_navigation();
	$navigation_obj->generate_markup_nav();
	$navigation_obj->generate_markup_footer();	
	
	// Set up database.
	$db_conn_set = new class_db_connect_params();
	$db_conn_set->set_name(DATABASE::NAME);
	
	$db = new class_db_connection($db_conn_set);
	$query = new class_db_query($db);
		
	$paging = new class_paging();
	$paging->set_row_max(APPLICATION_SETTINGS::PAGE_ROW_MAX);	
	
	// Establish sorting object, set defaults, and then get settings
	// from user (if any).
	$sorting = new class_sort_control;
	$sorting->set_sort_field(1);
	$sorting->set_sort_order(SORTING_ORDER_TYPE::DECENDING);
	$sorting->populate_from_request();

	
	$query->set_sql('{call mendeleev_area_update_list(@page_current	= ?,
													@page_rows 		= ?,
													@update_from	= ?,
													@update_to		= ?,
													@sort_field		= ?,
													@sort_order		= ?)}');
											
	$page_last 	= NULL;
	$row_count 	= NULL;		
	
	$params = array(array($paging->get_page_current(),	SQLSRV_PARAM_IN),
					array($paging->get_row_max(), 		SQLSRV_PARAM_IN),
					array(NULL,							SQLSRV_PARAM_IN),
					array(NULL, 						SQLSRV_PARAM_IN),
					array($sorting->get_sort_field(),	SQLSRV_PARAM_IN),
					array($sorting->get_sort_order(),	SQLSRV_PARAM_IN));

	$query->set_params($params);
	$query->query();
	
	$query->get_line_params()->set_class_name('mendeleev_class_area_data');
	$_obj_data_main_list = $query->get_line_object_list();

	// --Paging
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('class_paging');
	
	//$_obj_data_paging = new class_paging();
	if($query->get_row_exists()) $paging = $query->get_line_object();
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?></title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $navigation_obj->get_markup_nav(); ?>                                                                                
            <div class="page-header">
                <h1>Area Updates</h1>
                <p>First level category list for audit questions.</p>
            </div>
          
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <caption></caption>
                    <thead>
                        <tr>
                            <th><a href="<?php echo $sorting->sort_url(1); ?>">Time <?php echo $sorting->sorting_markup(1); ?></a></th>
                            <th><a href="<?php echo $sorting->sort_url(2); ?>">Type <?php echo $sorting->sorting_markup(2); ?></a></th>
                            <th><a href="<?php echo $sorting->sort_url(3); ?>">Author <?php echo $sorting->sorting_markup(3); ?></a></th>
                            <th><a href="<?php echo $sorting->sort_url(5); ?>">PI <?php echo $sorting->sorting_markup(5); ?></a></th>
                            <th>Department</th>
                            <th>Areas</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                    <tbody>                        
                        <?php
							
                            if(is_object($_obj_data_main_list) === TRUE)
							{
								for($_obj_data_main_list->rewind(); $_obj_data_main_list->valid(); $_obj_data_main_list->next())
								{
									$update_text 	= NULL;
									$account_name 	= NULL;
															
									$_obj_data_main = $_obj_data_main_list->current();
									
									switch($_obj_data_main->get_update_type())
									{
										case 1:
											$update_text = 'Chematix';
											break;
										case 2:
											$update_text = 'Email';
											break;
										default:
											 $update_text = 'NA';
											 break;
									}
									
									//$account_lookup->lookup($_obj_data_main->get_account());
									//$account_name = $account_lookup->get_account_data()->get_name_l().', '.$account_lookup->get_account_data()->get_name_f();
									
                            ?>
                                        <tr>
                                            <td><?php if(is_object($_obj_data_main->get_log_update()) === TRUE) echo date(APPLICATION_SETTINGS::TIME_FORMAT, $_obj_data_main->get_log_update()->getTimestamp()); ?></td>
                                            <td><?php echo $update_text; ?></td>
                                            <td><a href="mailto:<?php echo $_obj_data_main->get_account(); ?>@uky.edu"><?php echo $_obj_data_main->get_account(); ?></a></td>
                                            <td><?php echo $_obj_data_main->get_pi_name_l().', '. $_obj_data_main->get_pi_name_f(); ?></td>
                                            <td><?php echo $_obj_data_main->get_department(); ?></td>
                                            <td><?php echo $_obj_data_main->get_areas(); ?></td>
                                            <td><?php echo $_obj_data_main->get_details(); ?></td>
                                            
                                            
                                        </tr>                                    
                            <?php								
                            	}
							}
                        ?>
                    </tbody>                        
                </table>  
            </div>
            
            <br>
			<?php 
				echo $paging->generate_paging_markup();
				echo $navigation_obj->get_markup_footer(); 
			?>
        </div><!--container-->        
    <script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
	  ga('create', 'UA-40196994-1', 'uky.edu');
	  ga('send', 'pageview');
	  
	  $(document).ready(function(){
		$('[data-toggle="tooltip"]').tooltip();
		});
		
		
	</script>
</body>
</html>

<?php
	// Collect and output page markup.
	$page_obj->markup_from_cache();	
	$page_obj->output_markup();
?>