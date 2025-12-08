<?php
return [
    'error' => 'Error',
    'description' => 'Description',
    'resolution' => 'Resolution',

    'ERR001' => [
        'message' => 'Please select counter first!',
        'description' => 'User attempted action without selecting a counter.',
        'resolution' => 'Prompt user to choose a counter before proceeding.',
    ],
    'ERR002' => [
        'message' => 'No call',
        'description' => 'No current call to perform the operation.',
        'resolution' => 'Initiate or wait for a new call.',
    ],
    'ERR003' => [
        'message' => 'Close Current Serving Call Firstly!',
        'description' => 'User must end the active call before starting a new one.',
        'resolution' => 'Close the current call before proceeding.',
    ],
    'ERR004' => [
        'message' => 'This call is on hold temporarily',
        'description' => 'Selected call is temporarily on hold.',
        'resolution' => 'Resume the call before continuing.',
    ],
    'ERR005' => [
        'message' => 'This call is on Hold temporarily (start)',
        'description' => 'Queued call entered a temporary hold state.',
        'resolution' => 'Wait until hold is removed or resume manually.',
    ],
    'ERR006' => [
        'message' => 'The queue number already exists',
        'description' => 'Queue number already exists in the system.',
        'resolution' => 'Generate a new unique queue number.',
    ],
    'ERR007' => [
        'message' => "System doesn't have a call which is being served by you!",
        'description' => 'No active call found assigned to user.',
        'resolution' => 'Ensure a call is currently being served.',
    ],
    'ERR008' => [
        'message' => "The queue number is already exist",
        'description' => 'The queue number is already exist in database',
        'resolution' => 'Please check your queue number',
    ],
    'ERR009' => [
        'message' => "The current queue has been reset which are not closed!",
        'description' => 'The current queue has been reset',
        'resolution' => 'Your queue has been reset',
    ],

    'ERR010' => [
        'message' => "The queue number is not exist",
        'description' => 'The queue number is not exist in database',
        'resolution' => 'Please check your queue number',
    ],

    'ERR011' => [
        'message' => "This queue is already called",
        'description' => 'This queue is already called in syste,',
        'resolution' => 'Please again call the next queue',
    ],

    'BOOK001' => [
        'message' => 'Unable to generate ticket due to invalid rules.',
        'description' => 'System configuration blocked ticket creation.',
        'resolution' => 'Contact admin to review booking rules.',
    ],
    'BOOK002' => [
        'message' => 'Payment failed: Something went Wrong',
        'description' => 'Unknown issue occurred during payment.',
        'resolution' => 'Retry payment or contact support.',
    ],
    'BOOK003' => [
        'message' => 'Payment service keys are missing',
        'description' => 'API credentials for payment are not set.',
        'resolution' => 'Configure API Key & Secret in settings.',
    ],
    'BOOK004' => [
        'message' => 'Payment setting not configured',
        'description' => 'Payment settings are incomplete.',
        'resolution' => 'Complete payment setup in admin panel.',
    ],

    'SUCCESS001' => [
        'message' => 'Call Successful',
    ],
    'SUCCESS002' => [
        'message' => 'Suspension processed successfully with notifications sent',
    ],
    'SUCCESS003' => [
        'message' => 'Call started Successfully'
    ],
    'SUCCESS004' => [
        'message' => 'Call Closed Successfully'
    ],
    'SUCCESS005' => [
        'message' => 'Call Transfer Successful'
    ],
    'SUCCESS006' => [
        'message' => 'Recall Successful'
    ],
    'SUCCESS007' => [
        'message' => 'Call Move Back Successful'
    ],
    'SUCCESS008' => [
        'message' => 'Request has been sent to the admin'
    ],
    'SUCCESS009' => [
        'message' => 'Hold Successful'
    ],
    'SUCCESS0010' => [
        'message' => 'Cancelled Successful'
    ],
    'SUCCESS0011' => [
        'message' => 'SMS sent successfully!'
    ],
    'SUCCESS0012' => [
        'message' => 'Queue generated successfully!'
    ],
    'SUCCESS0013' => [
        'message' => 'Estimate note updated successfully!'
    ],
    'SUCCESS0014' => [
        'message' => 'Call Revert Successful'
    ],
    'SUCCESS0015' => [
        'message' => 'Visitor edited successfully'
    ],
    'SUCCESS0016' => [
        'message' => 'Call Missed Successful'
    ],
   'SUCCESS0017' => [
    'message' => 'Data Saved Successfully'
],

    'VAL001' => [
        'message' => 'Please enter queue number and category',
    ],
    'VAL002' => [
        'message' => 'Please enter break type and comment',
    ],

    'Click on the continue button to unlock this screen! Break time is for' => 'Click on the continue button to unlock this screen! Break time is for',
    'minutes.' => 'minutes.',
    'CONTINUE' => 'CONTINUE',
    'Call started Successfully' => 'Call started Successfully',
    'success' => 'success',
    'Suspension processed successfully with notifications sent' => 'Suspension processed successfully with notifications sent',
    'Are you sure' => 'Are you sure',
    'warning' => 'warning',
    'You want to revert this' => 'You want to revert this',
    'YES, REVERT IT' => 'YES, REVERT IT',
    'No, CANCEL' => 'No, CANCEL',
    'Please rate our service' => 'Please rate our service',
    'Excellent' => 'Excellent',
    'Good' => 'Good',
    'Neutral' => 'Neutral',
    'Poor' => 'Poor',
    'Please Wait' => 'Please Wait',
    'Revert Queue' => 'Revert Queue',
    'Cancelled' => 'Cancelled',
    'Your data is safe' => 'Your data is safe',
    'error' => 'error',
    "You won't be able to revert this" => "You won't be able to revert this",
    'OK' => 'OK',
    'Cancel' => 'Cancel',
    'Please enter queue number and category' => 'Please enter queue number and category',
    'Break' => 'Break',
    'Choose Any Reason' => 'Choose Any Reason',
    'Comment' => 'Comment',
    'Please enter break type and comment' => 'Please enter break type and comment',
    'Enter Queue Number' => 'Enter Queue Number',
    'Select Category' => 'Select Category',
    'Type of Break' => 'Type of Break',
    'Unlock Screen' => 'Unlock Screen',
    'Updating' => 'Updating',
    'Success!' => 'Success!',
    'Yes, delete it' => 'Yes, delete it',
    'Data Deleted Successfully' => 'Data Deleted Successfully',
    'No record selected' => 'No record selected',

];
