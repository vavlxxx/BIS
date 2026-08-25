<?php
if (!defined('ABSPATH')) {
    exit;
}

$site_name = isset($site_name) ? (string) $site_name : get_bloginfo('name');
$site_url = isset($site_url) ? (string) $site_url : home_url('/');
$type_label = isset($type_label) ? (string) $type_label : 'Новая заявка';
$request_owner = isset($request_owner) ? (string) $request_owner : 'коллеги';
$accent_value = isset($accent_value) ? (string) $accent_value : $type_label;
$admin_request_url = isset($admin_request_url) ? (string) $admin_request_url : admin_url();
$detail_rows = isset($detail_rows) && is_array($detail_rows) ? $detail_rows : array();
$footer_note = isset($footer_note) ? (string) $footer_note : '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($type_label); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f3f5f8;font-family:Arial,sans-serif;color:#111827;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f5f8;margin:0;padding:24px 0;width:100%;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:720px;width:100%;">
                    <tr>
                        <td style="padding:0 16px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#20242a;border-radius:0px;overflow:hidden;">
                                <tr>
                                    <td style="padding:40px 40px 24px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="height:56px;">
                                                    <img style="max-height:56px;" src="<?php echo get_template_directory_uri(); ?>/assets/img/bis-logo-white.png"/>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 40px 16px;color:#ffffff;font-size:18px;line-height:1.5;font-weight:700;">
                                        Здравствуйте!
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 40px 24px;color:#ffffff;font-size:22px;line-height:1.2;font-weight:700;">
                                        Новая заявка: <?php echo esc_html($type_label); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 40px 12px;color:#c7d0d9;font-size:15px;line-height:1.7;">
                                        Ниже собраны все данные из формы.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 40px 0;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
                                            <?php foreach ($detail_rows as $row) : ?>
                                                <?php
                                                $label = isset($row['label']) ? (string) $row['label'] : '';
                                                $value = isset($row['value']) ? (string) $row['value'] : '';
                                                $is_multiline = !empty($row['multiline']);
                                                if ('' === trim($value)) {
                                                    continue;
                                                }
                                                ?>
                                                <tr>
                                                    <td style="padding:14px 0;border-bottom:1px solid #323840;vertical-align:top;width:180px;color:#8f9aa7;font-size:13px;line-height:1.5;text-transform:uppercase;letter-spacing:0.08em;">
                                                        <?php echo esc_html($label); ?>
                                                    </td>
                                                    <td style="padding:14px 0;border-bottom:1px solid #323840;color:#ffffff;font-size:15px;line-height:1.7;">
                                                        <?php if ($is_multiline) : ?>
                                                            <?php echo wp_kses_post(nl2br(esc_html($value))); ?>
                                                        <?php else : ?>
                                                            <?php echo esc_html($value); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:32px 40px 40px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="border-left:2px solid #3b98ff;padding-left:18px;">
                                                    <div style="color:#ffffff;font-size:15px;line-height:1.6;font-weight:700;">С уважением,</div>
                                                    <div style="color:#c7d0d9;font-size:15px;line-height:1.6;">сайт <?php echo esc_html($site_name); ?></div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#ffffff;">
                                <tr>
                                    <td style="padding:32px 40px 14px;color:#374151;font-size:12px;line-height:1.8;">
                                        <?php echo esc_html($footer_note); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 40px 40px;color:#6b7280;font-size:14px;line-height:1.7;">
                                        Сайт: <a href="<?php echo esc_url($site_url); ?>" style="color:#111827;text-decoration:underline;"><?php echo esc_html($site_url); ?></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
