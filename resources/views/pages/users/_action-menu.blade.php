<!--begin::Action--->
<td class="text-end">
    <a href="javascript:void(0)" data-edit="{{ route('users.edit', $model->id) }}" data-bs-toggle="modal" data-bs-target="#kt_modal_user" class="user_edit btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
        {!! theme()->getSvgIcon("icons/duotune/art/art005.svg", "svg-icon-3") !!}
    </a>

    <a href="#" data-destroy="{{ route('users.destroy', $model->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
        {!! theme()->getSvgIcon("icons/duotune/general/gen027.svg", "svg-icon-3") !!}
    </a>
</td>
<!--end::Action--->
