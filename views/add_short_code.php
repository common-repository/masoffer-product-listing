<div class="wrap" id="addShortcodeElm">
    <div class="mo-loader"></div>
    <h1>Add shortcode</h1>
    <div class="notice notice-success">
        <p>This plugin only supports for Shopee, Tiki and Lazada.</p>
    </div>

    <div id="err" class="error" style="display: none;">
        <p>Add shortcode failed. Please try again!</p>
        <p id="messageError"></p>
    </div>
    <div class="notice notice-success" id="notice-success" style="display: none;">
        <p>Add shortcode success.</p>
    </div>

    <form id="addShortcodeForm" action="/wp-json/mo_get_product/v1/addShortcode" method="post" autocomplete="off">
        <table class="form-table">
            <tbody>
                <tr id="shortcodeElm" style="display: none;">
                    <th><label >Shortcode</label></th>
                    <td><input type="text" value="" class="regular-text" readonly></td>
                </tr>
                <tr>
                    <th><label for="name">Name</label></th>
                    <td><input name="name" id="name" type="text" value="" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="urls">Url</label></th>
                    <td colspan="3"><textarea name="urls" required></textarea></td>
                </tr>
                <tr>
                    <th><label for="aff_sub1">Aff_sub1</label></th>
                    <td><input name="aff_sub1" id="aff_sub1" type="text" value="" class="regular-text"></td>

                    <th><label for="aff_sub2">Aff_sub2</label></th>
                    <td><input name="aff_sub2" id="aff_sub2" type="text" value="" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="aff_sub3">Aff_sub3</label></th>
                    <td><input name="aff_sub3" id="aff_sub3" type="text" value="" class="regular-text"></td>

                    <th><label for="aff_sub4">Aff_sub4</label></th>
                    <td><input name="aff_sub4" id="aff_sub4" type="text" value="" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="key">Template</label></th>
                    <td>
                        <select name="type" class="regular-text">
                            <?php foreach ($template as $key => $val) { ?>
                                <option value="<?= $key ?>" <?php if(@$data['type'] === $key){ ?> selected <?php } ?>><?= $val ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <th><label for="button_title">Button title</label></th>
                    <td><input name="button_title" id="button_title" type="text" value="<?= $dataOption['button_title'] ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="show_shop_logo">Shop logo</label></th>
                    <td><input name="show_shop_logo" type="checkbox" value="1" <?php if($dataOption['show_shop_logo'] == 1 || strlen($dataOption['show_shop_logo']) == 0){ echo 'checked';} ?> ></td>

                    <th><label for="show_shop_name">Shop name</label></th>
                    <td><input name="show_shop_name" type="checkbox" value="1" <?php if($dataOption['show_shop_name'] == 1 || strlen($dataOption['show_shop_name']) == 0){ echo 'checked';} ?> ></td>
                </tr>
                <tr>
                    <th><label for="show_price">Price</label></th>
                    <td><input name="show_price" type="checkbox" value="1" <?php if($dataOption['show_price'] == 1 || strlen($dataOption['show_price']) == 0){ echo 'checked';} ?> ></td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input class="button button-primary" id="addShortcodeSubmit" type="submit" value="Save"/>
        </p>
    </form>
</div>
