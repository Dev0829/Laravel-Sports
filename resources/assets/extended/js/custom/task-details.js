var KTTaskDetails = function () {
  // Private variables
  var form;
  var submitButton;
  var validation;

  // Private functions
  var initValidation = function () {
      // Init form validation rules. For more info check the FormValidation plugin's official documentation: https://formvalidation.io/
      validation = FormValidation.formValidation(
          form,
          {
              fields: {
                  name: {
                      validators: {
                          notEmpty: {
                              message: 'Task name is required'
                          }
                      }
                  },
                  overview: {
                      validators: {
                          notEmpty: {
                              message: 'Task overview is required'
                          }
                      }
                  },
              },
              plugins: {
                  trigger: new FormValidation.plugins.Trigger(),
                  bootstrap: new FormValidation.plugins.Bootstrap5({
                      rowSelector: '.fv-row',
                      eleInvalidClass: '',
                      eleValidClass: ''
                  })
              }
          }
      );
  }

  var handleForm = function () {
      submitButton.addEventListener('click', function (e) {
          e.preventDefault();

          // Validate form
          validation.validate().then(function (status) {
              if (status === 'Valid') {
                  // Show loading indication
                  submitButton.setAttribute('data-kt-indicator', 'on');

                  // Disable button to avoid multiple click
                  submitButton.disabled = true;

                  // Send ajax request
                  axios.post(submitButton.closest('form').getAttribute('action'), new FormData(form))
                      .then(function (response) {
                          // Show message popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                          Swal.fire({
                              text: "Thank you! You've updated your basic info",
                              icon: "success",
                              buttonsStyling: false,
                              confirmButtonText: "Ok, got it!",
                              customClass: {
                                  confirmButton: "btn btn-primary"
                              }
                          }).then(function (result) {
                              if (result.isConfirmed) {
                              }
                          });
                      })
                      .catch(function (error) {
                          let dataMessage = error.response.data.message;
                          let dataErrors = error.response.data.errors;

                          for (const errorsKey in dataErrors) {
                              if (!dataErrors.hasOwnProperty(errorsKey)) continue;
                              dataMessage += "\r\n" + dataErrors[errorsKey];
                          }

                          if (error.response) {
                              Swal.fire({
                                  text: dataMessage,
                                  icon: "error",
                                  buttonsStyling: false,
                                  confirmButtonText: "Ok, got it!",
                                  customClass: {
                                      confirmButton: "btn btn-primary"
                                  }
                              });
                          }
                      })
                      .then(function () {
                          // always executed
                          // Hide loading indication
                          submitButton.removeAttribute('data-kt-indicator');

                          // Enable button
                          submitButton.disabled = false;
                      });
              } else {
                  // Show error popup. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                  Swal.fire({
                      text: "Sorry, looks like there are some errors detected, please try again.",
                      icon: "error",
                      buttonsStyling: false,
                      confirmButtonText: "Ok, got it!",
                      customClass: {
                          confirmButton: "btn btn-primary"
                      }
                  });
              }
          });
      });
  }

  // Public methods
  return {
      init: function () {
          form = document.getElementById('kt_task_details_form');
          submitButton = form.querySelector('#kt_task_details_submit');

          initValidation();
          handleForm();
      }
  }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
  KTTaskDetails.init();
});