<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <base href="<?= $web_root ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <script src="https://kit.fontawesome.com/981b5a6232.js" crossorigin="anonymous"></script>
  <script src="lib/sweetalert2@11.js" type="text/javascript"></script>

  <script src="lib/jquery-3.6.3.min.js" type="text/javascript"></script>
  <script src="lib/just-validate-4.2.0.production.min.js" type="text/javascript"></script>
  <script src="lib/just-validate-plugin-date-1.2.0.production.min.js" type="text/javascript"></script>
  <script>
    let justValidateActive = <?= Configuration::get("justValidate"); ?>;

    let titleAvailable;

    console.log("Just Validate est en mode : " + justValidateActive);



    $(function() {
      leavingBeforeSaving();
      if (justValidateActive) {
        validationFormWithJustValidate();
      } else {
        validationForm();
      }

    });

    function validationFormWithJustValidate() {

      const tricount_title = '';

      const validation = new JustValidate('#add-tricount-form', {
        validateBeforeSubmitting: true,
        lockForm: true,
        focusInvalidField: false,
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
      });

      validation
        .addField('#title', [{
            rule: 'required',
            errorMessage: 'Field is required'
          },
          {
            rule: 'minLength',
            value: 3,
            errorMessage: 'Minimum 3 characters'
          },

          {
            rule: 'customRegexp',
            value: /^[\w\s]{3,}$/,
            errorMessage: 'Invalid title'

          },

          {
            rule: 'maxLength',
            value: 256,
            errorMessage: 'Maximum 256 characters'
          },
        ], {
          successMessage: 'Looks good!'
        })
        .addField('#description', [{
            rule: 'minLength',
            value: 3,
            errorMessage: 'Minimum 3 characters'
          },
          {
            rule: 'maxLength',
            value: 256,
            errorMessage: 'Maximum 256 characters'
          },
        ], {
          successMessage: 'Looks good!'
        })

        .onValidate(async function(event) {
          const title = $("#title").val();

          try {
            const response = await $.ajax({
              url: 'tricount/title_available_service',
              method: 'POST',
              data: {
                param1: title,
                tricount_title: tricount_title
              },
              dataType: 'json'
            });

            titleAvailable = response; // Update the global titleAvailable variable

            console.log(titleAvailable);

            if (!titleAvailable) {
              this.showErrors({
                '#title': 'Title already exists'
              });
            }
          } catch (error) {
            console.error("Error checking title availability:", error);
          }
        })

        .onSuccess(function(event) {
          if (titleAvailable) {
            event.target.submit();
          }
        });

      $("input:text:first").focus();
    }




    function validationForm() {
      const form = $('#add-tricount-form');
      const titleInput = $('#title');
      const descriptionInput = $('#description');

      titleInput.on('input', validateTitle);
      descriptionInput.on('input', validateDescription);
      form.on('submit', function(event) {
        if (!validateForm()) {
          event.preventDefault();
        }
      });

      function validateForm() {
        let isTitleValid = validateTitle();
        let isDescriptionValid = validateDescription();
        let isDescriptionEmpty = $('#description').val().trim().length == 0;
        return isTitleValid && (isDescriptionValid || isDescriptionEmpty) && ($('#title-validation').text() == 'Title is good !' || $('#title-validation').text() == '');
      }





      function validateTitle() {
        let new_title = titleInput.val().trim();
        if (new_title.length >= 3) {
          $.ajax({
            url: 'tricount/compareTitleTricount',
            method: 'POST',
            data: {
              new_title: new_title
            },
            dataType: 'json',
            success: function(response) {
              if (response === true) {
                $('#title-validation').text('Title not good :(').removeClass('text-danger').addClass('text-success');
                titleInput.removeClass('is-invalid').addClass('is-valid');
              } else {
                $('#title-validation').text('Title is good !').removeClass('text-danger').addClass('text-success');
                titleInput.removeClass('is-invalid').addClass('is-valid');
              }
            },
            error: function() {
              console.log("title not ok");
              $('#title-validation').text('Title already exists ').removeClass('text-success').addClass('text-danger');
              titleInput.removeClass('is-valid').addClass('is-invalid');
            }
          });
          return true;
        } else {
          $('#title-validation').text('The title must have at least 3 characters').removeClass('text-success').addClass('text-danger');
          titleInput.removeClass('is-valid').addClass('is-invalid');
          return false;
        }
      }

      function validateDescription() {
        let descriptionInput = $('#description');
        let descriptionValidation = $('#description-validation');
        let descriptionError = $('#description-error');

        let new_description = descriptionInput.val().trim();
        if (new_description.length >= 3) {
          descriptionValidation.text('Description valide').removeClass('text-danger').addClass('text-success');
          descriptionInput.removeClass('is-invalid').addClass('is-valid');
          return true;
        } else if (new_description.length === 0) {
          descriptionValidation.text('');
          descriptionInput.removeClass('is-invalid').removeClass('is-valid');
          return false;
        } else {
          descriptionValidation.text('Description non valide').removeClass('text-success').addClass('text-danger');
          descriptionInput.removeClass('is-valid').addClass('is-invalid');
          return false;
        }
      }
      $('#description').on('input', validateDescription);
    }

    function leavingBeforeSaving() {
      var formModified = false;

      // Listen for changes in form inputs
      $('#add-tricount-form :input').on('input change', function() {
        formModified = true;
      });

      // Show SweetAlert2 confirmation modal when clicking on Back button
      $('#backBtn').on('click', function(event) {
        event.preventDefault();

        if (formModified) {
          Swal.fire({
            title: 'Unsaved changes!',
            text: 'You have unsaved changes. Are you sure you want to leave without saving?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Leave without saving',
            cancelButtonText: 'Cancel'
          }).then(function(result) {
            if (result.isConfirmed) {
              window.location.href = $(event.target).attr('href');
            }
          });
        } else {
          window.location.href = $(event.target).attr('href');
        }
      });
    }
  </script>

  <title>Add Tricount</title>

</head>

<body>
  <form action="tricount/add_tricount" method="post" id="add-tricount-form" data-just-validate-ignore>
    <nav class="navbar navbar-light" style="background-color:#e3f2fd;">
      <a href="tricount/index" class="btn btn-outline-danger" id="backBtn">Back</a>
      <div>
        Tricount <i class="bi bi-caret-right"></i> Add
      </div>
      <input type="submit" class="btn btn-outline-primary" value="Add">
    </nav>

    <div class="container p-5">
      <h2 class="text-center">Add a new tricount</h2>
      <div class="form-group text-center">
        <label for="title">Title:</label>
        <input type="text" class="form-control" id="title" name="title">
        <div id="title-error"></div>
        <p id="title-validation"></p>
      </div>
      <input type="hidden" name="creatorID" id="creatorID" value="<? $user->getId() ?>">
      <div class="form-group text-center">
        <label for="description">Description:</label>
        <textarea class="form-control" id="description" name="description"></textarea>
        <div id="description-validation"></div>
        <div id="description-Error"></div>

      </div>
      <input type="hidden" id="created_at" name="created_at" value="<?= time() ?>">
    </div>
  </form>

  <?php if (count($errors) != 0) : ?>
    <div class="alert alert-danger mt-5">
      <p class="text-danger">Please correct the following error(s) :</p>
      <ul>
        <?php foreach ($errors as $error) : ?>
          <li class="text-danger"><?= $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

</html>