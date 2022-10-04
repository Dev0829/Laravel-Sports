<x-base-layout>
    {{-- <div class="col-xxl-4">
        {{ theme()->getView('partials/modals/two-factor-authentication/_main') }}
    </div> --}}
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card body-->
        <div class="card-body pt-6">
            <button id="create_user" class="btn btn-sm btn-primary btn-active-primary" data-create="{{ route('users.create') }}" data-bs-toggle="modal" data-bs-target="#kt_modal_user">
                Create
            </button>
            <!--begin::Table-->
            {{ $dataTable->table() }}
            <!--end::Table-->

            {{-- Inject Scripts --}}
            @section('scripts')
                {{ $dataTable->scripts() }}
            @endsection

        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    {{ theme()->getView('pages/users/edit') }}
</x-base-layout>
