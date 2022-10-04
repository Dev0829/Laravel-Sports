$(function(){window.{{ config('datatables-html.namespace', 'LaravelDataTables') }}=window.{{ config('datatables-html.namespace', 'LaravelDataTables') }}||{};window.{{ config('datatables-html.namespace', 'LaravelDataTables') }}["%1$s"]=$("#%1$s").DataTable(%2$s);
  $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
  {{ config('datatables-html.namespace', 'LaravelDataTables') }}["%1$s"].on('click', '[data-destroy]', function (e) {
    e.preventDefault();
    if (!confirm("{{ __('Are you sure to delete this record?') }}")) {
      return;
    }
    axios.delete($(this).data('destroy'), {
      '_method': 'DELETE',
    })
    .then(function (response) {
      {{ config('datatables-html.namespace', 'LaravelDataTables') }}["%1$s"].ajax.reload();
    })
    .catch(function (error) {
      console.log(error);
    });
  });

  {{ config('datatables-html.namespace', 'LaravelDataTables') }}["%1$s"].on('click', '.user_edit[data-edit]', function (e) {
    e.preventDefault();
    axios.post($(this).data('edit'), {
      '_method': 'GET',
    })
    .then(function (response) {
      $('#id').val(response.data.info.id);
      console.log(response);
      if (response.data.info.info.avatar) {
        $('#avatar').css("backgroundImage", 'url(storage/' + response.data.info.info.avatar + ')');
      } else {
        $('#avatar').css("backgroundImage", 'unset');
      }
      $('#first_name').val(response.data.info.first_name);
      $('#last_name').val(response.data.info.last_name);
      $('#company').val(response.data.info.info.company);
      $('#phone').val(response.data.info.info.phone);
      $('#website').val(response.data.info.info.website);
      $('#country').find('option[value="'+response.data.info.info.country+'"]').prop('selected', true);
      $('#country').select2().trigger('change');
      $('#currency').find('option[value="'+response.data.info.info.currency+'"]').prop('selected', true);
      $('#currency').select2().trigger('change');
      $('#language').find('option[value="'+response.data.info.info.language+'"]').prop('selected', true);
      $('#language').select2().trigger('change');
      $('#timezone').find('option[value="'+response.data.info.info.timezone+'"]').prop('selected', true);
      $('#timezone').select2().trigger('change');
      if (response.data.info.info.marketing == 1) {
        $('#allowmarketing').prop('checked', true);
      } else {
        $('#allowmarketing').prop('checked', false);
      }
      if (response.data.info.info.communication !== null) {
        if (response.data.info.info.communication.email == 1) {
          $('#communication_email').prop('checked', true);
        } else {
          $('#communication_email').prop('checked', false);
        }

        if (response.data.info.info.communication.phone == 1) {
          $('#communication_phone').prop('checked', true);
        } else {
          $('#communication_phone').prop('checked', false);
        }
      }
    })
    .catch(function (error) {
      console.log(error);
    });
  });
});

$(document).ready(function() {
  $("#create_user").css('position', 'absolute');
  $("#create_user").css('top', '35px');
  $("#create_user").on("click", function() {
    $("input[name='_method']").val("POST");
    $('#avatar').css("backgroundImage", 'unset');
    $('#first_name').val('');
    $('#last_name').val('');
    $('#company').val('');
    $('#phone').val('');
    $('#website').val('');
    $('#country').val('1');
    $('#country').select2().trigger('change');
    $('#currency').val('1');
    $('#currency').select2().trigger('change');
    $('#language').val('1');
    $('#language').select2().trigger('change');
    $('#timezone').val('1');
    $('#timezone').select2().trigger('change');
    $('#allowmarketing').prop('checked', false);
    $('#communication_email').prop('checked', false);
    $('#communication_phone').prop('checked', false);
  });

  $('.modal').on('shown.bs.modal', function (e) {
    $(this).find('select').select2({
        dropdownParent: $(this).find('.modal-content')
    });
  });
});