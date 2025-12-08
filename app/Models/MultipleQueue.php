<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ {
    Category, Team, Queue as QueueDB, SiteDetail, SmtpDetails, SmsAPI, GenerateQrCode, CategoryFormField, Location}
    ;
    use Illuminate\Validation\Rule;
    use Livewire\Rules\Numeric;
    use Carbon\Carbon;
    use Livewire\Attributes\On;
    use App\Events\QueueCreated;
    use App\Models\FormField;
    use DB;
    use Illuminate\Support\Facades\Auth;

    use Illuminate\Support\Facades\Redis;
    use Illuminate\Support\Facades\Cache;
    use Log;
    use Illuminate\Support\Facades\Session;
    use Filament\Facades\Filament;
    use Livewire\Attributes\Computed;

    class MultipleQueue extends Component
 {
        public $selectedCategoryId;

        public $secondChildId, $thirdChildId;
        public $teamId;
        public $name = '';
        public $phone = '';
        public $divOne;
        public $categoryName = '';
        public $secondCategoryName = '';
        public $thirdCategoryName = '';

        public $qrCodeDetailsCache;
        public $locationStep = true;
        public $firstStep = false;
        public $secondStep = false;
        public $thirdStep = false;
        public $fourthStep = false;
        public $currentPage = Category::STEP_1;

        public $totalLevelCount = Category::STEP_1;
        public $dynamicForm = [];
        // public $firstCategories;

        public $dynamicProperties = [];

        public $showTicketText;
        public $showTicketText_2;
        public $token_start;
        public $last_token;
        public $booking_setting;
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
        public $is_qr_code;
        public $qrcode_tagline;
        public $allCategories = [];
        public $allLocations = [];
        public $location;
        public $locationName;
        public $selectedFirstChildren = []; 
        public $selectedSecondChildren = []; 
      
        public function mount() {
            $this->showFormQueue = false;
           $this->teamId = Team::getTeamId($this->domainSlug);
            if (empty($this->teamId)) {
                abort(404);
            }
            $this->locationName = '';
            $this->booking_setting = SiteDetail::STATUS_YES;
           
            $this->location = Session::get('selectedLocation');
            if (!empty($this->location)) {
                $this->locationName = Location::locationName($this->location);
            }
        
            $this->locationStep = false;
            $this->firstStep = true;
        
            if (empty($this->location) && !Auth::check()) {
                $this->location = '';
                $this->allLocations = Location::getLocations($this->teamId);
                $this->locationStep = true;
                $this->firstStep = false;
            }
       
        
            if (!empty($this->siteDetails)) {
                $this->fontSize = $this->siteDetails->category_text_font_size ?? $this->fontSize;
                $this->borderWidth = $this->siteDetails->category_border_size ?? $this->borderWidth;
                $this->fontFamily = $this->siteDetails->ticket_font_family ?? $this->fontFamily;
                $this->booking_setting = $this->siteDetails->booking_system ?? SiteDetail::STATUS_YES;
                if (!isset($this->qrCodeDetailsCache)) {
                    $this->qrCodeDetailsCache = GenerateQrCode::viewGeneratorCode($this->teamId);
                    if(empty($this->qrCodeDetailsCache)){
                       $this->is_qr_code = SiteDetail::STATUS_NO;
                    }else{

                        $this->is_qr_code = $this->siteDetails->is_qr_code ?? SiteDetail::STATUS_NO;
                    }
                }
                $this->qrcode_tagline = $this->siteDetails->qrcode_tagline;
            }
        }
        #[Computed]
        public function domainSlug(){
           return Team::getSlug();
        }
     #[Computed]
     public function siteDetails(){

            return SiteDetail::getMyDetails($this->teamId);
     }

     #[Computed]
        public function qrCodeDetails()
     {
         if (!isset($this->qrCodeDetailsCache)) {
             $this->qrCodeDetailsCache = GenerateQrCode::viewGeneratorCode($this->teamId);
             if(empty($this->qrCodeDetailsCache)){
                $this->is_qr_code = SiteDetail::STATUS_NO;
             }
         }
 
         return $this->qrCodeDetailsCache;
     }
     #[Computed]
     public function firstCategories(){
        return Category::getFirstCategoryN($this->teamId, $this->location);
     }
        public function rules()
        {

            try{
                 $rules = [];
                    foreach ( $this->dynamicProperties as $fieldName => $value ) {
                        $fieldId = explode( '_', $fieldName )[ 1 ];

                        $field = FormField::findDynamicFormField( $this->dynamicForm, $fieldId );

                        if ( $field ) {
                            FormField::addDynamicFieldRules( $rules, $fieldName, $field,$this->allCategories );
                        }
                    }
                    return $rules;
                }
                    catch(\Throwable $ex){
                        $this->dispatch( 'swal:ticket-generate', [
                            'title'=>'Oops...',
                            'text'=>'Unable to generate ticket due to invalid rules. Please contact to the admin',
                            'icon'=>'error'
                        ] );

                    }
                }

        public function messages()
 {
            $messages = [];

            foreach ( $this->dynamicProperties as $fieldName => $value ) {
                $fieldId = explode( '_', $fieldName )[ 1 ];

                $field = FormField::findDynamicFormField( $this->dynamicForm, $fieldId );
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
        
        public function updatedSelectedFirstChildren($values)
    {
    //    $this->selectedFirstChildren[] =$values;
        $categories = Category::whereIn('parent_id', $this->selectedFirstChildren);
        
        if (!empty($location)) {
            $categories->whereJsonContains('category_locations', $this->location);
        }
        
        $this->secondChildren = $categories->pluck('name', 'id');
        $this->thirdStep = true;

    }
        public function updatedSelectedSecondChildren($values)
    {
    //    $this->selectedFirstChildren[] =$values;
         dump($this->selectedSecondChildren);
        $subcategories = Category::whereIn('parent_id', $this->selectedSecondChildren);
        
        if (!empty($location)) {
            $subcategories->whereJsonContains('category_locations', $this->location);
        }
        
        $this->thirdChildren = $subcategories->pluck('name', 'id');

    }
        public function render()
 {

            return view( 'livewire.multiple-queue');
        }

        public function updatedLocation( $value )
        {
            $this->location = $value;
            $this->locationName =  Location::locationName( $value );
            Session::put('selectedLocation',$this->location);
            $this->locationStep = false;
            $this->firstStep = true;
            return redirect(request()->header('Referer'));
        }

        public function showFirstChild( $categoryId )
 {
            $this->selectedCategoryId = $categoryId;
            // dump('selectedCategoryId '.$this->selectedCategoryId);
            // dump($this->firstChildren);
            if ( empty( $this->firstChildren ) )
            $this->updateCurrentPage( Category::STEP_4 );
            else
            $this->updateCurrentPage( Category::STEP_2 );

            $this->currentPageFn( $this->currentPage );
            $this->secondChildId = null;
            $this->totalLevelIncFn();
        }
        #[Computed]      
        public function firstChildren()
        {
            $cacheKey = 'first_children_' . $this->selectedCategoryId . '_' . $this->location;
            return cache()->remember($cacheKey, now()->addMinutes(10), function () {
                $children = Category::getPluckNames($this->selectedCategoryId, $this->location);
                return !empty($children) ? $children->toArray() : [];
            });
        }
        #[Computed]      
        public function secondChildren()
        {
            $cacheKey = 'second_children_' . implode('_',$this->selectedFirstChildren) . '_' . $this->location;
            // return cache()->remember($cacheKey, now()->addMinutes(10), function () {
            //     $children = Category::getPluckNames($this->secondChildId, $this->location);
            //     return !empty($children) ? $children->toArray() : [];
            // });
            return cache()->remember($cacheKey, now()->addMinutes(10), function () {
                $children = Category::getPluckSubcategoriesNames($this->selectedFirstChildren, $this->location);
                return !empty($children) ? $children->toArray() : [];
            });
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
  

            if ( empty( $this->secondChildren ) ){
                $this->updateCurrentPage( Category::STEP_4 );
        
            }
            else
            $this->updateCurrentPage( Category::STEP_3 );

            $this->currentPageFn( $this->currentPage );
            $this->totalLevelIncFn();

        }

        public function showQueueForm( $secondCId ) {
            $this->thirdChildId = $secondCId;

            $this->updateCurrentPage( Category::STEP_4 );
            $this->currentPageFn( $this->currentPage );

        }

        public function saveQueueForm() {

            if ( $this->siteDetails?->queue_form_display == SiteDetail::STATUS_YES )
            $this->validate();

            try {
                $this->dispatch( 'queue:refresh' );

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
                $token_digit = $this->siteDetails?->token_digit ?? 4;  //4
                $isExistToken = true;

                while ( $isExistToken ) {
                    $newToken = QueueDB::newGeneratedToken( $lastToken, $this->siteDetails?->token_start, $token_digit );

                    if ( strlen( $newToken ) > $token_digit ) {
                        $this->dispatch( 'swal:ticket-generate', [
                            'title' => 'Oops...',
                            'text' => 'Unable to create more tickets'  ,
                            'icon' => 'error'
                        ] );
                        return;
                    }

                    $isExistToken = QueueDB::checkToken( $this->teamId, $this->acronym, $newToken );
                    Log::emergency( 'Checking if token exists: ' . $newToken . ' - Exists: ' . ( $isExistToken ? 'Yes' : 'No' ) );

                    if ( $isExistToken ) {
                        $lastToken = $newToken;
                        Log::emergency( 'Token already exists, generating a new token based on last token: ' . $lastToken );
                    } else {
                        $this->token_start = $newToken;
                        $isExistToken = false;
                    }
                }

                $todayDateTime = Carbon::now();
                $storeData = [
                    'name'=>$this->name,
                    'phone'=>$this->phone,
                    'category_id'=>$this->selectedCategoryId ?? null,
                    'sub_category_id'=>$this->secondChildId ?? null,
                    'child_category_id'=>$this->thirdChildId ?? null,
                    'team_id'=> $this->teamId,
                    'token' => $this->token_start,
                    'token_with_acronym' => $this->booking_setting == QueueDB::STATUS_NO ? QueueDB::LABEL_YES : QueueDB::LABEL_NO,
                    'json'=>$jsonDynamicData,
                    'arrives_time'=> $todayDateTime,
                    'datetime'=>$todayDateTime,
                    'start_acronym'=> $this->acronym,
                    'locations_id'=> $this->location,
                ];

                $queueCreated = QueueDB::storeQueue( $storeData );
                QueueCreated::dispatch( $queueCreated );
                $this->counterID =  QueueDB::assignCounterToQueue( $queueCreated->id ,$this->location);

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

                $pendingCount = QueueDB::countPending( $this->teamId, $queueCreated->id, $this->countCatID, $this->fieldCatName, $this->counterID,$this->location ) ;

                $data = [
                    'name' => $queueCreated->name,
                    'phone' => $queueCreated->phone,
                    'queue_no' => $queueCreated->id,
                    'arrives_time' => Carbon::parse( $queueCreated->created_at )->format( 'd-m-Y h:i A' ),
                    'category_name' => $this->categoryName,
                    'thirdC_name' => $this->thirdCategoryName,
                    'secondC_name' => $this->secondCategoryName,
                    'pending_count' => $pendingCount,
                    'token' =>$queueCreated->token,
                    'token_with_acronym'=>$queueCreated->start_acronym,
                    'to_mail'=>$this->email,
                    'locations_id'=>$this->location,
                    'location_name' =>$this->locationName,

                ];

                $logo =  SiteDetail::viewImage( SiteDetail::FIELD_LOGO_PRINT_TICKET, $this->teamId );
                $waitingTime = 0;
                if ( !empty( $this->siteDetails ) ) {
                    if ( $this->siteDetails->ticket_text_enable == SiteDetail::STATUS_YES ) {
                        $estimate_time = $this->siteDetails->estimate_time ?? 0 ;

                        $waitingTime =  $estimate_time * $data[ 'pending_count' ];

                        if ( !empty( $this->siteDetails->ticket_text_2 ) )
                        $this->showTicketText_2 = str_replace( '{{Waiting Time}}', $waitingTime, $this->siteDetails->ticket_text_2 );

                        if ( !empty( $this->siteDetails->ticket_text ) ) {
                            $text = str_replace( '{{QUEUE COUNT}}', $data[ 'pending_count' ], $this->siteDetails->ticket_text );
                            $this->showTicketText = str_replace( '{{Waiting Time}}', $waitingTime, $text );
                        }

                    }
                }

                $queueCreated->waiting_time = $waitingTime;
                // $queueCreated->queue_count = $pendingCount;
                $queueCreated->save();

                $this->sendNotification( $data );
                $this->resetForm();

                // DB::commit();
                // $lock->release();
                // Release the lock after the transaction is committed
                $this->dispatch( 'swal:saved-queue', [
                    'timer'=>4000,
                    'html'=>'<div style="padding-top:20px;text-align:center" class="flex content-center gap-4"> <img src="'.asset( $logo ).'" class="w-100 h-100" style="margin:auto;max-width:160px"/></div><div class="flex flex-col gap-2 text-black-400 pt-5" style="line-height:1.24;text-align:center;border:1px solid #ddd;padding:12px;border-radius:14px;margin-top:15px;font-family: Simplified Arabic Fixed;"><h3 style="font-size:16px;margin:0">Name: '.$data[ 'name' ].'</h3><div ><h3 style="font-size:16px;margin:0">Queue No. '.$this->acronym.$data[ 'token' ].'</h3></div><div><h5 style="font-size:16px;margin:0">Arrived:'. $data[ 'arrives_time' ].'</h5></div><div><h3 style="font-size:16px;margin:0">Branch Name: '.$data[ 'location_name' ].'</h3></div>  <div><h3 style="font-size:16px;margin:0">'.$data[ 'category_name' ].'</h3><h3 style="font-size:16px;margin:0">'.$data[ 'secondC_name' ].'</h3><h3 style="font-size:16px;">'.$data[ 'thirdC_name' ].'</h3></div> <div><h4 style="font-size:16px;margin:0">'.$this->showTicketText .'</h4><h4 style="font-size:16px;margin:0">'.$this->showTicketText_2 .'</h4></div></div>',
                    'confirmButtonText'=>'Thank You'
                ] );

            } catch ( \Throwable $ex ) {
                // DB::rollBack();
                // $lock->release();
                // Release the lock in case of an exception

                Log::emergency( 'Queue generate issue on desktop' );
                Log::emergency( $ex );

                $this->dispatch( 'swal:ticket-generate', [
                    'title'=>'Oops...',
                    'text'=>'Unable to generate ticket. Please contact to the admin',
                    'icon'=>'error'
                ] );
            }

        }

        public function resetDynamic() {
            $this->dynamicForm = FormField::getFields( $this->teamId );
            $this->allCategories = [
                'thirdChildId'=>$this->thirdChildId,
                'secondChildId'=>$this->secondChildId,
                'selectedCategoryId'=>$this->selectedCategoryId,
            ];
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
            $data[ 'location' ] = Location::find( $this->location )->value( 'location_name' );
            if ( !empty( $this->phone ) ) {
                // SmsAPI::sendSms( $this->teamId, $data );

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
            if ( Auth::check() ) {
                $this->locationStep = false;
                $this->firstStep = true;
            } else {

                if ( !empty( $this->allLocations ) ) {
                    $this->location = '';
                    $this->locationStep = true;
                    $this->firstStep = false;
                } else {
                    $this->locationStep = false;
                    $this->firstStep = true;
                }
            }
            $this->secondStep = $this->thirdStep = $this->fourthStep = false;
            $this->currentPage = $this->totalLevelCount = Category::STEP_1;
            $this->selectedCategoryId = $this->secondChildId = $this->thirdChildId = null ;
            $this->thirdCategoryName =  $this->secondCategoryName = $this->categoryName = null;
        }

        public function updateCurrentPage( $page ) {
            if ( $page == Category::STEP_4 ) {
                         
                if ( $this->siteDetails?->queue_form_display == SiteDetail::STATUS_NO ) {
                    $this->saveQueueForm();
                    return;
                }
            }
            $this->currentPage = $page;
            if ( $this->currentPage == Category::STEP_4 )
            $this->resetDynamic();

        }

        public function currentPageFn( $page ) {

            switch( $page ) {
                case Category::STEP_2:
                $this->secondStep  = true;
                $this->firstStep = $this->thirdStep = $this->fourthStep = false;
                break;
                case Category::STEP_3:
                $this->thirdStep = true;
                $this->firstStep = $this->secondStep = $this->fourthStep = false;
                break;
                case Category::STEP_4:
                $this->fourthStep = true;
                $this->firstStep = $this->secondStep = $this->thirdStep = false;
                break;
                default:
                $this->firstStep = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;
            }

        }

        public function goBackFn( $page ) {

            $this->totalLevelDecFn();

            switch( $page ) {
                case Category::STEP_1:
                $this->secondChildId = $this->selectedCategoryId =     $this->thirdChildId  = null;
                $this->locationStep  = true;
                $this->firstStep = $this->secondStep = $this->thirdStep = $this->fourthStep = false;
                break;
                case Category::STEP_2:
                 $this->secondChildId =  $this->thirdChildId  = null;
                    $this->firstStep  = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;
                break;
                case Category::STEP_3:
                $this->secondStep = true;
                $this->thirdChildId = null;
                $this->firstStep = $this->thirdStep = $this->fourthStep = false;
                break;
                case Category::STEP_4:
                $this->thirdStep = true;
                $this->firstStep = $this->secondStep = $this->fourthStep = false;
                break;
                default:
                $this->secondChildId = $this->selectedCategoryId =    $this->thirdChildId  = null;
                $this->firstStep = true;
                $this->secondStep = $this->thirdStep = $this->fourthStep = false;

            }
            $this->resetDynamic();
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
    }