

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        /* Inline styles for email compatibility */
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin-right: 3px;
            margin-left: 0px;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            margin-left: -5px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            background-color: #1d4ed8; /* Blue button color */
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-top: 20px;
            line-height: 1.5;
        }
        .button:hover {
            background-color: #1e40af; /* Darker blue on hover */
        }
        .text-center {
            text-align: center;
        }
        .mt-4 {
            margin-top: 1rem;
        }
        /* Responsive design adjustments */
        @media only screen and (max-width: 600px) {
            .button {
                font-size: 14px;
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>
    <table class="container" role="presentation" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <table class="card" role="presentation" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td>
                            <h1 style="font-size: 24px; color: #333333; margin: 0 0 20px 0;">Reset Your Password</h1>
                            <p style="font-size: 16px; color: #555555; margin: 0;">Hello,</p>
                            <p style="font-size: 16px; color: #555555; margin: 0;">Click the button below to reset your password. If you did not request a password reset, please ignore this email.</p>
                            <a href="{{ $url }}" class="button">Reset Password</a>
                            <p style="font-size: 14px; color: #888888; margin-top: 20px;">If you have any questions, feel free to contact our support team at <a href="mailto:chibuike@innoblog.com.ng" style="color: #1d4ed8;">chibuike@innoblog.com.ng</a>.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
