# Issue Report

This report details the potential issues found during a static analysis of the codebase. The issues are categorized by the scenarios provided in the original request.

## Admin Configuration

### Scenarios

*   Enable Single Country Code Mode
*   Enable Multiple Country Codes Mode
*   No country selected in Multiple Mode

### Analysis

The `app/Livewire/TicketScreenSettings.php` component and its corresponding view (`resources/views/livewire/ticket-screen-settings.blade.php`) handle the configuration of the country code mode. The `country_options` property determines whether a single country code is used or multiple are allowed.

### Potential Issues

1.  **Lack of Validation for Multiple Country Codes Mode:**
    *   **Description:** When an administrator enables "Multiple Country Codes Mode," there is no validation to ensure that at least one country is selected in the "Allowed Countries" management page (`/country-manager`). If no countries are selected, the ticket screen may not display any country code options, preventing users from entering their phone numbers.
    *   **Files:** `app/Livewire/TicketScreenSettings.php`, `app/Livewire/CountryManager.php`
    *   **Recommendation:** Add a validation rule to the `TicketScreenSettings` component to ensure that at least one country is selected when multiple country code mode is enabled.

## Ticket Screen

### Scenarios

*   Single Country Code Display
*   Multiple Country Codes Display
*   Select Country Code & Enter Number
*   Invalid Phone Number Format
*   Empty Phone Number
*   Switch Country Code

### Analysis

The `resources/views/livewire/queue.blade.php` view displays the phone number input field. The `app/Livewire/Queue.php` component handles the logic for this view.

### Potential Issues

1.  **Invalid Phone Number Format:**
    *   **Description:** There is no frontend or backend validation to check if the entered phone number is in a valid format for the selected country. The `onkeypress="return checkIt(event)"` only allows numbers to be entered, but it doesn't enforce any length or format rules. This could lead to invalid phone numbers being saved in the database.
    *   **File:** `resources/views/livewire/queue.blade.php`
    *   **Recommendation:** Implement both frontend and backend validation for phone numbers based on the selected country's formatting rules.

## Data Handling

### Scenarios

*   Save Single Mode Number
*   Check Concatenation

### Analysis

The `app/Livewire/Queue.php` component is responsible for saving the phone number to the database.

### Potential Issues

1.  **Inconsistent Phone Number Concatenation:**
    *   **Description:** The `saveQueueForm` method in the `Queue.php` component concatenates the country code and phone number, but it does so inconsistently. The `json` column stores the phone number with a `+` sign before the country code, while the `full_phone_number` column stores it without the `+` sign. This inconsistency could cause issues with external services that expect a specific format.
    *   **File:** `app/Livewire/Queue.php`
    *   **Recommendation:** Ensure that the phone number is stored in a consistent format in both the `json` and `full_phone_number` columns.

## System Usage

### Scenarios

*   Send SMS
*   Send WhatsApp
*   Show on Queue Screen
*   Show in Reports
*   Show in Ticket Details
*   Send SMS with India Code
*   Send WhatsApp with India Code
*   Send SMS with UAE Code
*   Send WhatsApp with UAE Code
*   Send SMS with Saudi Code
*   Send WhatsApp with Saudi Code
*   Send SMS with Qatar Code
*   Send WhatsApp with Qatar Code

### Analysis

The `app/Models/SmsAPI.php` model is responsible for sending SMS and WhatsApp messages. The `resources/views/livewire/monthly-report.blade.php` and `resources/views/livewire/ticket-view.blade.php` views display the phone number in reports and ticket details.

### Potential Issues

1.  **Phone Number Storage and Retrieval:**
    *   **Description:** The `monthly-report.blade.php` and `ticket-view.blade.php` views display the phone number by decoding the `json` column of the `queue_storages` table. This means that the phone number is not stored in a dedicated column, which could make it difficult to query and could lead to inconsistencies if the structure of the `json` column changes.
    *   **Files:** `resources/views/livewire/monthly-report.blade.php`, `resources/views/livewire/ticket-view.blade.php`
    *   **Recommendation:** Store the phone number in a dedicated column in the `queue_storages` table to improve data integrity and make it easier to query.

## Call Screen Events

### Scenarios

*   Trigger SMS on Ticket Called
*   Trigger WhatsApp on Ticket Called
*   Trigger SMS on Ticket Recalled
*   Trigger WhatsApp on Ticket Recalled
*   Trigger SMS on Ticket Transferred
*   Trigger WhatsApp on Ticket Transferred
*   Trigger SMS on Ticket Served
*   Trigger WhatsApp on Ticket Served
*   Trigger SMS on Ticket Cancelled
*   Trigger WhatsApp on Ticket Cancelled
*   Queue generated without service assignment
*   Queue generated with service assignment

### Analysis

The `app/Livewire/QueueCalls.php` component handles the call screen events.

### Potential Issues

1.  **Queue generated without service assignment:**
    *   **Description:** The `generateQueue` method in the `QueueCalls.php` component allows staff to generate a ticket without a service assignment. This could be a potential issue if a service is required for all tickets.
    *   **File:** `app/Livewire/QueueCalls.php`
    *   **Recommendation:** Add a validation rule to the `generateQueue` method to ensure that a service is selected before a ticket can be generated.
