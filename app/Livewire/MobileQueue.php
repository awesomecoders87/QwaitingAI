<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
    Category, Queue as QueueDB, SiteDetail, SmtpDetails, SmsAPI,User,Location,GenerateQrCode,QueueStorage,Country}
    ;
    use Illuminate\Validation\Rule;
    use Livewire\Rules\Numeric;
    use Carbon\Carbon;
    use Livewire\Attributes\On;

    use App\Events\QueueCreated;
    use App\Models\FormField;
    use DB;
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Support\Facades\Redis;
    use Illuminate\Support\Facades\Cache;
    use Log;
    use Auth;
    use Illuminate\Support\Facades\Session;
    use Filament\Facades\Filament;
    use App\Jobs\SendQueueNotification;
    use Livewire\Attributes\Layout;
    use Livewire\Attributes\Title;
    
    #[Layout('components.layouts.custom-layout')]
    class MobileQueue extends Component
 {
        
        #[Title('Queue')]
        
        public $selectedCategoryId;
        public $firstChildren;
        public $secondChildren, $secondChildId, $thirdChildId, $teamId;
        public $name = '';
        public $phone = '';
        public $divOne;
        public $domainSlug = '';
        public $categoryName = '';
        public $secondCategoryName = '';
        public $thirdCategoryName = '';

        public $locationStep = true;
        public $firstStep = false;
        public $secondStep = false;
        public $thirdStep = false;
        public $fourthStep = false;
        public $currentPage = Category::STEP_1;

        public $totalLevelCount = Category::STEP_1;
        public $dynamicForm = [];
        public $firstCategories;

        public $dynamicProperties = [];

        public $siteDetails;
        public $showTicketText;
        public $showTicketText_2;
        public $token_start;
        public $last_token;
        public $booking_setting = SiteDetail::STATUS_YES;
        public $acronym = '';
        public $fontSize = 'text-3xl';
        public $fontFamily = '';
        public $borderWidth = 'border-4';
        public $countCatID = 0;
        public $fieldCatName = '';
        public $counterID = 0;
        public $header = true;
        protected $listeners = [ 'changeDate' => 'changeDate' ];
        public $email;

        public $allLocations = [];
        public $location;
        public $locationName;
        public $location_detail;
        public $getlocation;
        public $url_string;
        public $getseconds;

        public $latitude;
        public $longitude;
        public $validDistance = 100;
        public $phone_code=null;
        public $countryCode;
        public $selectedCountryCode; 
   


        public function mount($url_string = null, $getlocation = null,$getseconds=null) {

            $this->showFormQueue = false;
           
            $this->teamId = tenant('id');

            $this->resetDynamic();

            $this->siteDetails = SiteDetail::getMyDetails( $this->teamId );
            if ( !empty( $this->siteDetails ) ) {
                $this->fontSize  = $this->siteDetails->category_text_font_size ?? $this->fontSize;
                $this->borderWidth  = $this->siteDetails->category_border_size ?? $this->borderWidth;
                $this->fontFamily  = $this->siteDetails->ticket_font_family ?? $this->fontFamily;
                $this->booking_setting =  $this->siteDetails->booking_system ?? SiteDetail::STATUS_YES;

            }
            if(is_null($getlocation)){
                $this->location = Session::get( 'selectedLocation' );
                Session::forget( 'hidetoggle');
            }else{
                $this->location = $getlocation;
                Session::put( 'hidetoggle',1 );
                Session::put('selectedLocation',$this->location);
            }

   
            // $this->dispatch('header-show-on-mobile');

            if (!empty( $this->location ) ) 
            $this->locationName =  Location::locationName( $this->location );

            $this->locationStep = false;
            $this->firstStep = true;

            $this->firstCategories = Category::getFirstCategoryN( $this->teamId, $this->location );
            $this->location_detail=Location::where('id',$this->location)->first();
           
            if ( is_null($getlocation) && (empty($this->location) || !Auth::check())) {
                $this->location = '';
                $this->allLocations = Location::where( 'team_id', $this->teamId )->pluck( 'location_name', 'id' );
                $this->locationStep = true;
                $this->firstStep = false;
            }

            $this->validDistance = GenerateQrCode::getRadius($this->teamId, $this->location );
            if(empty($this->validDistance)){
                $this->validDistance = 100;
            }
                 
            $this->countryCode = Country::query()->pluck('phonecode');
            $this->siteData = SiteDetail::where('team_id', $this->teamId)->first();
            $this->selectedCountryCode = $this->siteData->country_code ?? null;
            $this->phone_code = $this->selectedCountryCode;
        
             $this->redirect(url('queue') . '?mobile=true');
            // $this->dispatch('redirect-to-queue');
            

        }
    #[On('locationCodChange')] 
        public function locationCodChange($latitude, $longitude)
        {
            $this->latitude = $latitude;
            $this->longitude = $longitude;


     if (!empty($this->location) && !empty($this->accountSetting) && $this->isWithinRadius($this->latitude, $this->longitude)) {
            // Allow QR code scanning
            $this->dispatch('enable-qr-scanning');
        } else {
            // Deny QR code scanning
            $this->dispatch('deny-qr-scanning');
            
        }
            // \Log::info("Latitude: $this->latitude, Longitude: $this->longitude");
        }
        #[On('locationError')] 
        public function locationError()
        {
            // $this->dispatch('deny-qr-scanning'); 
        }
            // \Log::info("Latitude: $this->latitude, Longitude: $this->longitude");
        
        

        private function isWithinRadius($lat, $lng, $radius = 100)
        {
            $radius = $this->validDistance;
            // Fetch the central location details from the database
            $location_detail = Location::where('id', $this->location)->first();
             if(isset($location_detail) && !empty($location_detail->latitude) && !empty($location_detail->longitude)){
            $centralLat = $location_detail->latitude; // Latitude of the central point
            $centralLng = $location_detail->longitude; // Longitude of the central point

            // Calculate the distance between the central point and the given coordinates
            $distance = $this->calculateDistance($centralLat, $centralLng, $lat, $lng);

            // Convert radius to meters (radius is already in meters, no conversion needed)
            $radiusInMeters = $radius;

            // Log the values for debugging
            // dd(round($distance) . ' / ' . round($radiusInMeters));

            // Compare the distance with the radius
            return round($distance) <= round($radiusInMeters);

            }else{
            return false;
        }
        }

        private function calculateDistance($lat1, $lng1, $lat2, $lng2)
        {
            $earthRadius = 6371000; // Earth radius in meters

            // Convert latitude and longitude from degrees to radians
            $dLat = deg2rad($lat2 - $lat1);
            $dLng = deg2rad($lng2 - $lng1);

            // Haversine formula to calculate the distance
            $a = sin($dLat / 2) * sin($dLat / 2) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                sin($dLng / 2) * sin($dLng / 2);

            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            $distance = $earthRadius * $c; // Distance in meters

            return $distance;
        }
    
        public function handleLocationError($errorMessage)
        {
            \Log::error("Geolocation error: $errorMessage");
            dd("Geolocation error:". $errorMessage);
            // You can handle the error message as needed, e.g., show it to the user
        }

        public function rules()
          {

            $rules = [];
            foreach ( $this->dynamicProperties as $fieldName => $value ) {
                $fieldId = explode( '_', $fieldName )[ 1 ];

                $field = $this->findDynamicFormField( $fieldId );

                if ( $field ) {
                    $this->addDynamicFieldRules( $rules, $fieldName, $field );
                }
            }
            return $rules;
        }

        private function addDynamicFieldRules( &$rules, $fieldName, $field )
 {
            $fieldRules = [];

            switch( $field[ 'type' ] ) {
                case FormField::TEXT_FIELD:
                $this->addTextFieldRules( $fieldRules, $field );
                break;
                case FormField::SELECT_FIELD:
                $this->addSelectFieldRules( $fieldRules, $field );
                break;
                case FormField::NUMBER_FIELD:
                $this->addNumberFieldRules( $fieldRules, $field );
                break;
                case FormField::TEXTAREA_FIELD:
                $this->addTextAreaFieldRules( $fieldRules, $field );
                break;
            }
            $rules[ "dynamicProperties.$fieldName" ] = $fieldRules;
        }

        
        public function updatedLocation( $value )
        {
            $this->location = $value;
            $this->firstCategories = Category::getFirstCategoryN( $this->teamId, $this->location );
            $this->locationName =   Location::locationName( $value );
            $this->locationStep = false;
            $this->firstStep = true;

        }

        public function addTextAreaFieldRules( &$fieldRules, $field ) {
            $fieldRules[] = ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE )? 'required' : 'nullable';
            $this->validationRule( $fieldRules, $field );
        }

        private function addNumberFieldRules( &$fieldRules, $field ) {
            $fieldRules[] = ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE )? 'required' : 'nullable';
            $this->validationRule( $fieldRules, $field );
        }

        private function validationRule( &$fieldRules, $field ) {
            if ( !empty( $field[ 'validation' ] ) && $this->isValidRegex( $field[ 'validation' ] ) ) {
                $delimiter = '/';
                $regexPattern = $field[ 'validation' ];
                if ( $regexPattern[ 0 ] !== $delimiter || $regexPattern[ strlen( $regexPattern ) - 1 ] !== $delimiter ) {
                    $regexPattern = $delimiter . $regexPattern . $delimiter;
                }
                \Log::debug( 'Regex Pattern: ' . $regexPattern );
                $fieldRules[] = 'regex:' . $regexPattern;
            }
        }

        private function isValidRegex( $pattern ) {
            \Log::debug( 'Checking Regex Pattern: ' . $pattern );
            return @preg_match( $pattern, '' ) !== false;
        }

        private function addDateFieldRules( &$fieldRules, $field ) {
            $fieldRules[] = ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE )? 'required' : 'nullable';
            $this->validationRule( $fieldRules, $field );

        }

        private function addTextFieldRules( &$fieldRules, $field )
 {
            if ( str_contains( strtolower( $field[ 'title' ] ), 'email' ) ) {
                if ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE ) {
                    $fieldRules[] = 'required';
                }
                $fieldRules[] = 'email';
            } else {
                if ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE ) {
                    $fieldRules[] = 'required';
                }

            }
            $this->validationRule( $fieldRules, $field );

        }

        private function addSelectFieldRules( &$fieldRules, $field )
 {
            $fieldRules[] = ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE )? 'required' : 'nullable';
            $this->validationRule( $fieldRules, $field );
        }

        public function messages()
 {
            $messages = [];

            foreach ( $this->dynamicProperties as $fieldName => $value ) {
                $fieldId = explode( '_', $fieldName )[ 1 ];

                $field = $this->findDynamicFormField( $fieldId );
                if ( $field ) {
                    $fieldTitle = $field[ 'title' ];
                    $messages[ "dynamicProperties.$fieldName.required" ] = "The {$fieldTitle} field is required.";
                    if ( str_contains( strtolower( $fieldTitle ), 'email' ) ) {
                        $messages[ "dynamicProperties.$fieldName.email" ] = "Invalid email address for {$fieldTitle}.";
                    }
                    $messages[ "dynamicProperties.$fieldName.regex" ] = "The {$fieldTitle} field is invalid.";
                    $messages[ "dynamicProperties.$fieldName.min" ] = "The {$fieldTitle} field must be at least :min characters.";
                    $messages[ "dynamicProperties.$fieldName.max" ] = "The {$fieldTitle} field must be at most :max characters.";
                }
            }
            return $messages;
        }

      
        protected function findDynamicFormField( $fieldId )
 {
            foreach ( $this->dynamicForm as $field ) {
                if ( $field[ 'id' ] == $fieldId ) {
                    return $field;
                }
            }
            return null;

        }

        public function render()
       {

            return view( 'livewire.mobile-queue' );
        }

        public function showFirstChild( $categoryId )
 {
            $this->selectedCategoryId = $categoryId;
            $this->firstChildren = Category::getPluckNames( $categoryId ,$this->location);

            if ( !empty( $this->firstChildren ) )
            $this->firstChildren =  $this->firstChildren->toArray();

            if ( empty( $this->firstChildren ) )
            $this->updateCurrentPage( Category::STEP_4 );
            else
            $this->updateCurrentPage( Category::STEP_2 );

            $this->currentPageFn( $this->currentPage );
            $this->secondChildId = null;
            $this->totalLevelIncFn();
        }

        public function totalLevelIncFn() {
            $this->totalLevelCount++;
        }

        public function totalLevelDecFn() {
            if ( $this->totalLevelCount > 0 )
            $this->totalLevelCount--;
        }

        public function showSecondChild( $childId )
 {
            $this->secondChildId = $childId;
            $this->secondChildren = Category::getPluckNames( $childId, $this->location );
            if ( !empty( $this->secondChildren ) )
            $this->secondChildren =  $this->secondChildren->toArray();

            if ( empty( $this->secondChildren ) )
            $this->updateCurrentPage( Category::STEP_4 );
            else
            $this->updateCurrentPage( Category::STEP_3 );

            $this->currentPageFn( $this->currentPage );
            $this->totalLevelIncFn();

        }

        public function showQueueForm( $secondCId ) {
            $this->updateCurrentPage( Category::STEP_4 );
            $this->currentPageFn( $this->currentPage );

            $this->thirdChildId = $secondCId;
        }

        public function saveQueueForm() {

            // if ( $this->siteDetails?->queue_form_display == SiteDetail::STATUS_YES )
            // $this->validate();
            $this->phone_code;

            try {
                $this->dispatch( 'queue:refresh' );

                // DB::beginTransaction();
                $formattedFields = [];
                foreach ( $this->dynamicProperties as $key => $value ) {
                    $fieldName = preg_replace( '/_\d+/', '', $key );

                    $formattedFields[ $fieldName ] = $value;

                }

                $this->name = $formattedFields[ 'name' ] ?? null;
                $this->phone = $formattedFields[ 'phone' ] ?? null;
                $this->email = isset( $formattedFields[ 'email' ] ) ? $formattedFields[ 'email' ] : ( isset( $formattedFields[ 'Email' ] ) ? $formattedFields[ 'Email' ] : null );

                $jsonDynamicData = json_encode( $formattedFields );

                if ( $this->booking_setting == QueueDB::STATUS_NO ) {
                    if ( !empty( $this->secondChildId ) ) {
                        $this->acronym = Category::viewAcronym( $this->secondChildId );
                    } elseif ( !empty( $this->selectedCategoryId ) ) {
                        $this->acronym = Category::viewAcronym( $this->selectedCategoryId );
                    }
                } else {
                    $this->acronym = SiteDetail::DEFAULT_WALKIN_A;
                }

                $lastToken = QueueDB::getLastToken( $this->teamId, $this->acronym,$this->location);
                $token_digit = $this->siteDetails?->token_digit ?? 3;
                $isExistToken = true; 
                while ($isExistToken) {
                    $newToken = QueueDB::newGeneratedToken($lastToken, $this->siteDetails?->token_start, $token_digit);
                    
                    if (strlen($newToken) > $token_digit) {
                        $this->dispatch('swal:ticket-generate', [
                            'title' => 'Oops...',
                            'text' => 'Unable to create more tickets',
                            'icon' => 'error'
                        ]);
                        return;
                    }
                
                    $isExistToken = QueueDB::checkToken($this->teamId, $this->acronym, $newToken,$this->location );
                    Log::emergency("Checking if token exists: " . $newToken . " - Exists: " . ($isExistToken ? 'Yes' : 'No'));
                
                    if ($isExistToken) {
                        $lastToken = $newToken;
                        Log::emergency("Token already exists, generating a new token based on last token: " . $lastToken);
                    } else {
                        $this->token_start = $newToken;
                        $isExistToken = false;
                    }
                }
                 $todayDateTime = Carbon::now();
                 $nextPrioritySort = $this->getNextPrioritySort($this->selectedCategoryId);
    
                
                $storeData =[
                    'name'=>$this->name,
                    'phone'=>$this->phone,
                    'phone_code'=>$this->phone_code,
                    'category_id'=>$this->selectedCategoryId ?? null,
                    'sub_category_id'=>$this->secondChildId ?? null,
                    'child_category_id'=>$this->thirdChildId ?? null,
                    'team_id'=> $this->teamId,
                    'token' => $this->token_start,
                    'token_with_acronym' => $this->booking_setting == QueueDB::STATUS_NO ? QueueDB::LABEL_YES : QueueDB::LABEL_NO,
                    'json'=>$jsonDynamicData,
                    'to_mail'=>$this->email ?? '',
                    'arrives_time'=>$todayDateTime,                    
                    'datetime'=>$todayDateTime,
                    'start_acronym'=> $this->acronym,
                    'ticket_mode'=>QueueDB::TICKET_MODE_MOBILE,
                    'locations_id'=> $this->location,
                    'priority_sort'=> $nextPrioritySort,

                ];

                $queueCreated = QueueDB::storeQueue([
                    'team_id'=> $this->teamId,
                    'token' => $this->token_start,
                    'token_with_acronym' => $this->booking_setting == QueueDB::STATUS_NO ? QueueDB::LABEL_YES : QueueDB::LABEL_NO,
                    'locations_id'=> $this->location,
                    'arrives_time'=>$todayDateTime,
                ]  );
                $queueStorage =  QueueStorage::storeQueue( array_merge($storeData ,['queue_id'=>$queueCreated->id]));
                QueueCreated::dispatch( $queueStorage );
              
                // $this->counterID =  QueueStorage::assignCounterToQueue( $queueStorage->id,$this->location );

                $queueStorage->counter_id = $this->counterID;
                $queueStorage->save();
                if ( !empty( $this->thirdChildId ) )
                $this->thirdCategoryName = Category::viewCategoryName( $this->thirdChildId );
                if ( !empty( $this->secondChildId ) )
                $this->secondCategoryName = Category::viewCategoryName( $this->secondChildId );
                if ( !empty( $this->selectedCategoryId ) )
                $this->categoryName =  Category::viewCategoryName( $this->selectedCategoryId );

                if ( $this->siteDetails?->category_estimated_time == SiteDetail::STATUS_YES )
                $this->determineCategoryColumn();

                if ( $this->siteDetails?->counter_estimated_time == SiteDetail::STATUS_NO )
                $this->counterID  = 0;

                $pendingCount = QueueStorage::countPending( $this->teamId, $queueStorage->id, $this->countCatID, $this->fieldCatName, '',$this->location ) ;
               
                if ( !empty( $this->siteDetails ) ) {
                    if ( $this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES ) {
                        $estimate_time = $this->siteDetails->estimate_time ?? 0 ;
                        $waitingTime =  $estimate_time * $pendingCount;
                    }
                }

                Log::emergency("waitingTime " . $waitingTime);

                $queueStorage->waiting_time = $waitingTime;
                $queueStorage->save();
                $storeData =array_merge($storeData ,['waiting_time'=>$waitingTime]);

                $this->resetForm();
                if(!empty($storeData['to_mail'])){

                    $datanew =[
                        'to_mail' =>$storeData['to_mail'],
                        'message' =>"queue created and token number is ".$storeData['token']
                    ];
                    $type = 'ticket created';
                    $teamId = $this->teamId; // Replace with actual team ID
             
                    // Dispatch the job
               dispatch(new SendQueueNotification($storeData, $type, $teamId))->onQueue('default')->delay(now()->addSeconds(30));
                }
                if(!empty($storeData['phone'])){
               SmsAPI::sendSms( $this->teamId, $storeData,'ticket created','ticket created');
                }
                // DB::commit();
            //    $this->redirect('/visits/'. Crypt::encrypt($queueCreated->id));
               $this->redirect('/visits/'. base64_encode($queueCreated->id));

            } catch( \Exception $ex ) {
                // DB::rollBack();
               
            Log::emergency("Queue generate issue on Mobile");
            Log::emergency($ex->getMessage());
                $this->dispatch( 'swal:ticket-generate', [
                    'title'=>'Oops...',
                    'text'=>'Unable to generate ticket. Please contact to the admin',
                    'icon'=>'error'
                ] );
            }

        }

        public function resetDynamic() {
            $this->dynamicForm = FormField::getFields( $this->teamId );
            foreach ( $this->dynamicForm as $field ) {
                $propertyName = $field[ 'title' ] . '_' . $field[ 'id' ];
                $this->dynamicProperties[ $propertyName ] = '';
            }
        }

        public function determineCategoryColumn() {
            if ( !empty( $this->thirdChildId ) ) {
                $this->fieldCatName = 'child_category_id';
                $this->countCatID =  $this->thirdChildId;
            } else if ( !empty( $this->secondChildId ) ) {
                $this->fieldCatName = 'sub_category_id';
                $this->countCatID =  $this->secondChildId;
            } else {
                $this->fieldCatName = 'category_id';
                $this->countCatID =  $this->selectedCategoryId;
            }
        }

        public function sendNotification( $data ) {
            if ( isset( $data[ 'to_mail' ] ) && $data[ 'to_mail' ] != '' )
            SmtpDetails::sendMail( $data, 'Ticket Created', 'ticket-created', $this->teamId );

            if ( !empty( $this->phone ) ) {
                SmsAPI::sendSms( $this->teamId, $data,'Ticket Created','Ticket Created' );

                // SmsAPI::sendSmsWhatsApp( $this->teamId, $data );
            }
        }

        public function resetForm() {
            $this->name = $this->phone = '';
            $this->dynamicProperties = [];
            $this->resetDynamic();

        }
        #[ On( 'refresh-component' ) ]

        public function refreshComponent() {
            $this->firstStep = true;
            $this->secondStep = $this->thirdStep = $this->fourthStep = false;
            $this->currentPage = $this->totalLevelCount = Category::STEP_1;
            $this->selectedCategoryId = $this->secondChildId = $this->thirdChildId = null ;
        }

        public function updateCurrentPage( $page ) {
            if ( $page == Category::STEP_4 ) {

                if ( $this->siteDetails?->queue_form_display == SiteDetail::STATUS_NO ) {
                    $this->saveQueueForm();
                    return;
                }
            }
            $this->currentPage = $page;
        }

        public function currentPageFn( $page ) {

            if ( $page == Category::STEP_2 ) {
                $this->secondStep  = true;
                $this->firstStep = $this->thirdStep = $this->fourthStep = false;
            } else if ( $page ==  Category::STEP_3 ) {
                $this->thirdStep = true;
                $this->firstStep = $this->secondStep = $this->fourthStep = false;

            } else if ( $page ==   Category::STEP_4 ) {
                $this->fourthStep = true;
                $this->firstStep = $this->secondStep = $this->thirdStep = false;

            } else {
                $this->firstStep = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;
            }
        }

        public function goBackFn( $page ) {

            $this->totalLevelDecFn();
            if ( $page ==  Category::STEP_2 ) {
                $this->firstStep  = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;
            } else if ( $page ==  Category::STEP_3 ) {
                $this->secondStep = true;
                $this->firstStep = $this->thirdStep = $this->fourthStep = false;

            } else if ( $page ==   Category::STEP_4 ) {
                $this->thirdStep = true;
                $this->firstStep = $this->secondStep = $this->fourthStep = false;

            } else {
                $this->firstStep = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;
            }

        }

        public function changeDate( $selectedDate )
 {

            foreach ( $this->dynamicForm as $form ) {
                if ( $form[ 'type' ] == FormField::DATE_FIELD ) {
                    $this->dynamicProperties[ $form[ 'title' ].'_'.$form[ 'id' ] ] = $selectedDate;
                    break;
                }
            }

        }
         /***  set soring of queue code */
         protected function generateSequencePattern()
         {
             $categories = Category::where('team_id', $this->teamId)
                            ->where(function ($query) {
                                $query->whereNull('parent_id')
                                    ->orWhere('parent_id', '');
                            })
                         ->whereJsonContains('category_locations', $this->location)
                         ->orderBy('sort')
                         ->pluck('visitor_in_queue', 'id');
         
             return $categories;
         }
 
         public function getNextPrioritySort($categoryId)
         {
   
   
           $category = Category::find($categoryId);
             $nextserial = 1;
             // Generate the sequence pattern dynamically
             $sequencePattern = $this->generateSequencePattern();
   
             $filteredCategories = $sequencePattern->except($category->id);
                     
             // Sum the 'visitor_in_queue' values of the remaining categories
             $sumVisitorInQueue = $filteredCategories->sum() + $sequencePattern[$category->id];
                
             // Fetch existing queues for the current team and location
             $queues = QueueStorage::where('team_id', $this->teamId)
                 ->where('locations_id', $this->location)
                 ->where('category_id', $category->id)
                 ->whereNotNull('priority_sort')
                 ->orderBy('priority_sort')
                 ->whereDate('created_at', Carbon::today())
                 ->pluck('priority_sort')
                 ->toArray();
   
                 if(!empty($queues)){
                     $maxValue = max($queues);
                     if( $maxValue == 0){
                         $maxValue = $nextserial;
                         $queues = [];
                     }
                 }else{
                     $maxValue = $nextserial;
                 }
               
                
         if($sequencePattern[$category->id] == 1){
             if(!empty($queues)){
                return $nextserial = $maxValue + $sumVisitorInQueue;
             }else{
                 // Convert the collection to an array
                 $categoriesArray = $sequencePattern->toArray();
   
                 // Slice the array to get values before the key 44
                 $slicedArray = array_slice($categoriesArray, 0, array_search($category->id, array_keys($categoriesArray)));
   
                 // Sum the values in the sliced array
                 $sumBefore = array_sum($slicedArray);
                 // dd($sumBefore);
                return $nextserial = $maxValue +$sumBefore;
             }
         }elseif($sequencePattern[$category->id] > 1){
            
             $countserial = 0;
             if(!empty($queues)){
                for($i = $maxValue; $i>=1;$i--){
                 $checkSort = QueueStorage::where('team_id', $this->teamId)
                 ->where('locations_id', $this->location)
                 ->where('category_id', $category->id)
                 ->whereNotNull('priority_sort')
                 ->whereDate('created_at', Carbon::today())
                 ->where('priority_sort', $i)
                 ->exists();
                 if($checkSort){
                     $countserial +=1;
                 }else{
                     break;
                 }
                }
             //   dd($countserial.'/'.$sequencePattern[$category->id].'/'.$maxValue .'/'.$sumVisitorInQueue);
                if($countserial == $sequencePattern[$category->id]){
                  return $nextserial = $maxValue + $sumVisitorInQueue - 1;
                }else{
                 return $nextserial = $maxValue + 1;
                }
             }else{
                 $categoriesArray = $sequencePattern->toArray();
   
                 // Slice the array to get values before the key 44
                 $slicedArray = array_slice($categoriesArray, 0, array_search($category->id, array_keys($categoriesArray)));
   
                 // Sum the values in the sliced array
                 $sumBefore = array_sum($slicedArray);
                return $nextserial = $maxValue + $sumBefore;
             }
         }
   
         }
      
    }
