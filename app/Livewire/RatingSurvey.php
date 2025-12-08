<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{FeedbackQuestion, Team, Queue as QueueDB, Rating, QueueStorage, FeedbackSetting, Domain, ActivityLog};
use Livewire\Attributes\On;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowRatingAlert;

#[Layout('components.layouts.custom-layout')]
class RatingSurvey extends Component
{
    #[Title('Rating')]

    public $feedbackQuestions;
    public $feedbackSetting;
    public $questions = [];
    public $response = [];
    public $queueId;
    public $teamId;
    public $queueDB;
    public $locationId;
    public $comment;
    public $average;
    public $checkRating;
    public $showCommentBox = false;
    public $showquestion = true;
    public $showCommentSection = false;
    public $showAlert = false;
    public $rating_style = 'stars';

    public function mount()
    {
        $code = request()->get('code'); // or $this->code if it's bound via Livewire
        $this->queueId = base64_decode($code);
   
		
        $location = request()->get('loc');
		// $this->locationId = Session::get('selectedLocation');
        $this->queueDB = QueueStorage::where(['id' => $this->queueId])->first();
		
        if (empty($this->queueDB)){

            abort(404);
		}

        $this->teamId = $this->queueDB->team_id_id ?? tenant('id');
        $this->locationId = $this->queueDB->locations_id;
        $this->average = 0;
        $this->checkRating = Rating::where(['team_id' => $this->teamId, 'queue_storage_id' => $this->queueId])->exists();

        if ($this->checkRating) {
            return $this->redirect('/rating/already_submitted');
        }

        $this->feedbackSetting = FeedbackSetting::where(['team_id' => $this->teamId, 'location_id' => $this->locationId])->first();
       
		if (empty($this->feedbackSetting)) {
            abort(403);
        }
        $this->showCommentBox = $this->feedbackSetting->enable_comment_box;
        $this->showAlert = $this->feedbackSetting->enable_comment_box;
        $this->rating_style = $this->feedbackSetting->rating_style;

        $this->feedbackQuestions = FeedbackQuestion::where(['team_id' => $this->teamId, 'location_id' => $this->locationId])->first();
        if (!empty($this->feedbackQuestions)) {

            $this->questions = json_decode($this->feedbackQuestions->questions, true);
            $this->showquestion = true;
        } else {
            $this->showquestion = false;
        }
    }

    public function render()
    {
        return view('livewire.rating-survey');
    }

    #[On('check-average')]
    public function checkaverage()
    {
        $ratings = [];

        // Extract all the numeric values
        foreach ($this->response as $item) {
            foreach ($item as $value) {
                $ratings[] = (int) $value;
            }
        }
        $threshold = $this->feedbackSetting->rating_comment_box_threshold ?? 0;
        // Calculate average and cast to integer to remove decimal
        $this->average = count($ratings) > 0 ? (int) round(array_sum($ratings) / count($ratings)) : 0;

        if ((int)$this->average <= (int)$threshold) {
            $this->showCommentSection = true;
            $this->showquestion = false;
        } else {
            $this->showquestion = false;
            $this->dispatch('submit-rating');
        }
    }

    #[On('rating-submit')]
    public function create()
    {

        try {
            $todayDateTime = Carbon::now();

            // Optional: delete all previous ratings if you want to replace entirely
            // You can remove this if using updateOrInsert only.
            // Rating::where([
            //     'queue_storage_id' => $this->queueDB?->id,
            //     'team_id' => $this->teamId,
            // ])->delete();

            $this->checkRating = Rating::where(['team_id' => $this->teamId, 'queue_storage_id' => $this->queueId])->exists();

            if ($this->checkRating) {
                return $this->redirect('/rating/already_submitted');
            }


            $comment = $this->comment ?? null;
            foreach ($this->response as $key => $questionResponse) {

                foreach ($questionResponse as $question => $value) {
                    if ($question === 'comment') continue;

                    Rating::insert(
                        [
                            'queue_id' => $this->queueDB?->queue_id,
                            'queue_storage_id' => $this->queueDB?->id,
                            'user_id' => $this->queueDB?->closed_by,
                            'question' => $question,
                            'team_id' => $this->teamId,
                            'location_id' => $this->locationId,
                            'rating' => $value,
                            'comment' => $comment,
                            'created_at' => $todayDateTime,
                            'updated_at' => $todayDateTime,
                        ]
                    );
                }
            }

            $feedback_alert_enable = $this->feedbackSetting->feedback_alert_enable == 1 ? true : false;
            $feedback_alert_email = $this->feedbackSetting->feedback_alert_email ?? '';
             Log::info('rating survey feedback_alert_email'.$feedback_alert_email);
            if (!empty($feedback_alert_email) && $feedback_alert_enable) {
                $ratings = [];

                // Extract all the numeric values
                foreach ($this->response as $item) {
                    foreach ($item as $value) {
                        $ratings[] = (int) $value;
                    }
                }
                // Calculate average and cast to integer to remove decimal
                $this->average = count($ratings) > 0 ? (int) round(array_sum($ratings) / count($ratings)) : 0;
                $alert_threshold = $this->feedbackSetting->feedback_alert_threshold ?? 0;
                if ((int)$this->average <= (int)$alert_threshold) {

                    $token =  $this->queueDB?->tokenacronym . $this->queueDB?->token;
                    $domain = Domain::where('team_id', $this->teamId)->value('domain');
                    $teamName = tenant('name') ?? '';

                    Mail::to($feedback_alert_email)->send(new LowRatingAlert(
                        $this->average,
                        $this->comment ?? '',
                        $this->teamId,
                        $this->locationId,
                        now(),
                        $this->response,
                        $token, // queue number
                        $this->queueDB?->name . ' / ' . $this->queueDB?->phone, // customer info
                        $alert_threshold,
                        $domain,
                        $teamName,
                    ));
                }
            }
 //store activity log 
 Log::info('rating survey');
            ActivityLog::storeLog($this->teamId, $this->queueDB?->closed_by, $this->queueDB?->id, $this->queueDB?->id, 'Rating Submitted', $this->locationId, ActivityLog::RATING_SUBMITTED, null, $this->queueDB?->closed_by);
            return $this->redirect('/rating/thank-you');
        } catch (\Throwable $ex) {
            Log::emergency($ex->getMessage());
             Log::info('rating survey error '.$ex->getMessage());
            //store activity log when error
            ActivityLog::storeLog($this->teamId, $this->queueDB?->closed_by, $this->queueDB?->queue_id, $this->queueDB?->id, $ex->getMessage(), $this->locationId, ActivityLog::RATING_SUBMITTED, $ex->getMessage(), $this->queueDB?->closed_by);
            return redirect()->back();
      
            // session()->flash('message', $ex->getMessage());
        }
    }
}
