<x-base-layout>
{{-- {{ dd(auth()->user()->info->communication) }} --}}
{{ theme()->getView('pages/account/_navbar', array('class' => 'mb-5 mb-xl-10', 'info' => auth()->user()->info)) }}

{{ theme()->getView('pages/account/overview/_details', array('class' => 'mb-5 mb-xl-10', 'info' => auth()->user()->info)) }}

</x-base-layout>
