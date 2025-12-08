<div class="container mx-auto flex justify-center items-center">
    <div class="flex flex-col text-center"> 
        <h2 class="mb-2 text-4xl font-semibold text-gray-900">Satisfaction</h2>
        @if (session('message'))
        <div class="flex justify-center text-red-600">
         Error: {{ session('message') }}
        </div>
    @endif 

           <!-- Loader Element -->
           <div id="loader" class="flex justify-center hidden">
            <svg class="animate-spin h-28 w-14 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8h8a8 8 0 11-8-8z"></path>
            </svg>
        </div>
        

        <form id="form" wire:submit="create" method="POST">
            @csrf
           @if($showquestion)
            @forelse($questions as $key => $question)
                <div class="question mt-4 mb-5 px-3 {{ $key !== 0 ? 'hidden' : '' }}" data-question-index="{{ $key }}">
              
                    <h3 class="text-xl py-2 px-4">{{ $question['question'] }}</h3>
                    <div class="rating-section">
                        @if($rating_style == 'smilies')
                        <ul class="grid w-full gap-2 md:gap-6 grid-cols-4">
                            @foreach(App\Models\Queue::getEmojiText() as $emojiKey => $value)
                                <li>
                                    <input type="radio" id="emoji-{{$key}}-{{$emojiKey}}" wire:model="response.{{$key}}.{{$question['question'] }}" name="responses[{{$key}}]" value="{{$emojiKey}}" class="hidden peer" required />
                                    <label for="emoji-{{$key}}-{{$emojiKey}}" class="inline-flex items-center justify-between w-full md:p-5 p-2 text-gray-800 cursor-pointer border border-gray-300 bg-gray-100 rounded-lg mt-3 text-cente mb-4">
                                        <div class="block space-y-4 text-center m-auto">
                                            <div class="w-full text-4xl md:text-4xl font-semibold">{{$value['emoji']}}</div>
                                            <div class="w-full text-base md:text-xl">{{$value['label']}}</div>
                                        </div>
                                    </label>
                                </li>
                                @endforeach
                            </ul>
                          @elseif($rating_style == 'stars')

                                    <style>
                                        .star-rating {
                                            direction: rtl; /* Highlights leftward */
                                            display: inline-flex;
                                            justify-content: center;
                                            align-items: center;
                                            gap: 5px;
                                        }
                                        .star-rating input {
                                            display: none;
                                        }
                                        .star-rating label {
                                            font-size: 2.5rem;
                                            color: #ccc;
                                            cursor: pointer;
                                            transition: color 0.2s ease;
                                        }
                                        .star-rating input:checked ~ label,
                                        .star-rating label:hover,
                                        .star-rating label:hover ~ label {
                                            color: gold;
                                        }
                                    </style>
                                 <ul class="grid w-full">
                                    <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                                        <div class="star-rating">
                                            @for($i = 5; $i >= 1; $i--)
                                                <input 
                                                    type="radio" 
                                                    id="star-{{$key}}-{{$i}}" 
                                                    wire:model="response.{{$key}}.{{$question['question']}}" 
                                                    name="responses[{{$key}}]" 
                                                    value="{{$i}}" 
                                                    required 
                                                />
                                                <label for="star-{{$key}}-{{$i}}">★</label>
                                            @endfor
                                        </div>
                                    </div>
                                </ul>
                                @endif
                    </div>
                </div>
                
            @empty
                <p>No questions available</p>
            @endforelse

          @endif

            @if($showCommentSection)
                <div class="mt-6">
                    <label for="comment" class="block text-lg font-medium text-gray-700 mb-2">Leave a comment:</label>
                    <textarea id="comment" name="comment" wire:model.defer="comment"
                            rows="4"
                            class="w-full p-3 border rounded-md border-gray-300 focus:ring focus:ring-blue-200 focus:outline-none resize-none"
                            placeholder="Tell us more about your experience..." required></textarea>
                </div>
                <div id="submit-section" class="mt-6">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition disabled:opacity-70 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Submit Feedback</span>
                        <span wire:loading>
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form');
        const questions = document.querySelectorAll('.question');
        let currentQuestionIndex = 0;

        form.addEventListener('change', function (event) {
            if (event.target.matches('input[type="radio"]')) {
                questions[currentQuestionIndex].classList.add('hidden');
                currentQuestionIndex++;
                if (currentQuestionIndex < questions.length) {
                    questions[currentQuestionIndex].classList.remove('hidden');
                } else {
                    if (@json($showCommentBox)) {
                        Livewire.dispatch('check-average');
                    } else {
                        console.log('Dispatching rating-submit...');
                        document.getElementById('loader')?.classList.remove('hidden');
                        Livewire.dispatch('rating-submit');
                    }
                }
            }
        });
    });

    document.addEventListener('livewire:init', function () {
        Livewire.on('submit-rating', () => {
            console.log('Received submit-rating event');
            document.getElementById('loader')?.classList.remove('hidden');
            Livewire.dispatch('rating-submit');
        });
    });
</script>
