			</div>
		</div>
		<!-- / main body -->

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
	<div id="welcome" style="display:none">
        <p> <?php echo Kohana::lang('ui_main.welcome_message');?> </p>
    <div align="center">
        <a href="https://docs.google.com/forms/d/e/1FAIpQLSfXtNU5oYJR1ty7ufQibBvqcs-CHwSIXU9UIBradASmVIKSjw/viewform" target="_blank">Click here to fill the form</a> 
    </div>
	</div>
	<?php
	echo $footer_block;
	// Action::main_footer - Add items before the </body> tag
	Event::run('ushahidi_action.main_footer');

	//welcome popup
	echo html::script($this->themes->js_url."media/js/jquery.session.js");
	echo html::stylesheet($this->themes->css_url."media/js/jQuery-popModal/popModal.css");
	echo html::script($this->themes->js_url."media/js/jQuery-popModal/popModal.js");
	?>
	<script type="text/javascript">
	$(window).load(function(){
        if($.session.get('welcome') == undefined) {
           	$('#welcome').notifyModal({
				duration : -1,
				placement : 'center',
				onTop : true,
				});
           	$.session.set('welcome', true);
        }
    });
    </script>
</body>
</html>
