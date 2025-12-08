 <div class="max-w-7xl mx-auto bg-white p-6 rounded-xl shadow">
    <h1 class="text-3xl font-bold text-red-600 mb-6">System Error Code Table</h1>

    <div class="overflow-x-auto">
      <table class="min-w-full border border-gray-300 text-sm text-left">
        <thead class="bg-gray-200 text-gray-700">
          <tr>
            <th class="px-4 py-3 border">Error Code</th>
            <th class="px-4 py-3 border">Message</th>
            <th class="px-4 py-3 border">Description</th>
            <th class="px-4 py-3 border">Resolution</th>
          </tr>
        </thead>
        <tbody class="text-gray-700">
            @if(count($items) >0)
@foreach($items as $item)
       @if($item->type == 'system')
          <tr class="border-b">
            <td class="px-4 py-2 border">{{ $item->code }}</td>
            <td class="px-4 py-2 border">{{ $item->message }}</td>
            <td class="px-4 py-2 border">{{ $item->description }}</td>
            <td class="px-4 py-2 border">{{ $item->resolution }}</td>
          </tr>
          @endif
@endforeach
  <!-- Booking Errors -->
          <tr class="border-b bg-gray-100">
            <td class="px-4 py-2 border font-semibold" colspan="4">Booking Error Messages</td>
          </tr>

@foreach($items as $item)
       @if($item->type == 'booking')
          <tr class="border-b">
            <td class="px-4 py-2 border">{{ $item->code }}</td>
            <td class="px-4 py-2 border">{{ $item->message }}</td>
            <td class="px-4 py-2 border">{{ $item->description }}</td>
            <td class="px-4 py-2 border">{{ $item->resolution }}</td>
          </tr>
          @endif
@endforeach

@else

 <tr>
                        <td colspan="7" class="text-center py-6">
                            <img src="{{ url('images/no-record.jpg') }}" alt="No Records Found"
                                class="mx-auto" style="max-width:300px">
                            <p class="text-gray-500 dark:text-gray-400 mt-2">No records found.</p>
                        </td>
                    </tr>
@endif

        </tbody>
      </table>
    </div>
  </div>
