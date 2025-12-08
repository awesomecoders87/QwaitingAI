<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Jobs\ImportQueuesFromCsv;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use App\Services\QueueCsvImporter;

class ImportQueueController extends Controller
{
    public function showForm()
    {
        $locationId = Session::get('selectedLocation');
        return view('import.queues',compact('locationId'));
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'csv_file' => ['required','file','mimes:csv,txt'],
            // 'locations_id' => ['nullable','integer'],
        ]);

        $validated['locations_id'] = Session::get('selectedLocation');
        
        if (!$request->file('csv_file')->isValid()) {
            return back()->withErrors('Invalid file upload.');
        }

        // Stream and import synchronously without saving or queuing
        /** @var QueueCsvImporter $importer */
        $importer = app(QueueCsvImporter::class);
        $teamId = auth()->user()->team_id ?? null;
        $chunkSize = (int)(500);
        $result = $importer->importFromUploadedFile(
            $request->file('csv_file'),
            (int)($validated['locations_id'] ?? 0),
            $teamId,
            $chunkSize,
            'psb'
        );

        if (!$result['ok']) {
            return back()->withErrors($result['message'] ?? 'Import failed');
        }

        return back()->with('status', 'Imported '.$result['created'].' records successfully.');
    }
}
