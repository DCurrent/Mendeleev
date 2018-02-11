<?php 
	
	require(__DIR__.'/source/main.php');
	
	$dialog = NULL;
	
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
		
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Area Details</title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="../blair_dev - Copy/source/css/style.css" />
        <link rel="stylesheet" href="../blair_dev - Copy/source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        
        <!-- Place inside the <head> of your HTML -->
		<script type="text/javascript" src="http://ehs.uky.edu/libraries/vendor/tinymce/tinymce.min.js"></script>
        <script type="text/javascript">
        tinymce.init({
            selector: ".wysiwyg",
    plugins: [
        "advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste"
    ],
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});
        </script>
        
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
                <h1>Building/Room Entry</h1>
                <p>The Department of Homeland Security is requesting a current campus wide chemical inventory. Please complete the following form to verify that the chemical inventory for your lab is current and accurate in Chematix or that you have emailed an updated spreadsheet of your inventory to <a href="mailto:mailto:trobert@uky.edu">Robert Thomas</a>.
                    
            </div>
            
            <p class="bg-success lead">Your record has been submtted successfully. You may go <a href="area_update.php">back</a> and enter more buildings/rooms or close this page. Thank you!</span>
            
                        
            
            
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
        </script>
	</body>
</html>

<?php
	// Collect and output page markup.
	$page_obj->markup_from_cache();	
	$page_obj->output_markup();
?>