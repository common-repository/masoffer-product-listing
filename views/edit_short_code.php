<div class="wrap" id="editShortcodeElm">
    <div class="mo-loader"></div>
	<h1>Edit shortcode</h1>
    <div class="notice notice-success">
        <p>This plugin only supports for Shopee, Tiki and Lazada.</p>
    </div>
	<div id="err" class="error" style="display: none;">
		<p>Edit shortcode failed. Please try again!</p>
		<p id="messageError"></p>
	</div>
	<div class="notice notice-success" id="notice-success" style="display: none;">
		<p>Edit shortcode success.</p>
	</div>

	<form id="editShortcodeForm" action="/wp-json/mo_get_product/v1/editShortcode" method="post" autocomplete="off">
        <input type="hidden" name="id" value="<?= $shortcodeId ?>">
		<table class="form-table">
			<tbody>
				<tr>
					<th><label >Shortcode</label></th>
					<td><input type="text" value="[mo_product_listing id=<?= $shortcodeId ?>]" class="regular-text" readonly></td>
				</tr>
				<tr>
					<th><label for="name">Name</label></th>
					<td><input name="name" id="name" type="text" value="<?= $shortcodeInfo[0]['name'] ?>" class="regular-text" required></td>
				</tr>
                <tr>
					<th><label for="urls">Url</label></th>
					<td colspan="3"><textarea name="urls" style="height: 250px;width: 100%" required><?= $urls ?></textarea></td>
				</tr>
                <?php if(!empty($urlErrors)){ ?>
				<tr class="urlErrorClass">
					<th><label style="color: rgba(234,84,85,1)">Url Error</label></th>
					<td>
						<?php foreach ($urlErrors as $val) { ?>
							<input value="<?= $val ?>" type="text" class="regular-text">
						<?php } ?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th><label for="aff_sub1">Aff_sub1</label></th>
					<td><input name="aff_sub1" id="aff_sub1" type="text" value="<?= $shortcodeInfo[0]['aff_sub1'] ?>" class="regular-text" ></td>

					<th><label for="aff_sub2">Aff_sub2</label></th>
					<td><input name="aff_sub2" id="aff_sub2" type="text" value="<?= $shortcodeInfo[0]['aff_sub2'] ?>" class="regular-text" ></td>
				</tr>
				<tr>
					<th><label for="aff_sub3">Aff_sub3</label></th>
					<td><input name="aff_sub3" id="aff_sub3" type="text" value="<?= $shortcodeInfo[0]['aff_sub3'] ?>" class="regular-text" ></td>

					<th><label for="aff_sub4">Aff_sub4</label></th>
					<td><input name="aff_sub4" id="aff_sub4" type="text" value="<?= $shortcodeInfo[0]['aff_sub4'] ?>" class="regular-text" ></td>
				</tr>
				<tr>
					<th><label for="key">Template</label></th>
					<td>
						<select name="type">
							<?php foreach ($template as $key => $val) { ?>
								<option value="<?= $key ?>" <?php if($shortcodeInfo[0]['type'] == $key){ ?> selected <?php } ?>><?= $val ?></option>
							<?php } ?>
						</select>
					</td>
					<th><label for="button_title">Button title</label></th>
					<td><input name="button_title" id="button_title" type="text" value="<?= empty($shortcodeInfo[0]['button_title']) ? $dataOption['button_title'] : $shortcodeInfo[0]['button_title'] ?>" class="regular-text" required></td>
				</tr>
				<tr>
					<th><label for="show_shop_logo">Shop logo</label></th>
					<td><input name="show_shop_logo" type="checkbox" value="1"
                            <?php if($shortcodeInfo[0]['show_shop_logo'] == 1 || ($shortcodeInfo[0]['show_shop_logo'] == null && $dataOption['show_shop_logo'] == 1))
                            { echo 'checked';} ?>
						>
					</td>

					<th><label for="show_shop_name">Shop name</label></th>
					<td><input name="show_shop_name" type="checkbox" value="1"
							<?php if($shortcodeInfo[0]['show_shop_name'] == 1 || ($shortcodeInfo[0]['show_shop_name'] == null && $dataOption['show_shop_name'] == 1))
							{ echo 'checked';} ?>
						>
					</td>
				</tr>
				<tr>
					<th><label for="show_price">Price</label></th>
					<td><input name="show_price" type="checkbox" value="1"
                            <?php if($shortcodeInfo[0]['show_price'] == 1 || ($shortcodeInfo[0]['show_price'] == null && $dataOption['show_price'] == 1))
                            { echo 'checked';} ?>
						>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input class="button button-primary" type="submit" value="Save"/>
		</p>
	</form>
</div>
