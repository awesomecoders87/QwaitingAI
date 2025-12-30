<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Queue;
use App\Models\QueueStorage;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
 {
    use HasFactory, SoftDeletes;

    protected $fillable = [ 'priority', 'img', 'name', 'team_id', 'level_id', 'parent_id', 'other_name', 'acronym', 'display_on', 'for_screen', 'booking_category_show_for', 'category_locations','sort', 'visitor_in_queue','is_paid','redirect_url','description','is_service_template','service_time','note','description','amount','ticket_note','service_color','label_image','label_background_color','label_font_color','label_text','bg_color','created_at', 'updated_at', 'deleted_at','type' ];
    
    protected $dates = ['deleted_at'];

    const STEP_1 = 1;
    const STEP_2 = 2;
    const STEP_3 = 3;
    const STEP_4 = 4;
    const STEP_5 = 5;
    const STEP_6 = 6;
    public $location;
    public $teamId;
    // FORM

    public static function firstLevel() {
        return self::first()?->id;
    }

    public function setCategoryLocationsAttribute( $value )
 {
        $this->attributes[ 'category_locations' ] = json_encode( $value );
    }

    // Define an accessor to decode the JSON string when retrieving from the database

    public function getCategoryLocationsAttribute( $value )
 {
        return json_decode( $value, true );
    }

    public function team(): BelongsTo
 {
        return $this->belongsTo( Team::class );
    }

    public function level(): BelongsTo
 {
        return $this->belongsTo( Level::class );
    }

    public function getparent(): BelongsTo
 {
        return $this->belongsTo( self::class, 'parent_id', 'id' );
    }

    public function events():HasMany {
        return $this->hasMany( Event::class, 'category_id', 'id' );

    }

    public function serviceSetting():HasMany {
        return $this->hasMany( ServiceSetting::class, 'category_id', 'id' );

    }

    public function screenTemplate(): BelongsToMany
    {
        return $this->belongsToMany( ScreenTemplate::class );
    }

//     public function queues()
//  {
//         return $this->hasMany( Queue::class, 'category_id' );
//     }
    public function queues()
 {
        return $this->hasMany( QueueStorage::class, 'category_id' );
    }
    public function queuesSubCategoryId()
 {
        return $this->hasMany( QueueStorage::class, 'sub_category_id','id' );
    }

    public function users(): BelongsToMany
 {
       return $this->belongsToMany(User::class, 'category_user');
    }

    public function children()
 {
        return $this->hasMany( Category::class, 'parent_id', 'id' );
    }

    public function form_fields()
 {
        return $this->belongsToMany( FormField::class, 'category_form_field', 'category_id', 'form_field_id' );
    }
    public static function getFirstCategory( $teamId, $location = null )
 {
        $firstLevel = Level::getFirstRecord();

        if ( !empty( $firstLevel ) ) {
            $query = self::where( [
                'level_id' => $firstLevel->id,
                'team_id' => $teamId,
            ] );

            if ( $location !== null ) {
                $query->whereJsonContains( 'category_locations', "$location" );
            }

            return $query->pluck( 'name', 'id' );
        }

        return null;
        // Return null or handle the case when $firstLevel is empty
    }

    // public static function getFirstCategoryN($teamId, $location = null)
    // {
    //     $query = self::where([
    //         'level_id' => self::STEP_1,
    //         'team_id' => $teamId,
    //     ])
    //     ->whereNotNull('display_on')
    //     ->where('display_on', '!=', '')
    //     ->whereIn('display_on', ['Display on Transfer & Ticket Screen', 'Ticket Screen']);

    //     // Check that booking_category_show_for is not null or empty

    //     if ($location !== null) {
    //         $query->whereJsonContains('category_locations', "$location");
    //     }

    //     return $query->get(['id', 'name', 'img', 'other_name','redirect_url','service_time','is_service_template','note','description','bg_color']);
    // }

    public static function getFirstCategoryN($teamId, $location = null)
    {
        $query = self::where([
            'level_id' => self::STEP_1,
            'team_id' => $teamId,
        ])
        ->whereNotNull('display_on')
        ->where('display_on', '!=', '')
        ->whereNotNull('booking_category_show_for')
        ->where('booking_category_show_for', '!=', '')
        ->whereIn('display_on', ['Display on Transfer & Ticket Screen', 'Ticket Screen']);

        // Check that booking_category_show_for is not null or empty

         $query->whereIn('booking_category_show_for', [
            'Backend & Online Appointment Screen',
            'Backend',
        ]);
        if ($location !== null) {
            $query->whereJsonContains('category_locations', "$location");
        }

        return $query->get(['id', 'name', 'img', 'other_name','redirect_url','service_time','is_service_template','note','description','bg_color']);
    }
    public static function getFirstCategorybooking($teamId, $location = null)
{
    $query = self::where([
        'level_id' => self::STEP_1,
        'team_id' => $teamId,
    ]);
    // ->whereIn('display_on', ['Display on Transfer & Ticket Screen', 'Ticket Screen']);

    // Check that booking_category_show_for is not null or empty
    $query->whereNotNull('booking_category_show_for')
          ->where('booking_category_show_for', '!=', '');

    // Conditionally apply booking_category_show_for based on user login
    // if (auth()->check()) {
    //     $query->whereIn('booking_category_show_for', [
    //         'Backend & Online Appointment Screen',
    //         'Backend',
    //     ]);
    // } else {
        $query->whereIn('booking_category_show_for', [
            'Backend & Online Appointment Screen',
            'Online',
        ]);
    // }

    if ($location !== null) {
        $query->whereJsonContains('category_locations', "$location");
    }

    return $query->get(['id', 'name', 'img', 'other_name','redirect_url','service_time','is_service_template','note','description','bg_color','type']);
}

    public static function viewCategoryName( $categoryId ) {
        return self::withTrashed()->find( $categoryId )?->name;
    }

    public static function getPluckNames( $categoryId, $location = null )
 {
        if ( is_null( $categoryId ) )
        return collect();

        $query = self::where( 'parent_id', $categoryId );

        if ( !empty( $location ) ) {
            $query->whereJsonContains( 'category_locations', "$location" );
        }

        return $query->pluck( 'name', 'id','img' ,'other_name' );
        // return $query->get( [ 'id', 'name', 'img' ] );
    }
    // public static function getCategories( $categoryId, $location = null){
    //     if ( is_null( $categoryId ) )
    //     return collect();

    //     $query = self::where( 'parent_id', $categoryId )->whereIn('display_on', ['Display on Transfer & Ticket Screen', 'Ticket Screen']);

    //     if ( !empty( $location ) ) {
    //         $query->whereJsonContains( 'category_locations', "$location" );
    //     }


    //     return $query->get( [ 'id', 'name', 'img','other_name','redirect_url','service_time','is_service_template','note','description' ] );
    // }

     public static function getCategories( $categoryId, $location = null){
        if ( is_null( $categoryId ) )
        return collect();

        $query = self::where( 'parent_id', $categoryId )
        ->whereNotNull('display_on')
        ->where('display_on', '!=', '')
        ->whereNotNull('booking_category_show_for')
        ->where('booking_category_show_for', '!=', '')
        ->whereIn('display_on', ['Display on Transfer & Ticket Screen', 'Ticket Screen']);

        // Check that booking_category_show_for is not null or empty

         $query->whereIn('booking_category_show_for', [
            'Backend & Online Appointment Screen',
            'Backend',
        ]);
        if ( !empty( $location ) ) {
            $query->whereJsonContains( 'category_locations', "$location" );
        }


        return $query->get( [ 'id', 'name', 'img','other_name','redirect_url','service_time','is_service_template','note' ,'description'] );
    }


    public static function getchildDetail( $categoryId, $location = null){

        if ( is_null( $categoryId ) )
        return collect();

       $query = self::where( 'parent_id', $categoryId )
        ->whereNotNull('display_on')
        ->where('display_on', '!=', '')
        ->whereNotNull('booking_category_show_for')
        ->where('booking_category_show_for', '!=', '')
        ->whereIn('display_on', ['Display on Transfer & Ticket Screen', 'Ticket Screen']);

         $query->whereIn('booking_category_show_for', [
            'Backend & Online Appointment Screen',
            'Backend',
        ]);
        if ( !empty( $location ) ) {
            $query->whereJsonContains( 'category_locations', "$location" );
        }


        return $query->get( [ 'id', 'name', 'img' ] );
    }
    public static function getchildDetailBooking( $categoryId, $location = null){

        if ( is_null( $categoryId ) )
        return collect();

        $query = self::where( 'parent_id', $categoryId );
        // $query->whereIn('display_on', ['Display on Transfer & Ticket Screen', 'Ticket Screen']);

        // Check that booking_category_show_for is not null or empty
            $query->whereNotNull('booking_category_show_for')
            ->where('booking_category_show_for', '!=', '');

        // Conditionally apply booking_category_show_for based on user login
        // if (auth()->check()) {
        // $query->whereIn('booking_category_show_for', [
        //     'Backend & Online Appointment Screen',
        //     'Backend',
        // ]);
        // } else {
        $query->whereIn('booking_category_show_for', [
            'Backend & Online Appointment Screen',
            'Online',
        ]);
        // }
            if ( !empty( $location ) ) {
            $query->whereJsonContains( 'category_locations', "$location" );
        }


        return $query->get( [ 'id', 'name', 'img','other_name','redirect_url','service_time','is_service_template','note','description'] );
    }

    public static function getPluckSubcategoriesNames($categoryIds, $location = null)
    {
        if (empty($categoryIds)) {
            return collect();
        }

        $query = self::whereIn('parent_id', $categoryIds);

        if (!empty($location)) {
            $query->whereJsonContains('category_locations', "$location");
        }
        return $query->pluck( 'name', 'id' );

        // return $query->get( [ 'id', 'name', 'img' ] );
    }
    public static function getSubCategories($categoryIds, $location = null)
    {
        if (!is_array($categoryIds)) {
                    $categoryIds = [$categoryIds];
                }

                if (empty($categoryIds)) {
                    return collect();
                }

                $query = self::whereIn('parent_id', $categoryIds);

                if (!empty($location)) {
                    $query->whereJsonContains('category_locations', "$location");
                }

                return $query->get(['id', 'name', 'other_name','img','redirect_url','service_time','is_service_template','note','description']);
    }


    public static function viewAcronym( $categoryId ) {
        return self::find($categoryId)?->acronym;
    }

    public static function viewTicketNote( $categoryId ) {
        return self::find( $categoryId )?->ticket_note;
    }

    public static function viewCategory( $cateID ) {
      return self::find( $cateID );


    }
    public static function viewGetCategory( $cateID ) {
        $data = self::find( $cateID );
        $response =[];
        if(is_null($data['parent_id'])){
            $response['parent_catergory_id'] = $cateID;
            $response['child_catergory_id'] = null;
        }else{
            $response['parent_catergory_id'] = $data['parent_id'];
            $response['child_catergory_id'] = $cateID;
        }
        return $response;

    }
    public static function sortCategory( $order ) {
        foreach($order as $key=>$value){
            self::where('id',$value)->update(['sort'=>$key +1]);
        }
        return true;
    }

    public static function getStaffReportHeader( $teamId) {
        $location = Session::get('selectedLocation') ?? null;
        return  self::with('queues')->where(function ($query) {
            $query->whereNull('parent_id')
                  ->orWhere('parent_id', '');
        })
        ->select( 'id', 'team_id', 'name' )
        ->where( 'team_id', $teamId )
        ->whereJsonContains('category_locations', "$location")
        ->get();
    }

    public static function getUpdateNextPrioritySort($categoryid)
    {
        $location = Session::get('selectedLocation') ?? null;
        $domainSlug = Team::getSlug();
        $team_id =  Team::getTeamId( $domainSlug );
        $category =self::find($categoryid);

        $nextserial = 1;

      // Generate the sequence pattern dynamically
       $sequencePattern = self::where('team_id', $team_id)
        ->whereNull('parent_id')
        ->whereJsonContains('category_locations', "$location")
        ->orderBy('sort')
        ->pluck('visitor_in_queue', 'id');

        $filteredCategories = $sequencePattern->except($category->id);

        // Sum the 'visitor_in_queue' values of the remaining categories
        $sumVisitorInQueue = $filteredCategories->sum() + $sequencePattern[$category->id];

        // Fetch existing queues for the current team and location
        $queues = QueueStorage::where('team_id', $team_id)
            ->where('locations_id', $location)
            ->where('category_id', $category->id)
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
            // dd($maxValue);

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
            $checkSort = QueueStorage::where('team_id', $team_id)
            ->where('locations_id', $location)
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

    public static function changeSort(){
        $location = Session::get('selectedLocation') ?? null;
        $domainSlug = Team::getSlug();
        $team_id =  Team::getTeamId( $domainSlug );
        $queues = QueueStorage::where('team_id', $team_id)
        ->where('locations_id', $location)
        // ->whereNotNull('priority_sort')
        ->whereDate('created_at', Carbon::today())
        ->select('id','category_id')
        ->get();


        QueueStorage::where('team_id', $team_id)
        ->where('locations_id', $location)
        ->whereDate('created_at', Carbon::today())
        ->update(['priority_sort' => 0]);

        foreach($queues as $key => $Value){
        $Value['category_id'];
        $nextseries = self::getUpdateNextPrioritySort($Value['category_id']);

        QueueStorage::where('id', $Value->id)
          ->update(['priority_sort' => $nextseries]);
        }
}

public static function getAllCategories($teamId, $location = null)
{
    $categories = self::query()
        ->whereIn('display_on', ['Display on Transfer & Ticket Screen', 'Ticket Screen'])
        ->when($location, fn($q) => $q->whereJsonContains('category_locations', "$location"))
        ->get(['id', 'name', 'img', 'other_name', 'parent_id']);

    // Group by parent_id
    $grouped = $categories->groupBy('parent_id');

    // Build hierarchy recursively
    $buildTree = function ($parentId) use (&$buildTree, $grouped) {
        return $grouped[$parentId] ?? collect();
    };

    $firstCategories = $grouped[null] ?? collect();

    foreach ($firstCategories as $firstCategory) {
        $firstCategory->subcategories = $grouped[$firstCategory->id] ?? collect();

        foreach ($firstCategory->subcategories as $subcategory) {
            $subcategory->childSubcategories = $grouped[$subcategory->id] ?? collect();
        }
    }

    return $firstCategories;
}

/**
 * Get subcategories with their child subcategories recursively.
 */
public static function getCategoriesWithChildren($parentId, $location = null)
{
    // Fetch direct subcategories
    $subcategories = self::getCategories($parentId, $location);

    foreach ($subcategories as $subcategory) {
        // Fetch child subcategories for each subcategory
        $subcategory->childSubcategories = self::getCategories($subcategory->id, $location);
    }

    return $subcategories;
}

public function bookings()
{
    return $this->hasMany(Booking::class, 'category_id', 'id');
}

}
