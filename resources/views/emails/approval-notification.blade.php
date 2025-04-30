@component('mail::message')
    <div style="font-family: Arial, sans-serif; color: #333333;">
        <h2 style="color: #0056b3; margin-bottom: 20px;">{{ $title }}</h2>

        <p style="margin-bottom: 15px; line-height: 1.5;">{{ $message }}</p>

        @if (isset($actionUser) && $actionUser)
            <div style="background-color: #f7f7f7; padding: 10px 15px; border-left: 4px solid #0056b3; margin: 15px 0;">
                <p><strong>Action taken by:</strong> {{ $actionUser->name }}</p>
            </div>
        @endif

        <p style="margin: 20px 0;">You can view the AOP Application details by clicking the button below:</p>

        @component('mail::button', ['url' => $url, 'color' => 'primary'])
            View AOP Application
        @endcomponent

        <p style="margin-top: 30px; font-size: 14px; color: #666;">This is an automated message from the ZCMC ERP System.
            Please do not reply to this email.</p>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dddddd; font-size: 14px; color: #666666;">
        <p>Thanks,<br>{{ $appName }}</p>
        <p style="font-size: 12px; margin-top: 15px; color: #999999;">{{ date('Y') }} {{ $appName }}. All rights
            reserved.</p>
    </div>
@endcomponent
