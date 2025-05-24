<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['subject'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            width: 90%;
            text-align: center;
            display: table;
            margin: auto;
            padding-bottom: 10px;
        }

        .header-container {
            display: table-row;
        }

        .header-item {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        #zcmclogo,
        #dohlogo {
            height: 65px;
        }

        .header-text {
            text-align: center;
        }

        .header-text h5 {
            margin: 0;
        }

        .logo-container {
            width: 20%;
        }

        .text-container {
            width: 60%;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        h1 {
            color: #0056b3;
        }

        p {
            margin: 10px 0;
        }

        .details {
            margin: 20px 0;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
        }

        .details th,
        .details td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>

<body>


<div class="container">
    <header>
        <div class="header-container">
            <div class="header-item logo-container">
                <img src="https://drive.google.com/uc?export=view&id=1vWRCpAcExxR2tvvdZGF0-10nOHnVlDIq" width="60"
                     alt="ZCMC Logo">
            </div>
            <div class="header-item text-container header-text">
                <span>Republic of the Philippines</span>
                <h5>ZAMBOANGA CITY MEDICAL CENTER</h5>
                <span>Dr. Evangelista Street, Sta. Catalina, Zamboanga City</span>
            </div>
            <div class="header-item logo-container">
                <img src="https://drive.google.com/uc?export=view&id=1fP_1RYEeQHLmcNyXZePjVokSDbiIVQRM"
                     width="75" alt="DOH Logo">
            </div>
        </div>
    </header>

    <h2 style="text-align: center; margin: 10px 0 10px 0">{{ $data['subject'] }}</h2>
    <hr>
{{--    <p>--}}
{{--        @if ($data['context'] === 'request' || $data['context'] === 'update_user')--}}
{{--            Dear <strong>{{ $data['requester_name'] }}</strong>,--}}
{{--        @elseif ($data['context'] === 'update_next_user')--}}
{{--            Dear <strong>{{ $data['next_office_employee_name'] }}</strong>--}}
{{--        @endif--}}
{{--    </p>--}}

{{--    <p>--}}
{{--        @if ($data['context'] === 'request')--}}
{{--            This is to notify you that your request has been submitted successfully and is now--}}
{{--            <strong>{{ $data['status'] }}</strong>. The request will be reviewed and approved by--}}
{{--            <strong>{{ $data['next_office_area_code'] }}</strong> office. Please wait for further updates.--}}
{{--        @elseif ($data['context'] === 'update_user')--}}
{{--            This is to notify you that the status of your transaction has been updated to--}}
{{--            <strong>{{ $data['status'] }}</strong>.--}}
{{--        @elseif ($data['context'] === 'update_next_user')--}}
{{--            This is to notify you that a request from <strong>{{ $data['requester_name'] }}</strong> from the--}}
{{--            <strong>{{ $data['requester_area'] }}</strong> area is submitted and is now waiting for your review and--}}
{{--            approval.--}}
{{--        @endif--}}
{{--    </p>--}}

    <div class="details">
        <h4>This is a test notification</h4>
    </div>

    <div class="footer">
        <p style="margin: 0;">Best regards,</p>
        <p style="margin: 0;">IISU</p>
        <p style="margin: 0;">Innovation and Information Systems Unit</p>
    </div>
</div>
</body>

</html>
