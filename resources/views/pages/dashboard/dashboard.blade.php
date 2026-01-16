<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold text-[#132742] dark:text-[#F6EFEA]">
                    Dashboard
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Filter button -->
                <x-dropdown-filter align="right" />

                <!-- Datepicker -->
                <x-datepicker />

                <!-- Add view button -->
                <button class="btn bg-[#E56830] text-white hover:bg-[#cf5525] dark:bg-[#F6EFEA] dark:text-[#132742] dark:hover:bg-white transition duration-300 shadow-md">
                    <svg class="fill-current shrink-0 xs:hidden mr-2" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="max-xs:sr-only">Agregar vista</span>
                </button>

            </div>

        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-12 gap-6">

            <x-dashboard.dashboard-card-01 :dataFeed="$dataFeed" />
            <x-dashboard.dashboard-card-02 :dataFeed="$dataFeed" />
            <x-dashboard.dashboard-card-03 :dataFeed="$dataFeed" />
            <x-dashboard.dashboard-card-04 />
            <x-dashboard.dashboard-card-05 />
            <x-dashboard.dashboard-card-06 />
            <x-dashboard.dashboard-card-07 />
            <x-dashboard.dashboard-card-08 />
            <x-dashboard.dashboard-card-09 />
            <x-dashboard.dashboard-card-10 />
            <x-dashboard.dashboard-card-11 />
            <x-dashboard.dashboard-card-12 />
            <x-dashboard.dashboard-card-13 />

        </div>

    </div>
</x-app-layout>
