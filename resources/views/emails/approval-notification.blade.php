<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['subject'] }}</title>
    {{--
    CSS Styling for Email Template

    This styling is inline to ensure maximum compatibility with email clients.
    The design follows ZCMC branding guidelines and is structured to be
    responsive and accessible across different devices and email clients.
    --}}
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

    {{--
    Main Email Container

    This container holds the entire email content with a max-width of 600px
    to ensure good readability across devices.
    --}}
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

        {{-- Personalized Greeting - Dynamic based on context --}}
        <p>
            @if ($data['context'] === 'request' || $data['context'] === 'update_user')
                Dear <strong>{{ $data['requester_name'] }}</strong>,
            @elseif ($data['context'] === 'update_next_user')
                Dear <strong>{{ $data['next_office_employee_name'] }}</strong>
            @endif
        </p>

        {{--
        Notification Content
        This section dynamically displays different messages based on the context of the notification:
        - request: Initial request submission
        - update_user: Status update notification for the requester
        - update_next_user: Notification for the next person in the workflow
        --}}
        <p>
            @if ($data['context'] === 'request')
                This is to notify you that your request has been submitted successfully and is now
                <strong>{{ $data['status'] }}</strong>. The request will be reviewed and approved by
                <strong>{{ $data['next_office_area_code'] }}</strong> office. Please wait for further updates.
            @elseif ($data['context'] === 'update_user')
                This is to notify you that the status of your transaction has been updated to
                <strong>{{ $data['status'] }}</strong>.
            @elseif ($data['context'] === 'update_next_user')
                This is to notify you that a request from <strong>{{ $data['requester_name'] }}</strong> from the
                <strong>{{ $data['requester_area'] }}</strong> area is submitted and is now waiting for your review and
                approval.
            @endif
        </p>

        {{--
        Transaction Details Section

        This section displays key details about the transaction, including type, code, status, and requested date.
        --}}
        <div class="details">
            <h3>Transaction Details</h3>
            <table>
                <tr>
                    <th>Transaction Type</th>
                    <td>{{ $data['transaction_type'] }}</td>
                </tr>
                <tr>
                    <th>Transaction Code</th>
                    <td>{{ $data['transaction_code'] }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @php
                            $statusColors = [
                                'Pending' => '#FFC107', // Yellow/Amber
                                'Received' => '#4CAF50', // Green
                                'Released' => '#2196F3', // Blue
                                'Approved' => '#8BC34A', // Light Green
                                'Returned' => '#F44336', // Red
                                'On Hold' => '#9C27B0', // Purple
                                'Completed' => '#009688', // Teal
                                'default' => '#757575', // Grey
                            ];

                            $status = $data['status'] ?? 'Pending';
                            $bgColor = $statusColors[$status] ?? $statusColors['default'];
                            $textColor = in_array($status, ['Pending', 'Approved']) ? '#000' : '#fff';
                        @endphp

                        <span
                            style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; background-color: {{ $bgColor }}; color: {{ $textColor }}; text-align: center;">
                            {{ $status }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Requested At</th>
                    <td>{{ $data['requested_at'] }}</td>
                </tr>
            </table>
        </div>

        {{--
        Requester Details Section

        This section provides information about the person who made the request, including their employee ID, name, area, and area code.
        --}}
        <div class="details">
            <h3>Requester Details</h3>
            <table>
                <tr>
                    <th>Employee ID</th>
                    <td>{{ $data['requester_employee_id'] }}</td>
                </tr>
                <tr>
                    <th>Employee Name</th>
                    <td>{{ $data['requester_name'] }}</td>
                </tr>
                <tr>
                    <th>Area</th>
                    <td>{{ $data['requester_area'] }}</td>
                </tr>
                <tr>
                    <th>Area Code</th>
                    <td>{{ $data['requester_area_code'] }}</td>
                </tr>
                <tr>
                    <th>Date Requested</th>
                    <td>{{ $data['requested_at'] }}</td>
                </tr>
            </table>
        </div>

        {{-- Remarks Section --}}
        <p><strong>Remarks:</strong> {{ $data['remarks'] }}</p>

        {{--
        Current Location Information Box

        This section displays the current location of the request in the workflow.
        It dynamically shows different information based on where the request is currently being processed.
        --}}
        <div style="margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #007bff;">
            <p style="margin: 0;"><strong>Current Location:</strong>
                @if (isset($data['current_office_area_code']) && $data['current_office_area_code'] != 'N/A')
                    Your request is currently at <strong>{{ $data['current_office_area'] }}</strong>
                    ({{ $data['current_office_area_code'] }})
                    @if (isset($data['current_office_employee_name']) && $data['current_office_employee_name'] != 'N/A')
                        and was last updated by <strong>{{ $data['current_office_employee_name'] }}</strong>
                    @endif
                @elseif (isset($data['next_office_area_code']) && $data['next_office_area_code'] != 'N/A')
                    Your request is being forwarded to <strong>{{ $data['next_office_area_code'] }}</strong>
                @elseif(isset($data['updated_by']) && $data['updated_by'] != 'System')
                    Your request was last handled by <strong>{{ $data['updated_by'] }}</strong>
                @else
                    Your request is being processed in the system
                @endif
            </p>
        </div>

        {{--
        Status-specific Messages

        This section provides additional context and instructions based on the current
        status of the request. Each status has specific information relevant to that stage
        in the workflow.
        --}}
        <p>
            @switch($data['status'] ?? 'Pending')
                @case('Pending')
                    Please note that your request is currently awaiting review. You will be notified once the status is updated.
                @break

                @case('Received')
                    Your request has been received and is currently being processed by the
                    {{ $data['current_office_area'] ?? ($data['current_office_area_code'] ?? 'current office') }}.
                @break

                @case('Released')
                    Your request has been released from
                    {{ $data['current_office_area'] ?? ($data['current_office_area_code'] ?? 'the current office') }}
                    and is in transit to {{ $data['next_office_area_code'] ?? 'the next office' }}.
                    Note that the document has not yet been received by
                    {{ $data['next_office_area_code'] ?? 'the next office' }}.
                @break

                @case('Approved')
                    Your request has been approved and is being forwarded to the
                    {{ $data['next_office_area_code'] ?? 'next office' }} for further processing.
                @break

                @case('Returned')
                    Your request has been returned. Please review the remarks for details on why it was returned and what
                    actions may be needed.
                @break

                @case('On Hold')
                    Your request has been placed on hold. Please refer to the remarks section for more information about why
                    it's on hold and when processing might resume.
                @break

                @case('Completed')
                    Your request has been completed successfully. No further action is required at this time.
                @break

                @default
                    Your request status has been updated to "{{ $data['status'] }}". You will be notified of any further
                    changes.
            @endswitch

            If you have any questions or require assistance, feel free to contact us at <a
                href="mailto:ciis.zcmc@gmail.com">ciis.zcmc@gmail.com</a>.
        </p>

        {{-- Footer Section with Sender Information --}}
        <div class="footer">
            <p style="margin: 0;">Best regards,</p>
            <p style="margin: 0;">IISU</p>
            <p style="margin: 0;">Innovation and Information Systems Unit</p>
        </div>
    </div>
</body>

</html>
