<div id="maxsite-content-importer" class="wrap">
	<h1>Importer From MaxSite</h1>
	<form method="post" action="options.php">
		<div>
			<label for="maxsite_url">
				<?php _e( 'MaxSite url', IFM_TEXT_DOMAIN ); ?>
			</label>
			<input type="url" id="maxsite_url" name="maxsite_url" placeholder="https://yourmaxsiteurl.com"/>
		</div>
		<div class="loader" style="display: none;">
			<img src="<?php echo IFM_PLUGIN_URL ?>/assets/img/loader-pacman.svg">
		</div>
		<div class="results"></div>
		<?php submit_button( __( 'Import Content' ), IFM_TEXT_DOMAIN ); ?>
	</form>
</div>
