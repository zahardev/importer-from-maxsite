<?php
if ( ! function_exists( 'add_action' ) ) {
	exit;
}
?>
<div id="maxsite-content-importer" class="wrap">
	<h1>Importer From MaxSite</h1>
    <?php if( function_exists('curl_version') ) : ?>
    <?php printf( __( 'Before importing, please make sure you installed <a target="_blank" href="%s">Export API plugin</a> on your MaxSite CMS site.', IFM_TEXT_DOMAIN ), 'https://github.com/zahardoc/export_api' ); ?>
    <br><br>
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
        <div class="results" style="display: none">
			<?php _e('Importing is in the progress..', IFM_TEXT_DOMAIN); ?>
        </div>

		<?php submit_button( __( 'Import Content' ), IFM_TEXT_DOMAIN ); ?>
	</form>
    <?php else : ?>
        <div><?php _e('You can not use this plugin: please enable curl module first!', IFM_TEXT_DOMAIN); ?></div>
    <?php endif; ?>
</div>
