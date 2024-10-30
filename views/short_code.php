<style>
	.badge-danger{
		background: rgba(234,84,85,1);
		color: white;
		padding: 3px 10px;
		border-radius: 20px;
	}
</style>
<div class="wrap" id="listShortcodeElm">
	<div id="icon-users" class="icon32"></div>
	<h2>List Shortcode</h2>
	<div id="noticeCheck" class="notice notice-success">
	</div>
	<form id="posts-filter" method="get" action="" autocomplete="off">
		<input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>
		<?php $shortcodeTable->search_box('Search', 'shortcode' ); ?>
	</form>
	<?php $shortcodeTable->display(); ?>
</div>