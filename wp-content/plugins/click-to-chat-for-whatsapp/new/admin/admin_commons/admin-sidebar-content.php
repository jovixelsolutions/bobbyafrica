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
