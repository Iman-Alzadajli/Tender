<x-app-layout>

    @push('styles')
    {{-- We are pushing the required CSS links to the main layout --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('/css/dashboard.css' ) }}">
    @endpush

    {{-- هذا السطر سيقوم باستدعاء وعرض مكون Livewire --}}
    @livewire('dashboard.dashboard')

    @push('scripts')
    {{-- We push the required JS libraries to the main layout --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- تأكد من أن هذا المسار صحيح --}}
    <script src="{{ asset('/js/dashboard.js' ) }}"></script>
    @endpush

</x-app-layout>
