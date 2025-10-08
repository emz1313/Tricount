<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Change password</title>
  <base href="<?= $web_root ?>" />
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
    $(document).ready(function() {

      var formModified = false;

      // Listen for changes in form inputs
      $('#edit-password-form :input').on('input change', function() {
        formModified = true;
      });
      // Show SweetAlert2 confirmation modal when clicking on Back button
      $('#backBtn').click(function(event) {
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
    });

    function validationFormWithJustValidate() {
      const validation = new JustValidate('#edit-password-form', {
        validateBeforeSubmitting: true,
        lockForm: true,
        focusInvalidField: false,
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
      });

      validation
        .addField('#password', [{
            rule: 'required',
          },
          {
            rule: 'customRegexp',
            value: /^.*(?=.{8,})(?=.*\d)(?=.*[A-Z])(?=.*\W).*$/,
            errorMessage: 'The password must be at least 8 characters long, contain at least one digit, one uppercase letter, and one non-alphanumeric character.'
          }

        ], {
          succesMessage: 'bon mot de passe'
        })
        .addField('#newPassword', [{
            rule: 'required',
          }, {
            rule: 'customRegexp',
            value: /^.*(?=.{8,})(?=.*\d)(?=.*[A-Z])(?=.*\W).*$/,
            errorMessage: 'The password must be at least 8 characters long, contain at least one digit, one uppercase letter, and one non-alphanumeric character.',
          },
          {
            validator: (value, fields) => {
              if (
                fields['#password'] &&
                fields['#newPassword'].elem
              ) {
                const repeatPasswordValue =
                  fields['#password'].elem.value;
                console.log(repeatPasswordValue);

                return value === repeatPasswordValue;
              }

              return true;
            },
            errorMessage: 'Passwords should be the same',
          },
        ], {
          succesMessage: 'bon mot de passe'
        })
        .onSuccess(function(event) {
          event.preventDefault();
          event.target.submit();

        });

      $("input:text:first").focus();
    }
    let justValidateActive = <?= Configuration::get("justValidate"); ?>;
    $(document).ready(function() {
      if (justValidateActive) {
        console.log(justValidateActive);
        validationFormWithJustValidate();
      }
    });
  </script>
</head>

<body>
  <form action="user/edit_password" method="POST" id="edit-password-form" class="edit-password-form">
    <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">
      <a href="user/settings" class="btn btn-outline-danger" id="backBtn">Back</a>

      <a class="navbar-brand" style="color: grey;">
        Settings
        <i class="bi bi-caret-right"></i>
        Change Password
      </a>
      <button type="submit" class="btn btn-outline-primary">Save </button>
    </nav>

    <div class="container mt-5">

      <h1 class="text-center">Change password for <?= $user->getFullname() ?> :</h1>

      <div class="form-group">
        <label><b>Password : </b></label>
        <input id="password" type="password" class="form-control" placeholder="Enter your new password" name="newPass" size="16">
      </div>
      <div class="form-group">
        <label><b>Confirm the Password : </b></label>
        <input id="newPassword" type="password" class="form-control" placeholder="Enter your new password" name="newPass_Confirm" size="16">
      </div>
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

</body>

</html>