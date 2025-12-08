<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FormField;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Auth;

class FormFieldEdit extends Component
{
    #[Title('Edit Form Field')]

    // Form fields
    public $teamId;
    public $locationId;
    public $title;
    public $type = 'Text';
    public $options = [];
    public $label;
    public $placeholder;
    public $default_value;
    public $minimum_number_allowed;
    public $maximum_number_allowed;
    public $policy = 'Text';
    public $policy_content;
    public $policy_url;
    public $mandatory = true;
    public $ticket_screen = true;
    public $before_appointment_form = true;
    public $after_appointment_form = false;
    public $after_scan_screen = false;
    public $advanced_setting = false;
    public $validation;
    public $categories = [];
    public $is_edit_remove = FormField::STATUS_ACTIVE;
    public $isEdit = true;
    public $userAuth;
    public $is_multiple_options = false;

    // For the tree select
    public $categoriesData = [];

    public ?FormField $formField = null;

    public function mount($id)
    {
        $user = Auth::user();
        if (!$user->hasPermissionTo('Form Field Edit')) {
            abort(403);
        }

        $this->teamId = tenant('id'); // Ensure your tenant helper is correctly set up
        $this->locationId = Session::get('selectedLocation');
        $this->userAuth = Auth::user();
        $this->formFieldId = $id;

        // Load the form field by ID
        $this->formField = FormField::findOrFail($id);

        // Only allow editing if the form field belongs to the current tenant
        if ($this->formField->team_id != $this->teamId && $this->formField->location_id != $this->locationId) {
            abort(403, 'Unauthorized action.');
        }

        // Populate the form fields with existing data
        $this->title = trim($this->formField->title);
        $this->type = $this->formField->type;
        $this->options = $this->formField->options ?? [];
        $this->label = trim($this->formField->label);
        $this->placeholder = $this->formField->placeholder;
        $this->default_value = $this->formField->default_value;
        $this->minimum_number_allowed = $this->formField->minimum_number_allowed;
        $this->maximum_number_allowed = $this->formField->maximum_number_allowed;
        $this->policy = $this->formField->policy;
        $this->policy_content = $this->formField->policy_content;
        $this->policy_url = $this->formField->policy_url;
        $this->mandatory = $this->formField->mandatory;
        $this->ticket_screen = $this->formField->ticket_screen;
        $this->before_appointment_form = $this->formField->before_appointment_form;
        $this->after_appointment_form = $this->formField->after_appointment_form;
        $this->after_scan_screen = $this->formField->after_scan_screen;
        $this->advanced_setting = !empty($this->formField->validation);
        $this->validation = $this->formField->validation;
        $this->categories = $this->formField->categories->pluck('id')->toArray();
        $this->is_edit_remove = $this->formField->is_edit_remove;
        $this->is_multiple_options = (bool)$this->formField->is_multiple_options;

        // Load categories for the tree select
        $this->loadCategories();
    }

    protected function loadCategories()
    {
        // This is a placeholder for loading categories
        // In a real app, you'd fetch them from your database
        $this->categoriesData = Category::where('team_id',$this->teamId)->whereJsonContains('category_locations', (string) $this->locationId)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'count' => $category->form_fields_count,
                ];
            });
    }

    public function getFieldTypeOptions()
    {
        return FormField::getFieldType();
    }

    public function addOption()
    {
        $this->options[] = '';
    }

    public function removeOption($index)
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function updated($propertyName)
    {
        // Handle special cases when properties are updated
        if ($propertyName === 'type') {
            // Reset options when type changes from Select
            if ($this->type !== 'Select') {
                $this->options = [];
            } else if (empty($this->options)) {
                $this->options = ['', ''];
            }
        }
    }

    protected function rules()
    {
        $rules = [
            'title' => [
                'required',
                'string',
                // 'regex:/^[a-zA-Z\s]+$/',
                'regex:/^(?!\s)[a-zA-Z\s]+(?<!\s)$/',
                'max:100',
                Rule::unique('form_fields', 'title')
                    ->where('team_id', tenant('id'))
                    ->where('location_id', $this->locationId)
                    ->ignore($this->formField?->id),
            ],
            'type' => ['required', 'string'],
            'label' => ['required', 'string', 'max:100'],
            'placeholder' => ['required', 'string', 'max:100'],
            'default_value' => ['nullable', 'string', 'max:100'],
            'mandatory' => ['required', 'boolean'],
            'ticket_screen' => ['boolean'],
            'before_appointment_form' => ['boolean'],
            'after_appointment_form' => ['boolean'],
            'after_scan_screen' => ['boolean'],
            'is_edit_remove' => ['required'],
        ];

        // Conditional validation based on field type
        if ($this->type === 'Select') {
            $rules['options'] = ['required', 'array', 'min:2', 'max:50'];
            $rules['options.*'] = ['required', 'string', 'distinct'];
        } elseif ($this->type === 'Policy') {
            $rules['policy'] = ['required', 'string', 'in:Text,URL'];

            if ($this->policy === 'Text') {
                $rules['policy_content'] = ['required', 'string', 'max:255'];
            } else {
                $rules['policy_url'] = ['required', 'url'];
            }
        } elseif ($this->type =='Number'){
            $rules['minimum_number_allowed'] = ['required', 'numeric', 'min:1', 'max:255'];
            $rules['maximum_number_allowed'] = ['required', 'numeric', 'min:1', 'max:255', 'gte:minimum_number_allowed'];
        }
        elseif ($this->type =='Phone'){
            $rules['minimum_number_allowed'] = ['required', 'numeric', 'min:1', 'max:255'];
            $rules['maximum_number_allowed'] = ['required', 'numeric', 'min:1', 'max:255', 'gte:minimum_number_allowed'];
        }


        // Advanced settings validation
        if ($this->advanced_setting) {
            // $rules['validation'] = ['nullable', 'string', 'max:100', function ($attribute, $value, $fail) {
            //     // Custom validation to check if the regex is valid
            //     try {
            //         if (!empty($value) && @preg_match($value, '') === false) {
            //             $fail(__('text.The provided regex is invalid.'));
            //         }
            //     } catch (\Exception $e) {
            //         $fail(__('text.The provided regex is invalid.'));
            //     }
            // }];

            $rules['validation'] = ['nullable'];
        }

        return $rules;

    }

    public function submit()
    {

        // Validate the form data
        $validatedData = $this->validate();

        // Handle special cases
        if (!$this->advanced_setting) {
            $validatedData['validation'] = null;
        }


    if ($this->formField) {
        $this->formField->update(array_merge($validatedData, [
            'is_multiple_options' => $this->is_multiple_options == true ? 1 : 0,
        ]));
    } else {
        $formField = new FormField(array_merge($validatedData, [
            'is_multiple_options' => $this->is_multiple_options == true ? 1 : 0,
        ]));
        $formField->team_id = $this->teamId;
        $formField->location_id = $this->locationId;
        $formField->save();
        $this->formField = $formField;
    }



        // Sync categories
        $this->formField->categories()->sync($this->categories);

        // Show success message
        session()->flash('success', __('text.Form field has been saved successfully.'));

        ActivityLog::storeLog($this->teamId, $this->userAuth->id, null, null, 'Edit Form Field', $this->locationId, ActivityLog::SETTINGS, null, $this->userAuth);

        // Redirect or emit event based on your application needs
        $this->redirect(route('tenant.form-fields'));
    }

    public function render()
    {
        return view('livewire.form-field-edit');
    }
}
