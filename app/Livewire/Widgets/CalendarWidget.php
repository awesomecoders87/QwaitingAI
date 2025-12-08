<?php
namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Location;
use App\Models\CustomSlot;
use App\Models\ServiceSetting;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions;
use Filament\Forms;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Saade\FilamentFullCalendar\Data\EventData;
use App\Filament\Resources\EventResource;
use DateTime;
use DateInterval;
use DatePeriod;
use DateTimeZone;
use Filament\Forms\Components\TimePicker;
use Illuminate\Support\Facades\Session;

class CalendarWidget extends FullCalendarWidget
 {

    public Model | string | null $model = ServiceSetting::class;
    public $levelRecord;
    public $calendarStartDate;
    public $businessHours;
    public $selectedLocation;

    public function config(): array
 {


        return [
            'firstDay' => 1,
            'initialView'=> 'dayGridMonth',
            'editable'=>
            
            'false', 

            'headerToolbar' => [

                'left' => 'dayGridWeek,dayGridDay',
                'center' => 'title',
                'right' =>  'prev,next today',
            ],
           
        ];
    }

    public static function canView(): bool
     {
        return false;
    }
    public function mount()
     {
        $this->selectedLocation = Session::get('selectedLocation');
        $businessHours = ServiceSetting::where('team_id', Filament::getTenant()->id)
        ->where('location_id', $this->selectedLocation)
        ->where('category_id', $this->levelRecord->id)
        ->latest()->get()->toArray();
        $this->businessHours=$businessHours;
       
    }

    public function rules(): array
    {
        return [
            'start_at' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) {
                // Custom validation rule: start_at must be after 6 PM
                $selectedTime = DateTime::createFromFormat('H:i', $value);
                $sixPM = DateTime::createFromFormat('H:i', '18:00');

                if ($selectedTime <= $sixPM) {
                    $fail('The ' . Str::ucfirst($attribute) . ' must be after 6 PM.');
                }
            }],
        ];
    }
    public function getFormSchema(): array
 {
        return [
            Forms\Components\Grid::make()
            ->schema( [
                    Forms\Components\TextInput::make( 'title' )->label( 'Title' ),
                    Forms\Components\DatePicker::make( 'date' )->label( 'Select Date' )->native( false )->minDate(now()),
                    TimePicker::make('start')->native(false)->seconds(false),
                    TimePicker::make('end')->native(false)->after('start_at')->seconds(false),
                    Forms\Components\TextInput::make( 'team_id' )->default( $this->levelRecord?->team_id ),
                    Forms\Components\TextInput::make( 'id' ),
                    Hidden::make( 'location_id' )->default( $this->selectedLocation),
                    Hidden::make( 'category_id' )->default( $this->levelRecord?->id ),
                ] ),
            ];
        }

      
        public function fetchEvents( array $fetchInfo ): array {
   
            $businessHoursArray = $this->businessHours;
            $selecteddateArray = '';
           
            $openDays = [];
            $this->calendarStartDate =   $startDate = new DateTime($fetchInfo[ 'start' ], new DateTimeZone('Asia/Kolkata'));
            $endDate = new DateTime($fetchInfo[ 'end' ], new DateTimeZone('Asia/Kolkata'));

            $interval = new DateInterval( 'P1D' );
            $period = new DatePeriod( $startDate, $interval, $endDate );

            foreach ( $period as $date ) {
                $dayOfWeek = $date->format( 'l' );
                $selectedDate = new DateTime($date->format('Y-m-d'));
              
                $customSlots = CustomSlot::query()
                ->where('selected_date',$selectedDate)
                ->where('team_id',Filament::getTenant()->id)
                ->where('location_id', $this->selectedLocation)
                ->where('category_id', $this->levelRecord->id)
                ->first();

                if(!empty($customSlots)){
                  
                    $selecteddateArray = json_decode($customSlots['business_hours'], true );
                        $startTime = [];
                        $endTime = [];
                        if ($selecteddateArray['0'][ 'is_closed' ] == 'open' && strtolower( $selecteddateArray['0'][ 'day' ] ) == strtolower( $dayOfWeek ) ) {
                            $startTime = ( new DateTime( $date->format( 'Y-m-d' ) . ' ' . $selecteddateArray['0'][ 'start_time' ] ) )->format( 'Y-m-d H:i' );
                            $endTime = ( new DateTime( $date->format( 'Y-m-d' ) . ' ' . $selecteddateArray['0'][ 'end_time' ] ) )->format( 'Y-m-d H:i' );
                            $openDays[] = [
                                'id'=>$customSlots['id'],
                                'title' => date( 'H:i A' ,strtotime($startTime)).'-'.date( 'H:i A' ,strtotime($endTime)),
                                'date' => $date->format( 'Y-m-d' ),
                                'start' => $startTime,
                                'end' => $endTime,
                                'editable'=> false,
                                'textColor' => '#000000',
                                'backgroundColor' =>'#75f390'
                            ];
                            if(!empty($selecteddateArray['0'][ 'day_interval' ])){
    
                                foreach($selecteddateArray['0'][ 'day_interval'] as $interval){
                                    $startTime = ( new DateTime( $date->format( 'Y-m-d' ) . ' ' . $interval[ 'start_time' ] ) )->format( 'Y-m-d H:i' );
                                    $endTime = ( new DateTime( $date->format( 'Y-m-d' ) . ' ' . $interval[ 'end_time' ] ) )->format( 'Y-m-d H:i' );
                                    $openDays[] = [
                                        'id'=>$customSlots['id'],
                                        'title' =>  date( 'H:i A' ,strtotime($startTime)).'-'.date( 'H:i A' ,strtotime($endTime)),
                                        'date' => $date->format( 'Y-m-d' ),
                                        'start' => $startTime,
                                        'end' => $endTime,
                                        'editable'=> true,
                                        'type'=>'custom',
                                        'textColor' => '#000000',
                                        'backgroundColor' =>'#75f390'
                                    ];
                                }
                            }
                            
                        
                    }

                }else{
                   
                    foreach ( $businessHoursArray as $dayselect ) {
                        $dayarray = json_decode($dayselect['business_hours'], true );
                        $customdates = json_decode($dayselect['custom_business_hours'], true );
                        $startTime = [];
                        $endTime = [];
                        foreach($dayarray as $day){
                        if ( $day[ 'is_closed' ] == 'open' && strtolower( $day[ 'day' ] ) == strtolower( $dayOfWeek ) ) {
                            $startTime = ( new DateTime( $date->format( 'Y-m-d' ) . ' ' . $day[ 'start_time' ] ) )->format( 'Y-m-d H:i' );
                            $endTime = ( new DateTime( $date->format( 'Y-m-d' ) . ' ' . $day[ 'end_time' ] ) )->format( 'Y-m-d H:i' );
                            $openDays[] = [
                                'id'=>$dayselect['id'],
                                'title' => date( 'H:i A' ,strtotime($startTime)).'-'.date( 'H:i A' ,strtotime($endTime)),
                                'date' => $date->format( 'Y-m-d' ),
                                'start' => $startTime,
                                'end' => $endTime,
                                'editable'=> true,
                                'type'=>'regular',
                                'textColor' => '#000000',
                                'backgroundColor' =>'#75f390'
                            ];
                            if(!empty($day[ 'day_interval' ])){
    
                                foreach($day[ 'day_interval'] as $interval){
                                    $startTime = ( new DateTime( $date->format( 'Y-m-d' ) . ' ' . $interval[ 'start_time' ] ) )->format( 'Y-m-d H:i' );
                                    $endTime = ( new DateTime( $date->format( 'Y-m-d' ) . ' ' . $interval[ 'end_time' ] ) )->format( 'Y-m-d H:i' );
                                    $openDays[] = [
                                        'id'=>$dayselect['id'],
                                        'title' =>  date( 'H:i A' ,strtotime($startTime)).'-'.date( 'H:i A' ,strtotime($endTime)),
                                        'date' => $date->format( 'Y-m-d' ),
                                        'start' => $startTime,
                                        'end' => $endTime,
                                        'editable'=> true,
                                        'type'=>'regular',
                                        'textColor' => '#000000',
                                        'backgroundColor' =>'#75f390'
                                    ];
                                }
                            }
                            
                        }
                    }
                    } 
                }
              
            }

return $openDays;
        }
   

 


    protected function headerActions(): array
 {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function modalActions(): array
 {
        return [
            Actions\EditAction::make()
            ->mountUsing(
                function ( ServiceSetting $record, Forms\Form $form = null, array $arguments ) {
           dd($record,$arguments);
                    if ( $form ) {
                        $form->fill( [
                            'title' => 'tst'.$record->title,
                            'category_id' =>$this->levelRecord?->id,
                            'team_id' => $record->team_id,
                            'start' => $arguments[ 'event' ][ 'start' ] ?? $record->start,
                            'end' => $arguments[ 'event' ][ 'end' ] ?? $record->end,
    
                        ] );
                    }
                }
            ),
            // Actions\DeleteAction::make(),
        ];
    }
//  protected function modalActions(): array
//     {
//         return [
//             Actions\EditAction::make()->authorize(function ($record) {
//                 // Custom authorization logic for Edit action
//                 return auth()->user()->can('update', $record);
//             }),
//             Actions\DeleteAction::make()->authorize(function ($record) {
//                 // Custom authorization logic for Delete action
//                 return auth()->user()->can('delete', $record);
//             }),
//             $this->viewAction(),
//         ];
//     }
    public function eventDidMount(): string
 {
        return <<<JS
        function( {
            event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }
        ) {
            el.setAttribute( 'x-tooltip', 'tooltip' );
            el.setAttribute( 'x-data', "{ tooltip: '"+event.title+"' }" );
        }
        JS;
    }

    

}