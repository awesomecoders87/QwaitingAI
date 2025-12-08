<div class="p-4">
  <form wire:submit.prevent="save" class="space-y-6">
  <h2 class="text-xl font-semibold mb-4">{{ __('sidebar.Feedback Settings') }}</h2>
  <div class="rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6 space-y-6">


      <!-- Feedback System Section -->
      <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Feedback System -->
          <div>
            <label class="block text-gray-700 font-medium mb-1 dark:text-white">Feedback System</label>
            <div class="flex items-center space-x-6">
              <label><input type="radio" wire:model="feedback_system" value="1" class="mr-1"> Yes</label>
              <label><input type="radio" wire:model="feedback_system" value="0" class="mr-1"> No</label>
            </div>
          </div>

          <!-- Form After Closed Call -->
          <div>
            <label class="block text-gray-700 font-medium mb-1 dark:text-white">Open Feedback Form after Call</label>
            <div class="flex items-center space-x-6">
              <label><input type="radio" wire:model="form_after_closedcall" value="1" class="mr-1"> Yes</label>
              <label><input type="radio" wire:model="form_after_closedcall" value="0" class="mr-1"> No</label>
            </div>
          </div>
        </div>
      </div>
      <hr />
      <!-- Comment Box Section -->
      <div>
        <h3 class="text-lg font-semibold mb-2">Rating Comment Box Module</h3>
        <div class="grid grid-col-1 mb-3">
          <p class="text-sm text-gray-600 mb-3  dark:text-gray-400">Allow comments to enhance user feedback and engagement.</p>
          <div class="flex items-center space-x-6">
            <label><input type="radio" wire:model="enable_comment_box" value="1" class="mr-1"> Yes</label>
            <label><input type="radio" wire:model="enable_comment_box" value="0" class="mr-1"> No</label>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Enable Comment Box -->         

          <!-- Threshold to activate comment box -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-white">Threshold value to activate the comment box</label>
            <select wire:model="rating_comment_box_threshold" class="w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 bg-white dark:border-gray-600 dark:bg-gray-800 dark:text-white">
              <option value="0">Select Option</option>
              <option value="1">Poor</option>
              <option value="2">Neutral</option>
              <option value="3">Good</option>
              <option value="4">Excellent</option>
            </select>
          </div>
        </div>
      </div>
      <hr />
      <!-- Feedback Alert Section -->
      <div>
        <h3 class="text-lg font-semibold mb-2 dark:text-white">Feedback Alert Settings</h3>
        <div class="grid grid-col-1 mb-3">
        <p class="text-sm text-gray-600 mb-3 dark:text-white">
          Alerts will be sent to the user who handled the call or provided the service, and to the email specified below.
        </p>
        <div class="flex items-center space-x-6">
              <label><input type="radio" wire:model="feedback_alert_enable" value="1" class="mr-1"> Yes</label>
              <label><input type="radio" wire:model="feedback_alert_enable" value="0" class="mr-1"> No</label>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">        

          <!-- Alert Receiver Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-white">Alert Receiver Email</label>
            <input type="email" wire:model="feedback_alert_email" class="w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 bg-white dark:border-gray-600 dark:bg-gray-800 dark:text-white" style="margin:0" placeholder="example@email.com">
          </div>

          <!-- Feedback Alert Threshold -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-white">Feedback Alert Threshold</label>
            <input type="number" wire:model="feedback_alert_threshold" min="1" max="5" class="w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 bg-white dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Enter threshold (e.g. 3)">
          </div>
        </div>
      </div>


    
  </div>

{{-- New ui code start --}}



<div class="rounded-lg shadow border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4 space-y-6">

  <!-- Survey Settings -->
  <div class="bg-white shadow rounded-lg p-4 space-y-10 dark:bg-white/[0.03]">

    <h1 class="text-2xl font-bold">Post-Interaction Rating Survey Settings</h1>

    <!-- 1. Survey Status -->
    <section>
      <h2 class="text-xl font-semibold mb-2">Survey Status</h2>
      <label class="flex items-center gap-3">
        <input type="checkbox" wire:model="enable_post_interaction" class="toggle toggle-primary" id="surveyStatus" />
        <span>Enable post-interaction surveys</span>
      </label>
    </section>

    <!-- 2. Trigger Configuration -->

    <section>
      <h2 class="text-xl font-semibold mb-2">Trigger Configuration</h2>
      <div class="space-y-4">
        <div>
          <label class="block font-medium mb-1">Trigger Method</label>
          <div class="space-y-2">
            <label class="flex items-center gap-2">
              <input type="radio" name="triggerMethod" value="auto" wire:model.live="trigger_method" class="radio radio-primary" {{$trigger_method == 'auto' ? 'checked': '' }}>
              <span>Automatic (after each interaction)</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="radio" name="triggerMethod" value="random" wire:model.live="trigger_method" class="radio radio-primary" {{$trigger_method == 'random' ? 'checked': '' }}>
              <span>Randomized</span>
            </label>
            <div class="ml-6 flex items-center gap-2">
              <label for="randomPercent" class="text-sm">Send to</label>
              <input id="randomPercent" type="number" min="1" max="100" value="20" wire:model="random_percent" class="input input-sm input-bordered w-20 bg-white dark:border-gray-600 dark:bg-gray-800 dark:text-white">
              <span class="text-sm">%</span>
            </div>
            <label class="flex items-center gap-2">
              <input type="radio" name="triggerMethod" value="manual" wire:model.live="trigger_method" class="radio radio-primary" {{$trigger_method == 'manual' ? 'checked': '' }}>
              <span>Manual trigger by admin/supervisor</span>
            </label>
          </div>
        </div>
         @if($trigger_delay_enable)
        <div>
          <label class="block font-medium mb-1">Trigger Delay (in minutes)</label>
          <input type="number" placeholder="e.g., 10" wire:model="trigger_delay" class="input input-bordered w-full" />
        </div>
        {{-- <div>
          <label class="block font-medium mb-1">Email Template</label>
          <button class="btn btn-sm btn-outline btn-primary">Preview / Edit Template</button>
        </div> --}}
        @endif
      </div>
    </section>

    <!-- 3. Scheduling Panel -->
     @if($survey_scheduling_enable)
    <section>
      <h2 class="text-xl font-semibold mb-2">Survey Scheduling</h2>
      <label class="flex items-center gap-3 mb-4">
        <input type="checkbox" class="checkbox" id="enableScheduling" wire:model.live="enable_scheduling" />
        <span>Enable scheduling for fixed periods</span>
      </label>
        <!-- Show schedule table only when enabled -->
    @if ($enable_scheduling)
        <div class="space-y-3" id="scheduleTable">
            @foreach ($schedules as $index => $schedule)
                <div class="grid grid-cols-5 gap-4 items-center">
                    <!-- Duration type -->
                    <select wire:model="schedules.{{ $index }}.schedule_duration_type"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200 focus:border-blue-500 bg-white dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">Select Option</option>
                        <option value="1">Current Date</option>
                        {{-- <option value="2">Last Date</option>
                        <option value="3">Last Week</option>
                        <option value="4">Last Month</option> --}}
                    </select>

                    <!-- Duration -->
                   <input type="time" onclick="this.showPicker()"
                   class="input input-bordered bg-white dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                  wire:model="schedules.{{ $index }}.start_time">

        <!-- End Time -->
                  <input type="time" onclick="this.showPicker()"
                  class="input input-bordered  bg-white dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                  wire:model="schedules.{{ $index }}.end_time">
                    <!-- Delete button -->
                    {{-- <button type="button" class="btn btn-error btn-sm"
                        wire:click="removeSchedule({{ $index }})">
                        Delete
                    </button> --}}
                </div>
            @endforeach
        </div>

        <!-- Add Schedule button -->
        {{-- <button type="button" class="btn btn-sm btn-outline mt-4" wire:click="addSchedule">
            + Add Schedule
        </button> --}}
    @endif
    </section>
@endif
    <!-- 5. Manual Trigger -->
    
    @if($manual_trigger_enable)
    <section>
    <h2 class="text-xl font-semibold mb-2">Manual Trigger</h2>
    <div class="flex flex-col sm:flex-row gap-4 items-center">
        <input 
            type="text" 
            class="input input-bordered w-4/5 bg-white dark:border-gray-600 dark:bg-gray-800 dark:text-white" 
            placeholder="Search interaction (token,name,phone)" 
            wire:model="manual_trigger"
        >

        <button 
            type="button"
            wire:click="sendManualMail"
            wire:loading.attr="disabled"
            wire:target="sendManualMail"
            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow flex items-center justify-center"
        >
            <span wire:loading.remove wire:target="sendManualMail">
                Send Survey Now
            </span>
            <span wire:loading wire:target="sendManualMail" class="flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Sending...
            </span>
        </button>
    </div>
</section>
    @endif

    <!-- 4.1 Rating Style -->
    <section>
      <h2 class="text-xl font-semibold mb-2">Rating Style</h2>
      <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">Choose how customers will select their rating.</p>
      <div class="flex flex-col gap-3 sm:flex-row">
        <!-- Stars Option -->
        <label class="flex items-center gap-3 border p-3 rounded-lg cursor-pointer hover:border-primary">
          <input type="radio" value="stars" class="radio radio-primary" wire:model="rating_style" {{ $rating_style=='rating_style' ? 'checked' : ''}}>
          <div class="flex items-center gap-2">
            <span>⭐ Star Rating</span>
            <div class="flex text-yellow-400">
              <span class="text-lg">⭐</span>
              <span class="text-lg">⭐</span>
              <span class="text-lg">⭐</span>
            </div>
          </div>
        </label>
        <!-- Smilies Option -->
        <label class="flex items-center gap-3 border p-3 rounded-lg cursor-pointer hover:border-primary">
          <input type="radio" value="smilies" class="radio radio-primary" wire:model="rating_style" {{ $rating_style=='smilies' ? 'checked' : ''}}>
          <div class="flex items-center gap-2">
            <span>😊 Smiley Rating</span>
            <div class="flex">
              <span class="text-2xl">😞</span>
              <span class="text-2xl ml-1">😐</span>
              <span class="text-2xl ml-1">😊</span>
            </div>
          </div>
        </label>
      </div>
    </section>

 

    <!-- 6. Actions -->
    <section class="flex justify-end pt-6 border-t mt-6 gap-3">
      {{-- <button class="btn btn-outline">Preview Email</button> --}}
      {{-- <button class="btn btn-primary">Save Settings</button> --}}
        <!-- Submit -->
      <div class="flex justify-end">
        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow">
          {{ __('setting.Save') }}
        </button>
      </div>

    </form>
    </section>

   <!-- Flash Message -->
      {{-- @if ($successMessage)
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" id="alert">
        <strong class="font-bold">✔ {{ __('Settings Updated Successfully') }}</strong>
      </div>
      @endif --}}

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- JavaScript for dynamic behavior -->
<script>
  // Rating Comment Box Module Enable/Disable
  document.querySelectorAll('input[name="commentBoxEnable"]').forEach(el => {
    el.addEventListener('change', () => {
      document.getElementById('commentBoxThreshold').disabled = el.value !== 'yes';
    });
  });

  // Feedback Alert Settings Enable/Disable
  document.querySelectorAll('input[name="alertEnable"]').forEach(el => {
    el.addEventListener('change', () => {
      const disabled = el.value !== 'yes';
      document.getElementById('alertEmail').disabled = disabled;
      document.getElementById('alertThreshold').disabled = disabled;
    });
  });

  // Trigger Method Random % Enable/Disable
  document.querySelectorAll('input[name="triggerMethod"]').forEach(el => {
    el.addEventListener('change', () => {
      document.getElementById('randomPercent').disabled = el.value !== 'random';
    });
  });

  // Scheduling Panel Toggle
  // document.getElementById('enableScheduling').addEventListener('change', e => {
  //   document.getElementById('scheduleTable').classList.toggle('hidden', !e.target.checked);
  // });



</script>

<script>
  document.addEventListener('livewire:init', function () {
    Livewire.on('toggle-scheduling', function(data) {
    document.getElementById('scheduleTable').classList.toggle('hidden', !data.enabled);
});

    Livewire.on('hide-alert', () => {
      setTimeout(() => {
        document.getElementById('alert')?.remove();
        Livewire.dispatch('resetSuccessMessage');
      }, 3000);
    });

    Livewire.on('saved', () => {

        Swal.fire({
            title: "Saved successfully!",
            text: 'Success',
            icon: "success",
            // allowOutsideClick: false,
        }).then((result) => {
            window.location.reload();
        });
    });

     Livewire.on('success', (data) => {
      
        Swal.fire({
            icon: data[0].icon || 'success',
            title: data[0].title || 'Success',
            text: data[0].text || '',
        });
    });


     // Listen for error event and show SweetAlert
    Livewire.on('error', (data) => {
      Swal.fire({
        icon: data[0].icon || 'error',
        title: data[0].title || 'Error',
        text: data[0].text || 'Something went wrong',
      });
    });
  });
</script>




</main>
</div>

