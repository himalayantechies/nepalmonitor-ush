			</div>
		</div>
		<!-- / main body -->
<?php
	echo html::script($this->themes->js_url."media/js/jquery.session.js");
	echo html::stylesheet($this->themes->css_url."media/js/jQuery-popModal/popModal.css");
	echo html::script($this->themes->js_url."media/js/jQuery-popModal/popModal.js");
?>
	</div>
	<!-- / wrapper -->
	<!-- footer -->
	<div id="footer" class="clearingfix">

		<div id="underfooter"></div>

		<!-- footer content -->
		<div class="rapidxwpr floatholder">

			<!-- footer credits -->
			<div class="footer-credits">
				Powered by the &nbsp;
				<a href="http://www.ushahidi.com/">
					<img src="<?php echo url::file_loc('img'); ?>media/img/footer-logo.png" alt="Ushahidi" style="vertical-align:middle" />
				</a>
				&nbsp; Platform
			</div>
			<!-- / footer credits -->

			<!-- footer menu -->
			<div class="footermenu">
				<ul class="clearingfix">
					<li>
						<a class="item1" href="<?php echo url::site(); ?>">
							<?php echo Kohana::lang('ui_main.home'); ?>
						</a>
					</li>

					<?php if (Kohana::config('settings.allow_reports')): ?>
					<li>
						<a href="<?php echo url::site()."reports/submit"; ?>">
							<?php echo Kohana::lang('ui_main.submit'); ?>
						</a>
					</li>
					<?php endif; ?>
					
					<?php if (Kohana::config('settings.allow_alerts')): ?>
						<li>
							<a href="<?php echo url::site()."alerts"; ?>">
								<?php echo Kohana::lang('ui_main.alerts'); ?></a>
						</li>
					<?php endif; ?>

					<?php if (Kohana::config('settings.site_contact_page')): ?>
					<li>
						<a href="<?php echo url::site()."contact"; ?>">
							<?php echo Kohana::lang('ui_main.contact'); ?>
						</a>
					</li>
					<?php endif; ?>

					<?php
					// Action::nav_main_bottom - Add items to the bottom links
					Event::run('ushahidi_action.nav_main_bottom');
					?>
				</ul>

				<?php if ($site_copyright_statement != ''): ?>
	      		<p><?php echo $site_copyright_statement; ?></p>
		      	<?php endif; ?>
		      	
			</div>
			<!-- / footer menu -->


		</div>
		<!-- / footer content -->

	</div>
	<!-- / footer -->
	<div id="welcome" class="welcome" style="display:none">
		<div class="dialogModal_header"><?php echo Kohana::lang('ui_main.welcome_message.header');?></div>
		<div class="dialogModal_content">
	        <?php echo Kohana::lang('ui_main.welcome_message.content');?>
    	</div>
    	<div class="dialogModal_footer"><?php echo Kohana::lang('ui_main.welcome_message.footer');?></div>
	</div>
	<?php
	echo $footer_block;
	// Action::main_footer - Add items before the </body> tag
	Event::run('ushahidi_action.main_footer');

	//welcome popup
	?>
	<!-- <script type="text/javascript">
	$(document).ready(function(){
        if($.session.get('welcome') == undefined) {
			$('.welcome').dialogModal({
				topOffset : 25,
				top : 25,
				type : '',
				onOkBut : function() {},
				onCancelBut : function() {},
				onLoad : function(el, current) {},
				onClose: function() {
					$.session.set('welcome', true);
				},
				onChange : function(el, current) {
				}
			});
        }
    });
    </script> -->
</body>
</html>
