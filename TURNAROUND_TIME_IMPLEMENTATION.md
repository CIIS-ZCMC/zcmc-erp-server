# Application Timeline Turnaround Time Implementation

## Overview

Turnaround time in the ZCMC ERP Monitoring System represents the amount of working time that an application or transaction has spent at a particular office or stage of processing. The system calculates this metric with high precision, considering only office hours, working days, and excluding holidays and weekends.

## Calculation Method

The turnaround time calculation involves several key steps and considerations:

### Time Period Calculation

1. **Input Parameters**:
   - `date_in`: When the application arrived at the office/stage (in `ApplicationTimeline` this is the `created_at` timestamp)
   - `date_out`: When the application left the office/stage (in `ApplicationTimeline` this is either `date_approved` or `date_returned` depending on status)

2. **Office Hours Consideration**:
   - Working hours defined as 8:00 AM to 5:00 PM (9 working hours per day)
   - Only time within these hours contributes to turnaround time

3. **Non-Working Days Exclusion**:
   - Weekends (Saturdays and Sundays) are excluded
   - Public holidays are excluded (fetched from UMIS API via `UMISService::fetchHolidays()`)

### Step-by-Step Algorithm

1. **Initialization**:
   - If either `date_in` or `date_out` is missing, return null
   - If `date_out` is earlier than `date_in`, return "0 minutes"
   
2. **Day-by-Day Processing**:
   - The algorithm iterates through each day between `date_in` and `date_out`
   - For each day:
     - Skip if weekend or holiday
     - Determine the effective start and end times:
       - First day: Use `date_in` if it falls during office hours
       - Last day: Use `date_out` if it falls during office hours
     - Calculate working minutes for that day
     - Add to cumulative total

3. **Edge Cases Handling**:
   - If `date_in` is after office hours, that day contributes 0 minutes
   - If `date_out` is before office hours, that day contributes 0 minutes
   - If the application arrives and leaves on the same day, only count minutes between those times

### Detailed Algorithm Explanation

The heart of the turnaround time calculation is the day-by-day processing algorithm. This section explains in detail how the calculation works, breaking down each step of the process.

#### 1. Initialization and Date Preparation

```php
// Convert dates to Carbon instances if they aren't already
if (!($dateIn instanceof Carbon)) {
    $dateIn = Carbon::parse($dateIn);
}

if (!($dateOut instanceof Carbon)) {
    $dateOut = Carbon::parse($dateOut);
}
```

This ensures we're working with Carbon date objects, which provide powerful date manipulation functions. The timestamps in your `ApplicationTimeline` model might be stored as strings or DateTime objects, so this conversion is necessary.

#### 2. Setting Up Office Hours

```php
// Define office hours
$officeStartHour = 8; // 8:00 AM
$officeEndHour = 17;  // 5:00 PM
```

These constants define your working hours. The system only counts time between 8:00 AM and 5:00 PM as contributing to turnaround time.

#### 3. The Day-by-Day Iteration

```php
// Clone dateIn to avoid modifying the original
$currentDate = $dateIn->copy();

// Iterate through each day
while ($currentDate->startOfDay()->lte($dateOut->startOfDay())) {
    // Process each day...
    $currentDate->addDay();
}
```

This loop iterates through each calendar day between the start and end dates, one day at a time. We use `copy()` to avoid modifying the original date object, and `startOfDay()` to compare just the dates without considering the time component.

#### 4. Weekend and Holiday Exclusion

```php
// Skip weekends and holidays
if ($this->isWeekend($currentDate) || $this->isHoliday($currentDate, $holidays)) {
    $currentDate->addDay();
    continue;
}
```

Before processing any day, we check if it's a weekend or holiday. If it is, we skip that day completely and move to the next one. The `isWeekend()` function uses Carbon's built-in weekend detection, while `isHoliday()` compares against the list of holidays retrieved from the UMIS API.

#### 5. Determining Effective Working Hours

```php
// Calculate working period for this day
$dayStart = $currentDate->copy()->setHour($officeStartHour)->setMinute(0)->setSecond(0);
$dayEnd = $currentDate->copy()->setHour($officeEndHour)->setMinute(0)->setSecond(0);
```

For each working day, we start by assuming the full office hours (8:00 AM to 5:00 PM). These are the default working boundaries for the day.

#### 6. Special Handling for First and Last Days

```php
// Adjust start time if it's the first day
if ($currentDate->startOfDay()->eq($dateIn->startOfDay())) {
    if ($dateIn->gt($dayStart)) {
        $dayStart = $dateIn->copy();
    }
}

// Adjust end time if it's the last day
if ($currentDate->startOfDay()->eq($dateOut->startOfDay())) {
    if ($dateOut->lt($dayEnd)) {
        $dayEnd = $dateOut->copy();
    }
}
```

This is a critical part that handles partial working days:

- For the first day: If the application arrived after 8:00 AM, we adjust the start time to the actual arrival time.
- For the last day: If the application left before 5:00 PM, we adjust the end time to the actual departure time.

This ensures we're only counting the actual time the application spent in that stage.

#### 7. Calculating Minutes and Accumulating

```php
// Calculate minutes only if end time is after start time (valid working period)
if ($dayEnd->gt($dayStart)) {
    $dayWorkingMinutes = $dayEnd->diffInMinutes($dayStart);
    $workingMinutes += $dayWorkingMinutes;
    
    // For debugging
    Log::debug("Added {$dayWorkingMinutes} working minutes for " . $currentDate->format('Y-m-d'));
}
```

We only add minutes if there's a valid working period (end time is after start time). The `diffInMinutes()` method calculates the number of minutes between the effective start and end times for that day.

The logging is helpful for debugging and verifying the calculation is working correctly.

#### 8. Edge Cases Handling

The algorithm naturally handles several edge cases:

1. **After-hours arrival**: If an application arrives after 5:00 PM, the start time would be after the end time, so no minutes are counted for that day.

2. **Before-hours departure**: If an application leaves before 8:00 AM, similarly no minutes are counted.

3. **Weekend spanning**: If an application arrives Friday and leaves Monday, only the working hours on Friday and Monday are counted.

4. **Same-day processing**: If an application arrives and leaves on the same day, only the minutes between those times (within office hours) are counted.

#### Example Calculation Walkthrough

Let's walk through an example:

- AOP application arrives Monday at 2:00 PM
- AOP application leaves Wednesday at 10:00 AM

**Day 1 (Monday):**
- Office hours: 8:00 AM - 5:00 PM
- Effective hours: 2:00 PM - 5:00 PM (adjusted start time due to arrival time)
- Working minutes: 3 hours = 180 minutes

**Day 2 (Tuesday):**
- Office hours: 8:00 AM - 5:00 PM
- Effective hours: 8:00 AM - 5:00 PM (full day)
- Working minutes: 9 hours = 540 minutes

**Day 3 (Wednesday):**
- Office hours: 8:00 AM - 5:00 PM
- Effective hours: 8:00 AM - 10:00 AM (adjusted end time due to departure time)
- Working minutes: 2 hours = 120 minutes

**Total working minutes:** 180 + 540 + 120 = 840 minutes = 14 hours

**Final formatted result:** "1 day and 5 hours" (using 9-hour workdays)

This approach ensures that only actual working time is counted, providing an accurate measure of how long an application spent at a particular stage of processing.

### Time Format Conversion

After calculating the total working minutes:

1. Convert minutes to hours (`working_hours = floor(working_minutes / 60)`)
2. Calculate remaining minutes (`remaining_minutes = working_minutes % 60`)
3. Convert hours to days and remaining hours:
   - Days = floor(working_hours / office_hours_day) [9 hours per working day]
   - Remaining hours = working_hours % office_hours_day

### Human-Readable Output

The final turnaround time is presented in a human-readable format:

1. If all values are zero: "0 minutes"
2. Otherwise, combine days, hours, and minutes using:
   - Appropriate singular/plural forms (e.g., "1 day" vs "2 days")
   - Natural language formatting with commas and "and" (e.g., "1 day, 3 hours and 45 minutes")
   - Omitting zero values (e.g., "3 hours and 45 minutes" rather than "0 days, 3 hours and 45 minutes")

## Implementation in ApplicationTimelineResource

To implement the turnaround time calculation in the `ApplicationTimelineResource` class:

### 1. Holiday Retrieval from UMIS API

The system requires holiday information to accurately calculate working time. This is implemented through the `UMISService` class, which provides an interface to the UMIS API.

Below is the complete implementation of the UMISService class used for fetching holidays:

```php
<?php

namespace App\Services;

use App\Helpers\SystemLogHelper;
use App\Helpers\Token;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Exception;

class UMISService
{
    protected mixed $baseUrl;
    protected mixed $apiHeader;
    protected mixed $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.umis.base_url', env('UMIS_DOMAIN'));
        $this->apiHeader = config('services.umis.api_header', env('UMIS_API_HEADER', 'UMIS-Api-Key'));
        $this->apiKey = config('services.umis.api_key', env('API_KEY'));
    }

    /**
     * Get standardized headers for UMIS API requests
     *
     * @param array $additionalHeaders Additional headers to include
     * @return array
     */
    protected function getStandardHeaders(array $additionalHeaders = []): array
    {
        $headers = [
            'Accept' => 'application/json',
            $this->apiHeader => $this->apiKey,
        ];

        return array_merge($headers, $additionalHeaders);
    }

    /**
     * Create a standard HTTP client with common configurations
     *
     * @param array $headers Headers to include in the request
     * @return PendingRequest
     */
    protected function createHttpClient(array $headers = []): PendingRequest
    {
        return Http::withoutVerifying() // Skip SSL verification for development
            ->timeout(30) // Increase timeout to 30 seconds
            ->withHeaders($this->getStandardHeaders($headers));
    }

    /**
     * Get holidays data from UMIS
     *
     * @return array|null
     */
    public function fetchHolidays(): ?array
    {
        try {
            Log::info('Attempting to fetch holidays from UMIS API', [
                'url' => $this->baseUrl . '/api/pr-holidays',
                'has_api_key' => !empty($this->apiKey),
            ]);

            $response = $this->createHttpClient([
                'PRMonitoring' => 'PRM',
            ])->get($this->baseUrl . '/api/pr-holidays');

            if ($response->successful()) {
                Log::info('UMIS API - Successfully fetched holidays');
                return $response->json();
            }


            return null;
        } catch (Exception $e) {
            Log::error('UMIS API - Exception while fetching holidays', [
                'url' => $this->baseUrl . '/api/pr-holidays',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }
}
```

To use this service in your turnaround time calculation, you would implement the following method to retrieve and format the holidays:

```php
// In your service class
protected function getHolidays(): array
{
    // Create instance of UMISService
    $umisService = new UMISService();
    
    // Fetch holidays from UMIS
    $holidayData = $umisService->fetchHolidays();
    
    if (!$holidayData) {
        // Log failure and return empty array as fallback
        Log::warning('Failed to fetch holidays from UMIS, proceeding with empty holiday list');
        return [];
    }
    
    // Process the holiday data into the format we need (YYYY-MM-DD)
    $formattedHolidays = [];
    foreach ($holidayData as $holiday) {
        // Convert date format as needed
        $formattedDate = date('Y-m-d', strtotime($holiday['date']));
        $formattedHolidays[] = $formattedDate;
    }
    
    return $formattedHolidays;
}
```

### 2. Create Helper Methods

Add these methods to a trait or service class:

```php
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\UMISService;

/**
 * Check if a date is a weekend.
 * 
 * @param Carbon $date
 * @return bool
 */
public function isWeekend(Carbon $date): bool
{
    return $date->isWeekend();
}

/**
 * Check if a date is a holiday.
 * 
 * @param Carbon $date
 * @param array $holidays
 * @return bool
 */
public function isHoliday(Carbon $date, array $holidays): bool
{
    $dateFormat = $date->format('Y-m-d');
    return in_array($dateFormat, $holidays);
}

/**
 * Calculate turnaround time between two dates.
 * 
 * @param string|Carbon $dateIn Start date (when application entered the stage)
 * @param string|Carbon $dateOut End date (when application left the stage)
 * @return string|null
 */
public function calculateTurnaroundTime($dateIn, $dateOut): ?string
{
    // Convert dates to Carbon instances if they aren't already
    if (!($dateIn instanceof Carbon)) {
        $dateIn = Carbon::parse($dateIn);
    }
    
    if (!($dateOut instanceof Carbon)) {
        $dateOut = Carbon::parse($dateOut);
    }
    
    // If either date is missing, return null
    if (!$dateIn || !$dateOut) {
        return null;
    }
    
    // If dateOut is before dateIn, return "0 minutes"
    if ($dateOut->lt($dateIn)) {
        return "0 minutes";
    }
    
    // Get holidays from UMIS API
    $holidays = $this->getHolidays();
    
    // Define office hours
    $officeStartHour = 8; // 8:00 AM
    $officeEndHour = 17;  // 5:00 PM
    
    // Initialize working minutes
    $workingMinutes = 0;
    
    // Clone dateIn to avoid modifying the original
    $currentDate = $dateIn->copy();
    
    // Iterate through each day
    while ($currentDate->startOfDay()->lte($dateOut->startOfDay())) {
        // Skip weekends and holidays
        if ($this->isWeekend($currentDate) || $this->isHoliday($currentDate, $holidays)) {
            $currentDate->addDay();
            continue;
        }
        
        // Calculate working period for this day
        $dayStart = $currentDate->copy()->setHour($officeStartHour)->setMinute(0)->setSecond(0);
        $dayEnd = $currentDate->copy()->setHour($officeEndHour)->setMinute(0)->setSecond(0);
        
        // Adjust start time if it's the first day
        if ($currentDate->startOfDay()->eq($dateIn->startOfDay())) {
            if ($dateIn->gt($dayStart)) {
                $dayStart = $dateIn->copy();
            }
        }
        
        // Adjust end time if it's the last day
        if ($currentDate->startOfDay()->eq($dateOut->startOfDay())) {
            if ($dateOut->lt($dayEnd)) {
                $dayEnd = $dateOut->copy();
            }
        }
        
        // Calculate minutes only if end time is after start time (valid working period)
        if ($dayEnd->gt($dayStart)) {
            $dayWorkingMinutes = $dayEnd->diffInMinutes($dayStart);
            $workingMinutes += $dayWorkingMinutes;
            
            // For debugging
            Log::debug("Added {$dayWorkingMinutes} working minutes for " . $currentDate->format('Y-m-d'));
        }
        
        $currentDate->addDay();
    }
    
    return $this->formatTurnaroundTime($workingMinutes);
}

/**
 * Format turnaround time from minutes to human-readable string.
 * 
 * @param int $totalMinutes
 * @return string
 */
public function formatTurnaroundTime(int $totalMinutes): string
{
    if ($totalMinutes <= 0) {
        return "0 minutes";
    }
    
    // Convert minutes to hours
    $hours = floor($totalMinutes / 60);
    $remainingMinutes = $totalMinutes % 60;
    
    // Convert hours to days and remaining hours:
    - Days = floor($hours / office_hours_day) [9 hours per working day]
    - Remaining hours = working_hours % office_hours_day

    // Build the formatted string
    $parts = [];
    
    if ($days > 0) {
        $parts[] = $days . " " . ($days == 1 ? "day" : "days");
    }
    
    if ($remainingHours > 0) {
        $parts[] = $remainingHours . " " . ($remainingHours == 1 ? "hour" : "hours");
    }
    
    if ($remainingMinutes > 0) {
        $parts[] = $remainingMinutes . " " . ($remainingMinutes == 1 ? "minute" : "minutes");
    }
    
    // Combine parts with appropriate formatting
    if (count($parts) == 1) {
        return $parts[0];
    } elseif (count($parts) == 2) {
        return $parts[0] . " and " . $parts[1];
    } else {
        return $parts[0] . ", " . $parts[1] . " and " . $parts[2];
    }
}
```

### 3. Modify the ApplicationTimelineResource toArray Method

Update the `toArray` method in `ApplicationTimelineResource` to include turnaround time for each timeline entry:

```php
"timelines" => $this->whenLoaded('applicationTimelines', function () {
    return $this->applicationTimelines->map(function ($timeline) {
        // Get required dates for calculation
        $dateIn = $timeline->created_at;
        $dateOut = null;
        
        // Determine date_out based on status
        if ($timeline->status === ApplicationTimeline::STATUS_APPROVED) {
            $dateOut = $timeline->date_approved;
        } elseif ($timeline->status === ApplicationTimeline::STATUS_RETURNED) {
            $dateOut = $timeline->date_returned;
        }
        
        // Calculate turnaround time
        $turnaroundTime = null;
        if ($dateIn && $dateOut) {
            $turnaroundTime = $this->calculateTurnaroundTime($dateIn, $dateOut);
        }
        
        $activity_comments = $timeline->activityComments ?? collect([]);
        
        return [
            "id" => $timeline->id,
            "approver_user_id" => $timeline->approverUser->id ?? null,
            "approver_user" => $timeline->approverUser->name ?? null,
            "approver_user_position" => $timeline->approverUser->assignedArea->designation->name ?? null,
            "user_id" => $timeline->user->id,
            "user" => $timeline->user->name,
            "user_position" => $timeline->user->assignedArea->designation->name ?? null,
            "status" => $timeline->status,
            "remarks" => $timeline->remarks,
            "has_comments" => $activity_comments->count() > 0,
            "comments_count" => $activity_comments->count(),
            "activities_with_comments" => $activity_comments->pluck('activity_id')->unique()->count(),
            "date_created" => $timeline->date_created, 
            "date_approved" => $timeline->date_approved,
            "date_returned" => $timeline->date_returned,
            "date_updated" => $timeline->updated_at,
            
            // Add turnaround time
            "turnaround_time" => $turnaroundTime,
        ];
    });
}),
```

### 4. Adding Cumulative Turnaround Time

To add a cumulative turnaround time for the entire application:

```php
"cumulative_turnaround_time" => $this->whenLoaded('applicationTimelines', function () {
    $totalMinutes = 0;
    
    foreach ($this->applicationTimelines as $timeline) {
        $dateIn = $timeline->created_at;
        $dateOut = null;
        
        // Determine date_out based on status
        if ($timeline->status === ApplicationTimeline::STATUS_APPROVED) {
            $dateOut = $timeline->date_approved;
        } elseif ($timeline->status === ApplicationTimeline::STATUS_RETURNED) {
            $dateOut = $timeline->date_returned;
        }
        
        if ($dateIn && $dateOut) {
            // Add a method to calculate minutes only (without formatting)
            $timelineMinutes = $this->calculateTurnaroundTimeInMinutes($dateIn, $dateOut);
            $totalMinutes += $timelineMinutes;
        }
    }
    
    return $this->formatTurnaroundTime($totalMinutes);
}),
```

## Examples

1. **Simple Working Hours Case**:
   - AOP application arrives Monday at 10:00 AM
   - AOP application leaves Monday at 3:00 PM
   - Turnaround time: "5 hours"

2. **Multi-Day Case**:
   - PPMP application arrives Monday at 2:00 PM
   - PPMP application leaves Wednesday at 10:00 AM
   - Turnaround time calculation:
     - Monday: 3 hours (2:00 PM to 5:00 PM)
     - Tuesday: 9 hours (full working day)
     - Wednesday: 2 hours (8:00 AM to 10:00 AM)
     - Total: 14 hours = "1 day and 5 hours"

3. **Weekend and Holiday Exclusion**:
   - AOP application arrives Friday at 4:00 PM
   - AOP application leaves Monday at 9:00 AM
   - Only 2 working hours counted (1 hour on Friday, 1 hour on Monday)
   - Turnaround time: "2 hours"

The final turnaround time is returned in a human-readable format, combining days, hours, and minutes using appropriate singular/plural forms, natural language formatting, and omitting zero values.

```php
return $this->formatTurnaroundTime($totalMinutes);
```

This will return the formatted turnaround time, such as "1 day, 3 hours and 45 minutes" or "3 hours and 45 minutes", depending on the calculated values.
