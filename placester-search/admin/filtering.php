<?php extract(array('filters' => PL_Global_Filters::get_global_filters())); ?>
<?php $_POST = $filters; ?>

	<div class="settings_option_wrapper">
		<div id="global_filter_wrapper" class="<?php echo !empty($filters) ? 'filters_active' : '' ?>">
			<div class="header-wrapper">
				<h2>Global Listing Search Filters</h2>
				<div class="clear"></div>
				<a class="button-secondary" id="remove_global_filters" >Remove All Filters</a>
				<div class="clear"></div>
				<?php if (!empty($filters)): ?>
					<div class="global_filter_active">Global Filters are Active!</div>
				<?php endif ?>
				<div id="global_filter_message_remove"></div>
			</div>
			<div class="clear"></div>
			<p><p>Global listing search filters limit all the search results returned to your website. This is helpful if you have listings of many different types or locations created but only want this website to display a subset of them. For example, to only show properties in Boston.</p>
			<div class="global_filters tagchecklist">
				<?php if (!empty($filters)): ?>
					<p class="label">Active Filters:</p>	
				<?php endif ?>
				<form action="" id="active_filters">
					<?php PL_Global_Filters::display_global_filters(); ?>
				</form>	
			</div>
			<div class="clear"></div>
			<div class="search_filter_content">
				<div class="global_filter_col">
					<?php PL_Global_Filters::get_listing_attributes(); ?>	
				</div>
				<div class="global_filter_col form_item">
					<form action="" id="global_filter_form">
						<?php PL_Form::generate_form( PL_Config::PL_API_LISTINGS('get', 'args'), array('method'=>'POST', 'include_submit' => false, 'wrap_form' => false, 'echo_form' => true, 'title' => false, 'id' => 'global_filter_form', 'textarea_as_text' => true ) ); ?>		
					</form>
				</div>
				<div class="global_filter_col filter_button">
					<a class="button-secondary" id="add-single-filter">Add Filter to Search</a>	
				</div>
				<div class="global_filter_col" id="global_filter_message"></div>
			</div>	
		</div>
	</div>
