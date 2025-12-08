<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\FeedbackSetting;
use App\Models\ActivityLog;
use App\Models\Schedule;
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\MessageDetail;
use App\Models\SmtpDetails;
use App\Models\SmsAPI;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class FeedbackSettings extends Component
{
    #[Title('Feedback Setting')]
    public $teamId;
    public $locationId;

    public $feedback_system = false;
    public $form_after_closedcall = false;
    public $enable_comment_box = false;
    public $rating_comment_box_threshold = 2;
    public $feedback_alert_enable = false;
    public $feedback_alert_email = '';
    public $feedback_alert_threshold = 0;

    public $successMessage = null;
    public $userAuth;

    //Post-Interaction Rating Survey Settings
    public bool $enable_post_interaction =false;
    public $trigger_method ='manual';
    public $random_percent ='20';
    public bool $trigger_delay_enable = false;
    public $trigger_delay = 0;
    public bool $survey_scheduling_enable = false;
    public bool $manual_trigger_enable = true;
    public bool $enable_scheduling = false;
    public $schedule_duration_type = 0;
    public array $schedules = [];
    public $manual_trigger;
    public $manualMail =false;
    public $rating_style = 'stars';

    public function mount()
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Feedback Setting')) {
            abort(403);
        }

        $this->teamId = tenant('id');
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();

       $settings = FeedbackSetting::with('schedules')
				->where('team_id', $this->teamId)
				->where('location_id', $this->locationId)
				->first();


		if ($settings) {
			$this->manualMail = $settings?->enable_post_interaction == 1 ? true : false;
			$this->feedback_system           = (bool) $settings->feedback_system;
			$this->form_after_closedcall     = (bool) $settings->form_after_closedcall;
			$this->enable_comment_box        = (bool) $settings->enable_comment_box;
			$this->rating_comment_box_threshold = (int) $settings->rating_comment_box_threshold;
			$this->feedback_alert_enable     = (bool) $settings->feedback_alert_enable;
			$this->feedback_alert_email      = $settings->feedback_alert_email;
			$this->feedback_alert_threshold  = $settings->feedback_alert_threshold;

			$this->enable_post_interaction   = (bool) $settings->enable_post_interaction;
			$this->trigger_method            = $settings->trigger_method ?? 'manual';
			$this->random_percent            = $settings->random_percent ?? '20';
			$this->trigger_delay_enable      = (bool) $settings->trigger_delay_enable;
			$this->trigger_delay             = $settings->trigger_delay;
			$this->survey_scheduling_enable  = (bool) $settings->survey_scheduling_enable;
			$this->manual_trigger_enable     = (bool) $settings->manual_trigger_enable;
			$this->enable_scheduling         = (bool) $settings->enable_scheduling;
			$this->schedule_duration_type    = $settings->schedule_duration_type ?? 0;
			$this->manual_trigger            = $settings->manual_trigger;
			$this->rating_style              = $settings->rating_style ?? 'stars';

			// Load schedules from related table
		$this->schedules = $settings->schedules()
			->get()
			->map(fn($s) => [
				'id' => $s->id,
				'schedule_duration_type' => $s->schedule_duration_type,
				'start_time' => $s->start_time ? Carbon::parse($s->start_time)->format('H:i') : '',
				'end_time' => $s->end_time ? Carbon::parse($s->end_time)->format('H:i') : '',
			])
		   ->toArray();


			$this->trigger_delay_enable = false;
			$this->survey_scheduling_enable = false;
			$this->manual_trigger_enable = false;

		 if($this->trigger_method == 'auto'){
				$this->trigger_delay_enable = true;
			}
			if($this->trigger_method == 'random'){
				$this->survey_scheduling_enable = true;
			}
			if($this->trigger_method == 'manual'){
				$this->manual_trigger_enable = true;
			}

    }
}

     public function updatedTriggerMethod($value){

        $this->trigger_method =$value;

        $this->trigger_delay_enable = false;
        $this->survey_scheduling_enable = false;
        $this->manual_trigger_enable = false;

        if($value == 'auto'){
            $this->trigger_delay_enable = true;
        }
        if($value == 'random'){
            $this->survey_scheduling_enable = true;
        }
        if($value == 'manual'){
            $this->manual_trigger_enable = true;
        }
    }

    public function updatedEnableScheduling($value){

         if ($value && empty($this->schedules)) {
            $this->addSchedule();
        }

          $this->dispatch('toggle-scheduling');
      }

    public function addSchedule()
    {
        $this->schedules[] = [
            'schedule_duration_type' => '',
            'start_time' => '',
            'end_time' => '',
        ];
    }

    public function removeSchedule($index)
    {
        unset($this->schedules[$index]);
        $this->schedules = array_values($this->schedules); // reindex
    }


public function save()
{
    try {
        $this->validate([
            'feedback_system' => 'boolean',
            'form_after_closedcall' => 'boolean',
            'enable_comment_box' => 'boolean',
            'rating_comment_box_threshold' => 'required|integer|min:1|max:5',
            'feedback_alert_enable' => 'boolean',
            'feedback_alert_email' => 'nullable|email',
            'feedback_alert_threshold' => 'nullable|integer|min:1|max:5',
            'enable_post_interaction' => 'boolean',
            'trigger_method' => 'nullable|string',
            'random_percent' => 'nullable|numeric|min:1|max:100',
            'trigger_delay' => 'nullable|integer|min:1|max:1000',
            'enable_scheduling' => 'boolean',
            // 'manual_trigger' => 'nullable|string',
            'rating_style' => 'nullable|string',

            // conditional schedule validation
            'schedules.*.schedule_duration_type' => [
                Rule::requiredIf(fn () => $this->trigger_method === 'random' && $this->enable_scheduling),
                'string'
            ],
            'schedules.*.start_time' => [
                Rule::requiredIf(fn () => $this->trigger_method === 'random' && $this->enable_scheduling),
                'date_format:H:i'
            ],
            'schedules.*.end_time' => [
                Rule::requiredIf(fn () => $this->trigger_method === 'random' && $this->enable_scheduling),
                'date_format:H:i',
                'after:schedules.*.start_time'
            ],
        ]);

        $alertThreshold = is_numeric($this->feedback_alert_threshold)
            ? (int) $this->feedback_alert_threshold
            : 2;

        // save feedback setting
        $feedbackSetting = FeedbackSetting::updateOrCreate(
            ['team_id' => $this->teamId, 'location_id' => $this->locationId],
            [
                'feedback_system' => $this->feedback_system,
                'form_after_closedcall' => $this->form_after_closedcall,
                'enable_comment_box' => $this->enable_comment_box,
                'rating_comment_box_threshold' => $this->rating_comment_box_threshold,
                'feedback_alert_enable' => $this->feedback_alert_enable,
                'feedback_alert_email' => $this->feedback_alert_email,
                'feedback_alert_threshold' => $alertThreshold,
                'enable_post_interaction' => $this->enable_post_interaction,
                'trigger_method' => $this->trigger_method,
                'random_percent' => $this->random_percent,
                 'trigger_delay' => $this->trigger_delay !== '' ? (int) $this->trigger_delay : 0,
                'enable_scheduling' => $this->enable_scheduling,
                // 'manual_trigger' => $this->manual_trigger,
                'rating_style' => $this->rating_style,
            ]
        );

        // save schedules
        if ($this->enable_scheduling) {
        Schedule::where('feedback_setting_id',$feedbackSetting->id)->delete();
            foreach ($this->schedules as $scheduleData) {
                Schedule::create(
                    [
                        'team_id' => $this->teamId,
                        'feedback_setting_id' => $feedbackSetting->id,
                        'schedule_duration_type' => $scheduleData['schedule_duration_type'],
                        'start_time' => $scheduleData['start_time'],
                        'end_time' => $scheduleData['end_time'],
                    ]
                );
            }
        }

        session()->flash('success', 'Settings Updated Successfully.');
        $this->successMessage = __('text.updated successfully');

        ActivityLog::storeLog(
            $this->teamId,
            $this->userAuth->id,
            null,
            null,
            'Feedback Settings',
            $this->locationId,
            ActivityLog::SETTINGS,
            null,
            $this->userAuth
        );



    $this->dispatch('hide-alert');

        $this->dispatch('saved');

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Show validation errors in your debug output
        // dd($e->errors());

           ActivityLog::storeLog(
            $this->teamId,
            $this->userAuth->id,
            null,
            null,
            'Feedback Settings',
            $this->locationId,
            ActivityLog::SETTINGS,
            json_encode($e->errors()),
            $this->userAuth
        );

         $this->dispatch('error', [
            'icon' => 'error',
            'title' => 'Validation Error',
            'text' => implode("\n", Arr::flatten($e->errors()))
        ]);
    } catch (\Throwable $e) {
        // Catch any other error
        // dd($e->getMessage(), $e->getTraceAsString());
             ActivityLog::storeLog(
            $this->teamId,
            $this->userAuth->id,
            null,
            null,
            'Feedback Settings',
            $this->locationId,
            ActivityLog::SETTINGS,
            json_encode($e->getMessage()),
            $this->userAuth
        );

        // Dispatch SweetAlert with generic error message
        $this->dispatch('error', [
            'icon' => 'error',
            'title' => 'Error',
            'text' => $e->getMessage()
        ]);
    }
}

   public function sendManualMail()
  {


    try {
        if($this->manualMail){
        if (!empty($this->manual_trigger)) {

           $prefix = preg_replace('/[0-9]/', '', $this->manual_trigger);   // Extract letters e.g. GP
            $number = preg_replace('/[^0-9]/', '', $this->manual_trigger);  // Extract digits e.g. 0009


            $queueStorageData = QueueStorage::where('status', Queue::STATUS_CLOSE)
                ->where('team_id', $this->teamId)
                ->where('locations_id', $this->locationId)
                ->whereDate('closed_datetime', Carbon::now())
                ->where(function ($query) use ($prefix, $number) {
                    $query->when($prefix, function ($q) use ($prefix, $number) {
                        // Case when prefix exists
                        $q->where('start_acronym', $prefix)
                        ->where('token', $number);
                    }, function ($q) use ($number) {
                        // Case when prefix is null/empty (only number given)
                        $q->where('token', $number);
                    })
                    ->orWhere('name', $number)   // keep your name match
                    ->orWhere('phone', $number); // keep your phone match
                })
                ->first();

            if (!empty($queueStorageData)) {
                $userDetails = json_decode($queueStorageData?->json, true);

                $data = [
                    'to_mail'      => $userDetails['email'] ?? $userDetails['Email'] ?? null,
                    'name'         => $queueStorageData?->name,
                    'phone'        => $queueStorageData?->phone,
                    'phone_code'   => $queueStorageData->phone_code ?? '91',
                    'locations_id' => $queueStorageData->locations_id,
                ];

                $logData = [
                    'team_id'         => $this->teamId,
                    'location_id'     => $this->locationId,
                    'user_id'         => $queueStorageData->served_by,
                    'customer_id'     => $queueStorageData->created_by,
                    'queue_id'        => $queueStorageData->queue_id,
                    'queue_storage_id'=> $queueStorageData->id,
                    'email'           => $userDetails['email'] ?? $userDetails['Email'] ?? null,
                    'contact'         => $queueStorageData->phone,
                    'type'            => MessageDetail::CUSTOM_TYPE,
                    'event_name'      => 'Manual Trigger Feedback link',
                ];

                // Send main notification
                // $this->sendNotification($data, 'rating survey', $logData);

                if (!empty($data['to_mail']) || !empty($data['phone'])) {
                    $data['feedback_link'] = request()->getHost() . '/rating/survey?code=' . base64_encode($queueStorageData?->id);
                    $this->sendNotification($data, 'rating survey', $logData);
                }

                // Success SweetAlert
                $this->dispatch('success', [
                    'icon'  => 'success',
                    'title' => 'Mail Sent',
                    'text'  => 'Feedback link sent successfully.'
                ]);
            } else {
                // No matching record found
                $this->dispatch('error', [
                    'icon'  => 'warning',
                    'title' => 'Not Found',
                    'text'  => 'No matching record found for the provided input.'
                ]);
            }
        } else {

            // Manual trigger value empty
            $this->dispatch('error', [
                'icon'  => 'warning',
                'title' => 'Missing Data',
                'text'  => 'Please enter a token, name, or phone to send feedback.'
            ]);
        }

    }else{
        $this->dispatch('error', [
                'icon'  => 'warning',
                'title' => 'System is Disabled',
                'text'  => 'Please Enable the Post Interaction.'
            ]);
    }
    } catch (\Throwable $e) {


        // Error SweetAlert
        $this->dispatch('error', [
            'icon'  => 'error',
            'title' => 'Error Sending Mail',
            'text'  => $e->getMessage()
        ]);
    }
}

     public function sendNotification($data, $type, $logData = null)
    {

        if (!empty($logData)) {
            $logData['channel'] = 'email';
            // $logData['status'] = MessageDetail::SENT_STATUS;
            // MessageDetail::storeLog($logData);
        }

            if (isset($data['to_mail']) && $data['to_mail'] != '') {
                SmtpDetails::sendMail($data, $type, '',  $logData['team_id'],$logData);
            }
            if (!empty($data['phone'])) {
                $logData['channel'] = 'sms';
                $logData['status'] = MessageDetail::SENT_STATUS;
                $data['location_id'] = $data['locations_id'];
                SmsAPI::sendSms($logData['team_id'], $data, $type, $type, $logData);
            }

    }

    public function render()
    {
        return view('livewire.feedback-settings');
    }
}
