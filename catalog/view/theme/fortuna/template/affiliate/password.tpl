<?php echo $header; ?>

	<?php echo $content_top; ?>
	
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
			<?php echo $breadcrumb['separator']; ?>
			<a href="<?php echo $breadcrumb['href']; ?>">
				<?php echo $breadcrumb['text']; ?>
			</a>
		<?php } ?>
	</div>

	<?php 
	
	if ($column_left || $column_right) { $main = "span9"; } 
	else { 	$main = "span12"; } 

	?>

	<div class="row">

		<?php echo $column_left; ?>

		<section id="maincontent" class="<?php echo $main; ?>" role="main">

			<div class="mainborder">

				<?php if ($column_left) { ?>
					<div id="toggle_sidebar"></div>
				<?php } ?>

				<header class="heading">
					<h1 class="page-header"><?php echo $heading_title; ?></h1>
				</header>

				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="password-form" class="form-horizontal">
			
					<fieldset class="subheading">
						
						<legend><?php echo $text_password; ?></legend>

						<div class="control-group">
							<label for="password" class="control-label">
								<span class="req_mark">*</span> <?php echo $entry_password; ?>
							</label>
							<div class="controls">
								<input type="password" name="password" id="password" value="<?php echo $password; ?>" title="<?php echo $this->language->get('error_password'); ?>" class="password span3" required />
								<?php if ($error_password) { ?>
									<span class="error"><?php echo $error_password; ?></span>
								<?php } ?>
								</div>
						</div>

						<div class="control-group">
							<label for="confirm" class="control-label">
								<span class="req_mark">*</span> <?php echo $entry_confirm; ?>
							</label>
							<div class="controls">
								<input type="password" name="confirm" id="confirm" value="<?php echo $confirm; ?>"  title="<?php echo $this->language->get('error_password'); ?>" class="password span3" required />
								<?php if ($error_confirm) { ?>
									<span class="error"><?php echo $error_confirm; ?></span>
								<?php } ?>
								</div>
						</div>

					</fieldset>

					<div class="form-actions">

						<a href="<?php echo $back; ?>" class="btn"><?php echo $button_back; ?></a>
						<input type="submit" value="<?php echo $button_continue; ?>" class="btn btn-inverse" />

					</div>
				
				</form>

			</div>
		
		</section> <!-- #maincontent -->

		<?php echo $column_right; ?>

	</div> <!-- .row -->

	<?php echo $content_bottom; ?>

<?php echo $footer; ?>