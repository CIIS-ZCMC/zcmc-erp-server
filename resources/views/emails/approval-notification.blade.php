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

        h1, h2 {
            color: #0056b3;
        }

        p {
            margin: 10px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            margin: 5px 0;
        }

        .button {
            display: inline-block;
            background-color: #0056b3;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- Header Section with ZCMC and DOH Logos --}}
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

        {{-- Email Subject Line --}}
        <h2 style="text-align: center; margin: 10px 0 10px 0">{{ $data['subject'] }}</h2>
        <hr>

        {{-- Main Notification Message --}}
        <div style="margin: 20px 0;">
            <p>{{ $data['message'] }}</p>

            {{-- Status Badge --}}
            @php
                $statusColors = [
                    'Pending' => '#FFC107',
                    'Received' => '#4CAF50',
                    'Approved' => '#8BC34A',
                    'Returned' => '#F44336',
                    'Completed' => '#009688',
                    'default' => '#757575',
                ];

                $status = $data['status'] ?? 'Updated';
                $bgColor = $statusColors[$status] ?? $statusColors['default'];
                $textColor = in_array($status, ['Pending', 'Approved']) ? '#000' : '#fff';
            @endphp

            <div>
                <span class="status-badge" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
                    Status: {{ $status }}
                </span>
            </div>
        </div>

        {{-- Module Link (if available) --}}
        @if(isset($data['module_path']) && !empty($data['module_path']))
        <div style="margin: 20px 0;">
            <p>You can view this item in the system by clicking the button below:</p>
            <a href="{{ config('app.url') . '/' . $data['module_path'] }}" class="button">
                View in System
            </a>
        </div>
        @endif

        {{-- Context-specific Message --}}
        @if(isset($data['context']))
            <div style="margin: 20px 0;">
                @switch($data['context'])
                    @case('update_user')
                        <p>Thank you for using the ZCMC System. This is an automated notification about your recent transaction.</p>
                        @break

                    @case('update_next_approver')
                        <p>A transaction requires your attention. Please log in to the system to review and take appropriate action.</p>
                        @break

                    @case('returned_application')
                        <div style="border-left: 4px solid #F44336; padding: 10px; margin: 10px 0; background-color: #FFEBEE;">
                            <h3 style="color: #D32F2F; margin-top: 0;">Your AOP application has been returned</h3>
                            <p>Please review the feedback below and make the necessary revisions to your application.</p>

                            @if(isset($data['remarks']) && !empty($data['remarks']))
                            <div style="margin-top: 10px;">
                                <strong>Return Reason:</strong>
                                <p style="margin-top: 5px; padding: 8px; background-color: #FFF; border-radius: 4px;">{{ $data['remarks'] }}</p>
                            </div>
                            @endif

                            <p style="margin-top: 15px;">Please log in to the system to make the required changes and resubmit your application.</p>
                        </div>
                        @break

                    @case('update_all')
                        <p>This is a system-wide notification. No further action may be required from you at this time.</p>
                        @break

                    @default
                        <p>Thank you for using the ZCMC System.</p>
                @endswitch
            </div>
        @endif

        {{-- Show Remarks if present (for all contexts) --}}
        @if(isset($data['remarks']) && !empty($data['remarks']) && (!isset($data['context']) || $data['context'] !== 'returned_application'))
        <div style="margin: 20px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background-color: #f5f5f5;">
            <h3 style="margin-top: 0; color: #555;">Remarks:</h3>
            <p style="margin-bottom: 0;">{{ $data['remarks'] }}</p>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>This is an automated message from the ZCMC System. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Zamboanga City Medical Center. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
