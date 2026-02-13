<?php
function getEmailTemplate($title, $content) {
    // A professional, responsive email template
    // Uses inline styles for maximum compatibility
    $year = date('Y');

    return <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>$title</title>
<style>
    /* Reset styles */
    body { margin: 0; padding: 0; min-width: 100%; width: 100% !important; height: 100% !important; }
    body, table, td, div, p, a { -webkit-font-smoothing: antialiased; text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; line-height: 100%; }
    table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important; border-spacing: 0; }
    img { border: 0; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
    #outlook a { padding: 0; }
    .ReadMsgBody { width: 100%; } .ExternalClass { width: 100%; }
    .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }

    /* Custom Fonts */
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

    /* Responsive */
    @media screen and (max-width: 480px) {
        .mobile-padding { padding-left: 10px !important; padding-right: 10px !important; }
        .mobile-hide { display: none !important; }
        .mobile-center { text-align: center !important; }
        .mobile-full-width { width: 100% !important; max-width: 100% !important; direction: ltr !important; }
    }
</style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Roboto', Helvetica, Arial, sans-serif;">

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f4f4;">
    <tr>
        <td align="center" style="padding: 20px 0;">
            <!-- Logo -->
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="mobile-full-width" style="max-width: 600px;">
                <tr>
                    <td align="center" style="padding: 0 0 20px 0;">
                        <a href="https://myielts.skiloholic.com" target="_blank" style="text-decoration: none;">
                            <img src="https://myielts.skiloholic.com/assets/images/logo.png" alt="MyIELTS" width="150" style="display: block; font-family: Helvetica, Arial, sans-serif; color: #333333; font-size: 16px;" border="0">
                        </a>
                        <!-- Fallback Text if Logo doesn't load/exist -->
                        <div style="font-size: 24px; font-weight: bold; color: #d32f2f; margin-top: 5px;">MyIELTS</div>
                    </td>
                </tr>
            </table>

            <!-- Main Content Card -->
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="mobile-full-width" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden;">
                <!-- Header Stripe -->
                <tr>
                    <td align="center" style="background-color: #d32f2f; padding: 5px 0;"></td>
                </tr>

                <!-- Content Body -->
                <tr>
                    <td align="left" style="padding: 40px 30px;" class="mobile-padding">
                        <h1 style="font-size: 24px; font-weight: bold; margin: 0 0 20px 0; color: #333333;">$title</h1>

                        <div style="font-size: 16px; line-height: 1.6; color: #555555;">
                            $content
                        </div>
                    </td>
                </tr>

                <!-- Call to Action / Footer of Card -->
                <tr>
                    <td align="center" style="padding: 0 30px 40px 30px;" class="mobile-padding">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="center" style="padding-top: 20px; border-top: 1px solid #eeeeee;">
                                    <p style="font-size: 14px; color: #888888; line-height: 1.4; margin: 0;">
                                        Requires assistance? Contact us at <a href="mailto:support@skiloholic.com" style="color: #d32f2f; text-decoration: none;">support@skiloholic.com</a>
                                        <br>
                                        WhatsApp: +8801724413624
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Footer -->
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="mobile-full-width" style="max-width: 600px;">
                <tr>
                    <td align="center" style="padding: 20px 0 30px 0; font-size: 12px; color: #999999; line-height: 1.4;">
                        <p style="margin: 0;">&copy; $year MyIELTS - Powered by Skiloholic. All rights reserved.</p>
                        <p style="margin: 5px 0 0 0;">Dhaka, Bangladesh</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
EOT;
}
?>