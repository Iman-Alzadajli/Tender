<x-app-layout>

    @push('styles')
    {{-- We are pushing the required CSS links to the main layout --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('/css/dashboard.css' ) }}">
    @endpush

    {{-- ذا السطر بيقوم باستدعاء وعرض مكون Livewire --}}
    @livewire('dashboard.dashboard') {{--موقعه--}}

    @push('scripts')
    {{-- JS libraries --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- js for dashboard --}}
    <script src="{{ asset('/js/dashboard.js' ) }}"></script>
    @endpush

</x-app-layout>
