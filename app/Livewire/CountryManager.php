<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Country;
use App\Models\AllowedCountry;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

class CountryManager extends Component
{
    use WithPagination;

    public $teamId, $location;
    public $allcountries;
    public $select_countryId;
    public $countryId;
    public $showcountryModel = false;
    public $perPage = 10; // number of records per page

    protected $paginationTheme = 'tailwind'; // works well with Tailwind

    public function mount()
    {

        $this->teamId = tenant('id');
        $this->location = Session::get('selectedLocation');
        $this->allcountries = Country::orderBy('name')->get();

    }


    private function countriesQuery()
    {
        return AllowedCountry::where('team_id', $this->teamId)
            ->where('location_id', $this->location)
            ->latest();
    }

    public function openAddModal(): void
    {
        $this->reset(['countryId', 'select_countryId']);
        $this->showcountryModel = true;
    }

    public function openEditModal(int $id): void
    {
        $country = AllowedCountry::findOrFail($id);
        $this->countryId = $country->id;
        $this->select_countryId = $country->country_id;
        $this->showcountryModel = true;
    }

    public function save(): void
    {
        if (!$this->select_countryId) {
            $this->dispatch('alert', type: 'error', message: 'Please select a country.');
            return;
        }

        $exists = $this->countriesQuery()
            ->where('country_id', $this->select_countryId)
            ->when($this->countryId, fn($q) => $q->where('id', '!=', $this->countryId))
            ->exists();

        if ($exists) {
            $this->dispatch('alert', type: 'error', message: 'This country is already added!');
            return;
        }

        $data = Country::findOrFail($this->select_countryId);

        AllowedCountry::updateOrCreate(
            ['id' => $this->countryId,'team_id'=> $this->teamId,'location_id'=> $this->location],
            [
                'country_id' => $data->id,
                'name'       => $data->name,
                'iso_code'   => strtoupper($data->code),
                'phone_code' => $data->phonecode,
            ]
        );

        $this->reset(['showcountryModel', 'select_countryId', 'countryId']);
        $this->dispatch('alert', type: 'success', message: $this->countryId ? 'Country Updated!' : 'Country Added!');
        $this->resetPage(); // reset pagination after save
    }

    public function confirmDelete(int $id): void
    {
        $this->countryId = $id;
        $this->dispatch('confirmDelete');
    }

    #[On('delete')]
    public function delete(): void
    {
        AllowedCountry::find($this->countryId)?->delete();
        $this->reset(['countryId', 'select_countryId']);
        $this->dispatch('alert', type: 'success', message: 'Country Deleted!');
        $this->resetPage(); // reset pagination after delete
    }

    public function render()
    {
        $countries = $this->countriesQuery()->paginate($this->perPage);

        return view('livewire.country-manager', [
            'countries' => $countries,
        ]);
    }
}
