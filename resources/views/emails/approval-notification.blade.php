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
        <div style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <p style="font-size: 16px; line-height: 1.6; color: #333; margin-bottom: 20px;">{{ $data['message'] }}</p>

            {{-- Status Badge --}}
            @php
                $statusColors = [
                    'pending' => '#FFC107',
                    'received' => '#4CAF50',
                    'approved' => '#8BC34A',
                    'returned' => '#F44336',
                    'completed' => '#009688',
                    'default' => '#757575',
                ];

                $status = $data['status'] ?? 'updated';
                $bgColor = $statusColors[$status] ?? $statusColors['default'];
                $textColor = in_array($status, ['pending', 'approved']) ? '#000' : '#fff';
            @endphp

            <div>
                <span class="status-badge" style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 14px; letter-spacing: 0.5px; background-color: {{ $bgColor }}; color: {{ $textColor }};">
                    Status: {{ ucfirst($status) }}
                </span>
            </div>
        </div>

        {{-- Application Flow Information --}}
        @if(isset($data['current_area']) || isset($data['next_area']) || isset($data['stage']))
        <div style="margin: 20px auto; max-width: 600px; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <h3 style="margin-top: 0; color: #0056b3; font-size: 18px; border-bottom: 2px solid #eaeaea; padding-bottom: 10px; margin-bottom: 20px; text-align: center;">Approval Flow Information</h3>

            {{-- Area information displayed in a horizontal 3-column layout with proper spacing --}}
            <table style="width: 100%; border-spacing: 0; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <td style="width: 33%; padding: 5px 10px; text-align: left; vertical-align: top;">
                        <p style="color: #666; font-size: 16px; margin: 0 0 5px 0;">Current Area</p>
                        @if(isset($data['current_area']))
                        <div style="color: #333; font-weight: 500; font-size: 16px; padding: 8px; background-color: #f5f5f5; border-radius: 4px;">{{ $data['current_area'] }}</div>
                        @else
                        <div style="color: #333; font-weight: 500; font-size: 16px; padding: 8px; background-color: #f5f5f5; border-radius: 4px;">-</div>
                        @endif
                    </td>
                    <td style="width: 33%; padding: 5px 10px; text-align: left; vertical-align: top;">
                        <p style="color: #666; font-size: 16px; margin: 0 0 5px 0;">Next Area</p>
                        @if(isset($data['next_area']) && $data['next_area'] !== null)
                        <div style="color: #333; font-weight: 500; font-size: 16px; padding: 8px; background-color: #f5f5f5; border-radius: 4px;">{{ $data['next_area'] }}</div>
                        @else
                        <div style="color: #333; font-weight: 500; font-size: 16px; padding: 8px; background-color: #f5f5f5; border-radius: 4px;">-</div>
                        @endif
                    </td>
                    <td style="width: 33%; padding: 5px 10px; text-align: left; vertical-align: top;">
                        <p style="color: #666; font-size: 16px; margin: 0 0 5px 0;">Current Stage</p>
                        @if(isset($data['stage']))
                        <div style="color: #333; font-weight: 500; font-size: 16px; padding: 8px; background-color: #f5f5f5; border-radius: 4px;">{{ $data['stage'] }}</div>
                        @else
                        <div style="color: #333; font-weight: 500; font-size: 16px; padding: 8px; background-color: #f5f5f5; border-radius: 4px;">-</div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Approval Progress with simple dots for active stages --}}
            @php
                // Initialize activeStage with default values (all false)
                $activeStage = [
                    'planning' => false,
                    'division' => false,
                    'omcc' => false,
                    'final' => false
                ];

                // Determine approval flow stage for visualization
                $stage = 'init';
                if (isset($data['context'])) {
                    if ($data['context'] === 'final_approval') {
                        $stage = 'final';
                    } elseif (isset($data['current_area']) && stripos($data['current_area'], 'planning') !== false) {
                        $stage = 'planning_unit';
                    } elseif (isset($data['current_area']) && stripos($data['current_area'], 'division') !== false) {
                        $stage = 'division_chief';
                    } elseif (isset($data['current_area']) && stripos($data['current_area'], 'medical center chief') !== false ||
                             isset($data['current_area']) && stripos($data['current_area'], 'omcc') !== false) {
                        $stage = 'omcc';
                    }

                    // Update activeStage based on current stage
                    $activeStage = [
                        'planning' => $stage == 'planning_unit' || $stage == 'division_chief' || $stage == 'omcc' || $stage == 'final',
                        'division' => $stage == 'division_chief' || $stage == 'omcc' || $stage == 'final',
                        'omcc' => $stage == 'omcc' || $stage == 'final',
                        'final' => $stage == 'final'
                    ];
                }

                // Set default color
                $defaultColor = '#E0E0E0';
                $activeColor = '#81C784';
            @endphp

            <table style="width: 100%; margin-top: 20px;">
                <tr>
                    <td style="width: 120px;">
                        <p style="margin: 0; font-size: 16px; color: #666;">Approval Progress</p>
                    </td>
                    <td>
                        <table style="border-spacing: 25px 0;">
                            <tr>
                                <td style="text-align: center;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $activeStage['planning'] ? $activeColor : $defaultColor }}; margin: 0 auto;"></div>
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Planning</div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $activeStage['division'] ? $activeColor : $defaultColor }}; margin: 0 auto;"></div>
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Division</div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $activeStage['omcc'] ? $activeColor : $defaultColor }}; margin: 0 auto;"></div>
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">OMCC</div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $activeStage['final'] ? $activeColor : $defaultColor }}; margin: 0 auto;"></div>
                                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Completed</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        @endif

        {{-- Module Link (if available) --}}
        @if(isset($data['module_path']) && !empty($data['module_path']))
        <div style="margin: 20px 0; text-align: center;">
            <p style="margin-bottom: 15px; color: #555;">You can view this item in the system by clicking the button below:</p>
            <a href="{{ config('app.url') . '/' . $data['module_path'] }}" class="button" style="display: inline-block; background-color: #0056b3; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: 600; letter-spacing: 0.5px; transition: background-color 0.2s;">
                View in System
            </a>
        </div>
        @endif

        {{-- Context-specific Message --}}
        @if(isset($data['context']))
            <div style="margin: 25px 0 15px 0;">
                @switch($data['context'])
                    @case('update_user')
                        <div style="padding: 20px; background-color: #E8F5E9; border-left: 5px solid #4CAF50; border-radius: 4px;">
                            <h4 style="color: #2E7D32; margin-top: 0; margin-bottom: 10px;">Application Status Update</h4>
                            <p style="color: #333; margin-bottom: 10px;">Thank you for using the ZCMC System. This is an automated notification regarding your AOP application status.</p>
                            <p style="color: #333; margin-bottom: 0;">Your application has been processed by the indicated area and is now proceeding to the next step in the approval process.</p>
                        </div>
                        @break

                    @case('update_next_approver')
                        <div style="padding: 20px; background-color: #E3F2FD; border-left: 5px solid #2196F3; border-radius: 4px;">
                            <h4 style="color: #0D47A1; margin-top: 0; margin-bottom: 10px;">Action Required</h4>
                            <p style="color: #333; margin-bottom: 10px;">An AOP application requires your review and action. Please log in to the system to examine the application details and provide your decision.</p>
                            <p style="color: #333; margin-bottom: 0;">As the approver for your area, your prompt attention will help ensure timely processing of this application.</p>
                        </div>
                        @break

                    @case('returned_application')
                        <div style="padding: 20px; background-color: #FFEBEE; border-left: 5px solid #F44336; border-radius: 4px;">
                            <h4 style="color: #D32F2F; margin-top: 0; margin-bottom: 10px;">Application Returned</h4>
                            <p style="color: #333; margin-bottom: 10px;">Please review the feedback below and make the necessary revisions to your application.</p>

                            @if(isset($data['remarks']) && !empty($data['remarks']))
                            <div style="margin: 15px 0;">
                                <p style="font-weight: 600; margin-bottom: 5px; color: #333;">Return Reason:</p>
                                <div style="padding: 12px; background-color: #FFF; border-radius: 4px; border: 1px solid #FFCDD2;">{{ $data['remarks'] }}</div>
                            </div>
                            @endif

                            <p style="color: #333; margin-bottom: 0;">Please log in to the system to make the required changes and resubmit your application.</p>
                        </div>
                        @break

                    @case('final_approval')
                        <div style="padding: 20px; background-color: #E8F5E9; border-left: 5px solid #4CAF50; border-radius: 4px;">
                            <h4 style="color: #2E7D32; margin-top: 0; margin-bottom: 10px;">Application Approved</h4>
                            <p style="color: #333; margin-bottom: 10px;"><strong>Congratulations!</strong> Your Annual Operation Plan has been fully approved and finalized.</p>
                            <p style="color: #333; margin-bottom: 0;">You can now proceed with implementing your planned activities according to the approved AOP.</p>
                        </div>
                        @break

                    @case('update_all')
                        <div style="padding: 20px; background-color: #F5F5F5; border-left: 5px solid #9E9E9E; border-radius: 4px;">
                            <h4 style="color: #424242; margin-top: 0; margin-bottom: 10px;">System Notification</h4>
                            <p style="color: #333; margin-bottom: 0;">This is a system-wide notification about an AOP application. The application is currently being processed through the approval workflow.</p>
                        </div>
                        @break

                    @default
                        <div style="padding: 15px; background-color: #F5F5F5; border-radius: 4px;">
                            <p style="color: #333; margin: 0;">Thank you for using the ZCMC System.</p>
                        </div>
                @endswitch
            </div>
        @endif

        {{-- Show Remarks if present (for all contexts) --}}
        @if(isset($data['remarks']) && !empty($data['remarks']) && (!isset($data['context']) || $data['context'] !== 'returned_application'))
        <div style="margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
            <h4 style="margin-top: 0; margin-bottom: 10px; color: #555; border-bottom: 1px solid #eee; padding-bottom: 8px;">Remarks:</h4>
            <p style="margin: 0; line-height: 1.5; color: #333;">{{ $data['remarks'] }}</p>
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
