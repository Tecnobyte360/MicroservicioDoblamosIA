<div
    wire:loading
    @if($target) wire:target="{{ $target }}" @endif
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm"
>
    <div class="flex flex-col items-center gap-4 rounded-2xl bg-white dark:bg-slate-900 px-6 py-5 shadow-2xl">
        
        <!-- Spinner -->
        <svg class="h-10 w-10 animate-spin text-[#E56830]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
            </path>
        </svg>

        <!-- Texto -->
        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ $text }}
        </p>
    </div>
</div>
