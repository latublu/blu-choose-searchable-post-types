<?php
global $bluSearchablePostTypes;

$error = 0;
$errorMessage = '';
$updated = 0;
$updatedMessage = '';

if ( @$_POST['updateBluSearchAction'] == 'update'  ) 
{
	//echo "<br/>_POST: <pre>".print_r($_POST, true)."</pre>";
	
	// Validate
	
	if ( !isset($_POST['blusearch_allowed_post_types']) || !is_array($_POST['blusearch_allowed_post_types']) || !count($_POST['blusearch_allowed_post_types']) ) 
	{
		$error++;
	
		$errorMessage = 'At least one post type is required.';
	}
        
	if ( $error ) 
	{
		$errorMessage = 'Error updating settings - ' . $errorMessage;
	} 
	
	// Process
	
	if ( !$error && function_exists('update_option') ) 
	{
		$value = json_encode(array_values($_POST['blusearch_allowed_post_types']));
	
		if ( update_option('blusearch_allowed_post_types', $value) ) 
		{
			$updated++;
		} 
        
        if ( isset($_POST['blusearch_debug']) ) 
        {
        	if ( update_option('blusearch_debug',true) ) 
        	{
        	    $updated++;
        	} 
        }
        else
        {
        	if ( update_option('blusearch_debug',false) ) 
        	{
        	    $updated++;
        	} 
        }
        
        if ( $updated ) 
        {
        	$updatedMessage = 'Settings updated successfully.';
        } 
               
	}
}

$allowed_post_types_arr = $bluSearchablePostTypes->getAllowedPostTypes();

$blusearch_debug = $bluSearchablePostTypes->getDebug();

//echo "allowed_post_types_arr: <pre>".print_r($allowed_post_types_arr, true)."</pre>";

?>

<div class="wrap">
	
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	
		<h2>
			<?php echo $bluSearchablePostTypes->pluginShortName; ?> Settings
		</h2>
	
		<p>
		Choose which public post types are allowed in search results.
		</p>
		
		<?php
		if ( $error )
		{
		?>
			<div id="settings-error" class="error"> 
				<p>
					<strong><?php echo $errorMessage; ?></strong>
				</p>
			</div>
		<?php
		}
		elseif ( $updated )
		{
		?>
			<div id="settings-updated" class="updated"> 
				<p>
					<strong><?php echo $updatedMessage; ?></strong>
				</p>
			</div>
		<?php
		}
		?>
		
		<input type="hidden" name="updateBluSearchAction" value="update" />
		
		<table class="form-table">
			<tbody>
				
				<tr>
				<th scope="row">
					<label for="blusearch_allowed_post_types">Searchable Post Types</label>
				</th>
				<td>
					<?php
					$post_types = $bluSearchablePostTypes->getPublicPostTypes('objects');
					
					if ( is_array($post_types) && count($post_types) ) 
					{
					?>	
						<ul>
							
							<?php
							//echo "post_types: <pre>".print_r($post_types,true)."</pre>\n";
						
							$i = 0;
						
							foreach ($post_types as $post_type)
							{
							?>					
							
								<?php
								if ( in_array($post_type->name, $allowed_post_types_arr) )
								{
								?>
									<li><input type="checkbox" id="blusearch_allowed_post_types_<?php echo $i; ?>" name="blusearch_allowed_post_types[<?php echo $i; ?>]" value="<?php echo $post_type->name; ?>" checked="checked" /><?php echo $post_type->label; ?></li>
								<?php
								} else {
								?>
									<li><input type="checkbox" id="blusearch_allowed_post_types_<?php echo $i; ?>" name="blusearch_allowed_post_types[<?php echo $i; ?>]" value="<?php echo $post_type->name; ?>" /><?php echo $post_type->label; ?></li>
								<?php
								}
							
								$i++;
								?>
							
							<?php
							}
							?>	
										
						</ul>
					
					<?php
					} 
					?>					
					
					<p class="description">
					At least one post type is required.
					</p>
					
				</td>
				</tr>
				
				<tr>
				<th scope="row">
					<label for="blusearch_debug">Debug</label>
				</th>
				<td>
					<?php
					if ( $blusearch_debug == true )
					{
					?>
						<input type="checkbox" id="blusearch_debug" name="blusearch_debug" value="true" checked="checked" />
					<?php
					} else {
					?>
						<input type="checkbox" id="blusearch_debug" name="blusearch_debug" value="true" />
					<?php
					}
					?>
					
					<p class="description">
					If checked, debug output will be displayed at the top of the search results.
					</p>
					
				</td>
				</tr>
				
			</tbody>
		</table>

		<div class="submit">
			<input id="updateBluSearchSettings" name="updateBluSearchSettings" class="button button-primary" type="submit" value="<?php _e('Update Settings', 'BluSearch') ?>" />
		</div>
	
	</form>
	
 </div>
