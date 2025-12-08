<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Log;

class FormField extends Model
 {
    use HasFactory;

    protected $fillable = [ 'team_id','location_id', 'type', 'title', 'ticket_screen', 'options', 'after_scan_screen', 'mandatory', 'placeholder', 'custom_class', 'mandatory', 'placeholder', 'custom_class', 'before_appointment_form', 'after_appointment_form', 'minimum_number_allowed', 'maximum_number_allowed', 'policy_content', 'policy_url', 'label', 'validation', 'sort', 'policy', 'is_edit_remove', 'is_multiple_options','created_at', 'updated_at' ];

    const SELECT_FIELD = 'Select';
    const TEXT_FIELD = 'Text';
    const DATE_FIELD = 'Date';
    const NUMBER_FIELD = 'Number';
    const PHONE_FIELD = 'Phone';

    const URL_FIELD = 'URL';
    const POLICY_FIELD = 'Policy';
    const TEXTAREA_FIELD = 'Text Area';
    const CHECKBOX_FIELD = 'Checkbox';
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    protected $casts = [
        'options' => 'array',
    ];

    public function team(): BelongsTo
 {
        return $this->belongsTo( Team::class );
    }

    public function categories(): BelongsToMany
 {
        return $this->belongsToMany( Category::class );
    }

    public function categoriesR()
 {
        return $this->belongsToMany( Category::class, 'category_form_field', 'form_field_id', 'category_id' );
    }

    // public static function getFields( $teamId,  $isAppointment = false,$locationId =null) {
    //     $query = self::where( [ 'team_id'=>$teamId, 'ticket_screen'=>self::STATUS_ACTIVE ,'location_id'=>$locationId] );

    //     if ( $isAppointment == true )
    //     $query->where( [ 'before_appointment_form' =>self::STATUS_ACTIVE ] );

    //     $fields =  $query->orderByRaw( 'ISNULL(sort), sort ASC' )
    //     ->get();
    //     if ( $fields->isNotEmpty() )
    //     return $fields->toArray();
    //     else
    //     return [];
    // }

     public static function getFields( $teamId, $isAppointment = false, $locationId =null, $fieldsId = [],$allCategories = []) {

        $query = self::query()
        ->where('team_id', $teamId)
        ->where('ticket_screen', self::STATUS_ACTIVE);

        if (!is_null($locationId)) {
            $query->where('location_id', $locationId);
        }

        if ($isAppointment) {
            $query->where('before_appointment_form', self::STATUS_ACTIVE);
        }

        // Optional filter by form field IDs if passed
        if (!empty($fieldsId)) {
            $query->whereIn('id', $fieldsId);
        }

         // Filter by category if category IDs are passed
    if (!empty($allCategories)) {
        $query->whereIn('id', function ($subQuery) use ($allCategories) {
            $subQuery->select('form_field_id')
                ->from('category_form_field')
                ->whereIn('category_id', $allCategories);
        });
    }


        // MySQL-compatible sorting (use IF for NULL sorting)
        $fields = $query
            ->orderByRaw('ISNULL(sort), sort ASC')
            ->get();

        return $fields->toArray();

    }

    public static function getFieldsbooking( $teamId, $isAppointment = false,$locationId =null,$allCategories = []) {

        $query = self::where( [ 'team_id'=>$teamId, 'location_id'=>$locationId] );

        if ( $isAppointment == true )
        $query->where( [ 'before_appointment_form' =>self::STATUS_ACTIVE ] );

             // Filter by category if category IDs are passed
    if (!empty($allCategories)) {
        $query->whereIn('id', function ($subQuery) use ($allCategories) {
            $subQuery->select('form_field_id')
                ->from('category_form_field')
                ->whereIn('category_id', $allCategories);
        });
    }

       $fields =  $query->orderByRaw( 'ISNULL(sort), sort ASC' )->get();
        if ( $fields->isNotEmpty() )
        return $fields->toArray();
        else
        return [];
    }


    public static function getFieldsMulti($teamId, $selectedCategories)
    {
        $fields = FormField::where('form_fields.team_id', $teamId)
            ->where('form_fields.ticket_screen', self::STATUS_ACTIVE)
            ->leftJoin('category_form_field', 'form_fields.id', '=', 'category_form_field.form_field_id')
            ->where(function ($query) use ($selectedCategories) {
                $query->whereIn('category_form_field.category_id', $selectedCategories)
                      ->orWhereNull('category_form_field.category_id');
            })
            ->orderByRaw('ISNULL(form_fields.sort), form_fields.sort ASC')
            ->distinct()
            ->select('form_fields.*')
            ->get();

        return $fields->isEmpty() ? [] : $fields->toArray();
    }


    public static function getFieldType() {
        return[
            'Text'=>'Text Field',
            'Text Area'=>'Text Area',
            'Number'=>'Number',
            'Phone'=>'Phone',
            'Select'=>'Select Field',
            'Date'=>'Date Field',
            'Checkbox'=>'Checkbox',
            'URL'=>'URL',
            'Policy'=>'Policy',
        ];
    }

    public static function viewLabel($teamId, $name, $location = null) {
        $query = self::where([
            'team_id' => $teamId,
            'title' => $name
        ]);

        if ($location !== null) {
            $query->where('location_id', $location); // assuming column name is `location_id`
        }

        $fieldDetails = $query->select('title', 'label')->first();

        if (!empty($fieldDetails)) {
            return $fieldDetails->label;
        } else {
            return ucfirst($name);
        }
    }

    public static function storeStaticFields( $teamId ) {
        $fields = [
            [
                'team_id'=>$teamId,
                'type'=>'Text',
                'title'=>'name',
                'placeholder'=>'Name',
                'ticket_screen'=>self::STATUS_ACTIVE,
                'label'=>'Name',
                'validation'=>'/^[a-zA-Z\s]+$/',
                'is_edit_remove'=>self::STATUS_INACTIVE,
                'sort'=>'1',
                'mandatory'=>self::STATUS_ACTIVE,
                'created_at'=>Carbon::now()
            ],
            [
                'team_id'=>$teamId,
                'type'=>'phone',
                'title'=>'phone',
                'placeholder'=>'Phone',
                'ticket_screen'=>self::STATUS_ACTIVE,
                'label'=>'Phone',
                'validation'=>'/^\d+$/',
                'is_edit_remove'=>self::STATUS_INACTIVE,
                'sort'=>'2',
                'mandatory'=>self::STATUS_ACTIVE,
                'created_at'=>Carbon::now()

            ]
        ];
        return  self::insert( $fields );

    }

    public static function addDynamicFieldRules( &$rules, $fieldName, $field, $allCategories )
 {
        $fieldRules = [];
        if ( CategoryFormField::checkFieldCategory( $field[ 'id' ], $allCategories ) ) {

            switch( $field[ 'type' ] ) {
                case FormField::TEXT_FIELD:
                self::addTextFieldRules( $fieldRules, $field );
                break;
                case FormField::SELECT_FIELD:
                self::addSelectFieldRules( $fieldRules, $field );
                break;
                case FormField::NUMBER_FIELD:
                self::addNumberFieldRules( $fieldRules, $field );
                break;
                case FormField::TEXTAREA_FIELD:
                self::addTextAreaFieldRules( $fieldRules, $field );
                break;
                case FormField::CHECKBOX_FIELD:
                self::addTextAreaFieldRules( $fieldRules, $field );
                break;
                case FormField::PHONE_FIELD:
               self::addNumberFieldRules( $fieldRules, $field );
                break;
            }
            $rules[ "dynamicProperties.$fieldName" ] = $fieldRules;

        }
    }
    public static function addDynamicFieldRulesMul( &$rules, $fieldName, $field )
    {
           $fieldRules = [];
               switch( $field[ 'type' ] ) {
                   case FormField::TEXT_FIELD:
                   self::addTextFieldRules( $fieldRules, $field );
                   break;
                   case FormField::SELECT_FIELD:
                   self::addSelectFieldRules( $fieldRules, $field );
                   break;
                   case FormField::NUMBER_FIELD:
                   self::addNumberFieldRules( $fieldRules, $field );
                   break;
                   case FormField::TEXTAREA_FIELD:
                   self::addTextAreaFieldRules( $fieldRules, $field );
                   break;
                   case FormField::CHECKBOX_FIELD:
                   self::addTextAreaFieldRules( $fieldRules, $field );
                   break;
                   case FormField::PHONE_FIELD:
                   self::addNumberFieldRules( $fieldRules, $field );

                   break;
               }
               $rules[ "dynamicProperties.$fieldName" ] = $fieldRules;

       }
    public static function addTextFieldRules( &$fieldRules, $field )
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
        if (!empty($field['minimum_number_allowed'])) {
            $fieldRules[] = 'min:' . (int)$field['minimum_number_allowed'];
        }

        if (!empty($field['maximum_number_allowed'])) {
            $fieldRules[] = 'max:' . (int)$field['maximum_number_allowed'];
        }
        self::validationRule( $fieldRules, $field );

    }

    public static  function validationRule( &$fieldRules, $field ) {
        if ( !empty( $field[ 'validation' ] ) && self::isValidRegex( $field[ 'validation' ] ) ) {
            $delimiter = '/';
            $regexPattern = $field[ 'validation' ];
            if ( $regexPattern[ 0 ] !== $delimiter || $regexPattern[ strlen( $regexPattern ) - 1 ] !== $delimiter ) {
                $regexPattern = $delimiter . $regexPattern . $delimiter;
            }
            Log::debug( 'Regex Pattern:     '.  $field[ 'validation' ]  .' '. $regexPattern );
            $fieldRules[] = 'regex:' . $regexPattern;
        }
    }

    // public static function isValidRegex( $pattern ) {
    //     Log::debug( 'Checking Regex Pattern: ' . $pattern );
    //     return @preg_match( $pattern, '' ) !== false;

    // }

    public static function isValidRegex($pattern) {
    Log::debug('Checking Regex Pattern: ' . $pattern);

    $delimiter = '/';

    // Only add delimiters if not already present
    if ($pattern[0] !== $delimiter || $pattern[strlen($pattern) - 1] !== $delimiter) {
        $pattern = $delimiter . $pattern . $delimiter;
    }

    return @preg_match($pattern, '') !== false;
}

    public static  function addSelectFieldRules( &$fieldRules, $field ) {
        $fieldRules[] = ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE )? 'required' : 'nullable';
        self::validationRule( $fieldRules, $field );
    }

    public static function addNumberFieldRules( &$fieldRules, $field ) {
        $fieldRules[] = ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE )? 'required' : 'nullable';
        if (!empty($field['minimum_number_allowed'])) {
            $fieldRules[] = 'min:' . (int)$field['minimum_number_allowed'];
        }

        if (!empty($field['maximum_number_allowed'])) {
            $fieldRules[] = 'max:' . (int)$field['maximum_number_allowed'];
        }
        self::validationRule( $fieldRules, $field );
    }
    public static  function addTextAreaFieldRules( &$fieldRules, $field ) {
        $fieldRules[] = ( $field[ 'mandatory' ] == FormField::STATUS_ACTIVE )? 'required' : 'nullable';
        if (!empty($field['minimum_number_allowed'])) {
            $fieldRules[] = 'min:' . (int)$field['minimum_number_allowed'];
        }

        if (!empty($field['maximum_number_allowed'])) {
            $fieldRules[] = 'max:' . (int)$field['maximum_number_allowed'];
        }

        self::validationRule( $fieldRules, $field );
    }

    public static function findDynamicFormField( $dynamicForm, $fieldId )
 {
        foreach ( $dynamicForm as $field ) {
            if ( $field[ 'id' ] == $fieldId ) {
                return $field;
            }
        }
        return null;

    }

       public static function possiblePhoneKeys(){
        return  [
                'phone',
                'phone number',
                'phonenumber',
                'phone_no',
                'phoneno',
                'mobile',
                'mobile number',
                'mobileno',
                'cell',
                'cellphone',
                'telephone',
                'tel',
                'contact',
                'contact number',
                'whatsapp',
            ];
    }
}
