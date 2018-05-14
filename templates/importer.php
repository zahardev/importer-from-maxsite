<?php
if ( ! function_exists( 'add_action' ) ) {
	exit;
}
?>
<div id="maxsite-content-importer" class="wrap">
    <h1><?php _e( 'Importer From MaxSite', IFM_TEXT_DOMAIN ); ?></h1>
	<?php if ( empty( $errors ) ) : ?>
		<?php printf(
			__( 'Before importing, please make sure you installed <a target="_blank" href="%s">Export API plugin</a> on your MaxSite CMS site and <a target="_blank" href="%s">Advanced Custom Fields</a> plugin on you WordPress site.', IFM_TEXT_DOMAIN ),
			'https://github.com/zahardoc/export_api/',
			'https://wordpress.org/plugins/advanced-custom-fields/'
		); ?>
        <br><br>
        <form method="post" action="options.php">
            <div>
                <label for="maxsite_url">
					<?php _e( 'MaxSite url', IFM_TEXT_DOMAIN ); ?>
                </label>
                <input type="url" id="maxsite_url" name="maxsite_url"
                       placeholder="https://yourmaxsiteurl.com"/>
            </div>
            <div class="loader" style="display: none;">
                <img src="<?php echo IFM_PLUGIN_URL ?>/assets/img/loader-pacman.svg">
            </div>
            <div class="results" style="display: none">
				<?php _e( "Importing.. Please don't close the page until it is finished!", IFM_TEXT_DOMAIN ); ?>
            </div>

			<?php submit_button( __( 'Import Content', IFM_TEXT_DOMAIN ) ); ?>
        </form>
	<?php else : ?>
		<?php foreach ( $errors as $error ) : ?>
            <div class="error"><?php echo $error; ?></div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
