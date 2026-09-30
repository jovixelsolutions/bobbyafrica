<?php
/**
 * Sidebar content - admin main page
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$othersettings = get_option( 'ht_ctc_othersettings' );

?>

<div class="sidebar-content">

	<?php if ( defined( 'HT_CTC_IS_NEW' ) && 'yes' === HT_CTC_IS_NEW ) { ?>
	<div class="col s12 m8 l12 xl12">
		<?php
		// New Admin Interface 2026 requires PRO version 2.21+.
		$is_pro_compatible = true;
		// if pro version is less than 2.21 then return false
		if ( defined( 'HT_CTC_PRO_VERSION' ) && version_compare( HT_CTC_PRO_VERSION, '2.21', '<' ) ) {
			$is_pro_compatible = false;
		}
		?>
		
		<?php if ( $is_pro_compatible ) { ?>
			<div style="background: #ffffff; padding: 28px 20px; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); margin-bottom: 30px; text-align: center; position: relative; overflow: hidden;">
				<div style="position: absolute; top: -15px; left: -15px; width: 80px; height: 80px; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%); border-radius: 50%; z-index: 0;"></div>
				<div style="font-size: 38px; margin-bottom: 12px; position: relative; z-index: 1;">✨</div>
				<h4 style="margin: 0 0 10px; font-weight: 800; color: #0f172a; font-size: 18px; letter-spacing: -0.025em; position: relative; z-index: 1;">The New Era is Here</h4>
				<p style="margin: 0 0 16px; font-size: 13.5px; color: #64748b; line-height: 1.6; position: relative; z-index: 1;">Experience our most advanced, lightning-fast dashboard yet. Built for the future of Click to Chat.</p>
				<div style="margin: 0 0 28px; display: flex; flex-direction: column; gap: 8px; align-items: center; position: relative; z-index: 1;">
					<div style="font-size: 12px; font-weight: 500; color: #475569; display: flex; align-items: center; gap: 6px;">
						<span style="color: #6366f1; font-weight: 800;">✓</span> Switch back easily
					</div>
					<div style="font-size: 12px; font-weight: 500; color: #475569; display: flex; align-items: center; gap: 6px;">
						<span style="color: #6366f1; font-weight: 800;">✓</span> No settings will be lost
					</div>
				</div>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=click-to-chat&admin_ui=2026' ), 'ht_ctc_switch_ui', '_htnonce' ) ); ?>" class="button" style="width: 100%; justify-content: center; height: 44px; line-height: 44px; font-size: 14px; border-radius: 12px; font-weight: 700; display: inline-flex; align-items: center; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: #fff; border: none; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; z-index: 1; letter-spacing: 0.01em; margin: 0;">Switch to 2026 UI</a>
			</div>
		<?php } else { ?>
			<div style="background: #f5f3ff; padding: 30px 20px; border: 1px solid #ddd6fe; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.1); margin-bottom: 30px; text-align: center; position: relative; overflow: hidden;">
				<div style="position: absolute; top: -10px; right: -10px; width: 80px; height: 80px; background: rgba(99, 102, 241, 0.05); border-radius: 50%; z-index: 0;"></div>
				<div style="font-size: 32px; margin-bottom: 12px; position: relative; z-index: 1;">💎</div>
				<h4 style="margin: 0 0 8px; font-weight: 700; color: #4338ca; font-size: 18px; letter-spacing: -0.01em; position: relative; z-index: 1;">New Dashboard Available</h4>
				<p style="margin: 0 0 18px; font-size: 13.5px; color: #4338ca; opacity: 0.8; line-height: 1.6; position: relative; z-index: 1;">A redesigned admin dashboard is ready. Update the PRO plugin to try it.</p>
				<div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin: 0 0 18px; position: relative; z-index: 1;">
					<span style="background: #fff; border: 1px solid #ddd6fe; border-radius: 10px; padding: 8px 14px; text-align: center;">
						<span style="display: block; font-size: 11px; font-weight: 600; color: #6366f1; text-transform: uppercase; letter-spacing: 0.05em;">Your PRO</span>
						<span style="display: block; font-size: 15px; font-weight: 700; color: #4338ca;"><?php echo defined( 'HT_CTC_PRO_VERSION' ) ? esc_html( HT_CTC_PRO_VERSION ) : '—'; ?></span>
					</span>
					<span style="color: #6366f1; font-size: 16px; font-weight: 700;">→</span>
					<span style="background: #fff; border: 1px solid #ddd6fe; border-radius: 10px; padding: 8px 14px; text-align: center;">
						<span style="display: block; font-size: 11px; font-weight: 600; color: #6366f1; text-transform: uppercase; letter-spacing: 0.05em;">Required</span>
						<span style="display: block; font-size: 15px; font-weight: 700; color: #4338ca;">2.21+</span>
					</span>
				</div>
				<p style="margin: 0 0 20px; font-size: 12px; color: #4338ca; opacity: 0.7; line-height: 1.6; position: relative; z-index: 1;">After updating, switch to the new dashboard from here. Your settings stay as they are.</p>
				<a href="https://holithemes.com/shop/" target="_blank" class="button" style="width: 100%; justify-content: center; background: #6366f1; border: none; color: #fff; height: 42px; line-height: 42px; font-size: 14px; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; position: relative; z-index: 1; transition: all 0.3s ease; margin: 0; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">Update PRO Plugin</a>
				<div style="margin: 18px 0 0; padding: 12px 14px; background: rgba(255, 255, 255, 0.6); border: 1px dashed #c4b5fd; border-radius: 10px; text-align: left; position: relative; z-index: 1;">
					<p style="margin: 0; font-size: 12px; color: #4338ca; opacity: 0.85; line-height: 1.6;">
						License expired and not renewing for now? <a href="https://holithemes.com/shop/download-click-to-chat-pro-compatible-version/" target="_blank" style="color: #4338ca; font-weight: 600;">Download the compatible PRO version</a> to use the new dashboard.
					</p>
				</div>
				<p style="margin: 20px 0 0; font-size: 12px; color: #4338ca; opacity: 0.7; position: relative; z-index: 1;">
					<a href="http://holithemes.com/plugins/click-to-chat/support" target="_blank" style="color: #4338ca; font-weight: 600; text-decoration: none; border-bottom: 1px solid rgba(67, 56, 202, 0.3); transition: all 0.2s ease;"><?php esc_html_e( 'Contact Support', 'click-to-chat-for-whatsapp' ); ?></a>
				</p>
			</div>
		<?php } ?>
	</div>
	<?php } ?>

	<div class="col s12 m8 l12 xl12">
		<div class="row">
			<ul class="collapsible popout ht_ctc_sidebar_contat">
				<li class="active">
					<div class="collapsible-header"><?php esc_html_e( 'Contact Us', 'click-to-chat-for-whatsapp' ); ?>
						<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
					</div>	
					<div class="collapsible-body">
						<p class="description" style="font-size:14px;line-height:1.4;margin:10px 0;">
							Got a question? 😊 We’d love to hear from you!
						</p>
						<?php
						if ( defined( 'HT_CTC_PRO_VERSION' ) ) {
							?>
							<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/support"> Click to Chat - Support</a></p>
							<?php
						} else {
							?>
							
							<!-- Click to Chat — Forum -->
							<p class="description"><a target="_blank" href="https://wordpress.org/support/plugin/click-to-chat-for-whatsapp/#new-topic-0">Contact Us</a></p>
							<?php
						}
						do_action( 'ht_ctc_ah_admin_sidebar_contact_details' );
						?>
					</div>	
				</li>
			</ul>
		</div>
	</div>

	<?php
	do_action( 'ht_ctc_ah_admin_sidebar_contact' );

	if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
		?>
		<div class="col s12 m8 l12 xl12">
			<div class="row">
				<ul class="collapsible popout ht_ctc_sidebar_pro">
					<li class="active">
						<div class="collapsible-header"><?php esc_html_e( 'PRO', 'click-to-chat-for-whatsapp' ); ?> FEATURES 
							<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
						</div>
					  
						<div class="collapsible-body">	
							<p class="description">📝 Form Filling</p>
							<p class="description">&emsp;🔤 Text, 📧 Email</p>
							<p class="description">&emsp;🔽 Select, 📄 Text Area</p>
							<p class="description">&emsp;📅 Date, 📆 Date & Time</p>
							<p class="description">&emsp;🌍 International Number</p>
							<p class="description">👥 Multi-Agent Support</p>
							<p class="description">&emsp;⏳ Custom Time Ranges</p>
							<p class="description">&emsp;🔒 Hide Offline Agents</p>
							<p class="description">&emsp;⏰ Show Next Available Time</p>
							<p class="description">🎲 Random Numbers</p>
							<p class="description">🌍 Country-Based Display</p>
							<p class="description">📊 Google Ads Conversion Tracking</p>
							<p class="description">� Meta Conversion Tracking</p>
							<p class="description">�🕒 Business Hours</p>
							<p class="description">&emsp;🔒 Hide When Offline</p>
							<p class="description">&emsp;📞 Offline Alternate Number</p>
							<p class="description">&emsp;✨ Offline Call-to-Action</p>
							<p class="description">⏲️ Display Triggers</p>
							<p class="description">&emsp;⏱️ Time Delay</p>
							<p class="description">&emsp;🖱️ Scroll Depth</p>
							<p class="description">🔄 Display Based On</p>
							<p class="description">&emsp;📅 Days of Week</p>
							<p class="description">&emsp;🕓 Time of Day</p>
							<p class="description">&emsp;👤 User Login Status</p>
							<p class="description">🌐 Dynamic variables for Webhooks</p>
							<p class="description">🔗 Custom URL</p>
							<p class="description">📍 Fixed/Absolute Position Types</p>
							<p class="description">👋 Greetings Actions</p>
							<p class="description">&emsp;⏰ Time-Based</p>
							<p class="description">&emsp;🖱️ Scroll-Based</p>
							<p class="description">&emsp;🖱️ Click-Based</p>
							<p class="description">&emsp;👁️ Viewport-Based</p>
							<p class="description">⚙️ Page-Level Settings</p>
							<p class="description">&emsp;🎨 Style adjustments</p>
							<p class="description">&emsp;⏲️ Time/Scroll-based triggers</p>
							<p class="description">&emsp;💬 Greetings Content</p>
							<p class="description">✨ More Features</p>

							<p class="description" style="text-align: center; position:sticky; bottom:2px; margin-top:20px;"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/pricing/" class="waves-effect waves-light btn" style="width: 100%;">PRO Version</a></p>

						</div>	
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

	?>


</div>
