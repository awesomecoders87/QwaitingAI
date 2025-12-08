@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-xl">
  <h1 class="text-xl font-semibold mb-4">Upload Old Records CSV</h1>

  @if(session('status'))
    <div class="p-3 bg-green-100 text-green-700 rounded mb-3">{{ session('status') }}</div>
  @endif

  @if ($errors->any())
    <div class="p-3 bg-red-100 text-red-700 rounded mb-3">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
  @endif

  <form action="{{ route('tenant.import-queues.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4 border p-4 rounded">
    @csrf

    <div>
      <label class="block mb-1 font-medium">CSV file</label>
      <input type="file" name="csv_file" required class="border p-2 w-full">
      <p class="text-sm text-gray-500 mt-1">Columns expected: Token, Destination1, Airlines, Flight Number, Counter, Name</p>
    </div>

    <button class="bg-indigo-600 text-white px-4 py-2 rounded">Upload & Queue Import</button>
  </form>
</div>
@endsection
