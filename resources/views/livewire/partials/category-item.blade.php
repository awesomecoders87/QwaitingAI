<li>
    <input type="checkbox" wire:model.live="selectedCategories" value="{{ $category['id'] }}">
    {{ $category['name'] }}

    @if (!empty($category['children']))
        <ul class="ml-4" style="margin-left: 25px;">
            @foreach ($category['children'] as $subCategory)
                @include('livewire.partials.category-item', ['category' => $subCategory])
            @endforeach
        </ul>
    @endif
</li>
