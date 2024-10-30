<div class="wrap">
    <h1>MasOffer Settings</h1>

    <!-- using admin_action_ . $_REQUEST['action'] hook in admin.php -->
    <form action="<?php echo admin_url( 'admin.php' ); ?>" method="post">
        <input type="hidden" name="action" value="masoffer_product_action" />
        <?php wp_nonce_field( 'update-info-mo_prod_' ); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th><label for="key">Publisher Key</label></th>
                <td><input name="key" id="key" type="text" value="<?php echo $data['key'] ?>" class="regular-text code"></td>
            </tr>
            <tr>
                <th><label for="key">Domain Affiliate</label></th>
                <td>
                    <select name="domain">
                        <?php foreach ($domains as $domain) { ?>
                            <option value="<?= $domain ?>" <?php if($data['domain'] === $domain){ ?> selected <?php } ?>><?= $domain ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="send_to_email">Send to email</label></th>
                <td><input name="send_to_email" id="send_to_email" type="text" value="<?php echo $data['send_to_email'] ?>" class="regular-text code" required></td>
            </tr>
            <tr>
                <th><label for="notice">Turn on email notice</label></th>
                <td><input name="notice" type="checkbox" value="true" <?php if($data['notice'] == 'true'){ echo 'checked';} ?> ></td>
            </tr>
            <tr>
                <th><label for="button_title">Button title (default)</label></th>
                <td><input name="button_title" id="button_title" type="text" value="<?php echo empty($data['button_title']) ? self::BUTTON_TITLE_DEFAULT : $data['button_title'] ?>" class="regular-text code" required></td>
            </tr>
            <tr>
                <th><label for="show_shop_logo">Shop logo (default)</label></th>
                <td><input name="show_shop_logo" type="checkbox" value="1" <?php if($data['show_shop_logo'] == 1 || strlen($data['show_shop_logo']) == 0){ echo 'checked';} ?> ></td>
            </tr>
            <tr>
                <th><label for="show_shop_name">Shop name (default)</label></th>
                <td><input name="show_shop_name" type="checkbox" value="1" <?php if($data['show_shop_name'] == 1 || strlen($data['show_shop_name']) == 0){ echo 'checked';} ?> ></td>
            </tr>
            <tr>
                <th><label for="show_price">Price (default)</label></th>
                <td><input name="show_price" type="checkbox" value="1" <?php if($data['show_price'] == 1 || strlen($data['show_price']) == 0){ echo 'checked';} ?> ></td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input class="button button-primary" type="submit" value="Save"/>
        </p>
    </form>
</div> <!-- end div.wrap -->

