<!DOCTYPE html>
<html lang="EN">

<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <base href="<?= $web_root ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Add the Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

  <script src="https://kit.fontawesome.com/981b5a6232.js" crossorigin="anonymous"></script>
  <script src="lib/sweetalert2@11.js" type="text/javascript"></script>
  <script src="lib/jquery-3.6.3.min.js" type="text/javascript"></script>
  <script src="lib/just-validate-4.2.0.production.min.js" type="text/javascript"></script>
  <script src="lib/just-validate-plugin-date-1.2.0.production.min.js" type="text/javascript"></script>
  <script>
    let justValidateActive = <?= Configuration::get("justValidate"); ?>;

    function validationFormWithJustValidate() {
      const validation = new JustValidate('#signupForm', {
        validateBeforeSubmitting: true,
        lockForm: true,
        focusInvalidField: false,
        errorFieldCssClass: 'is-invalid',
        successFieldCssClass: 'is-valid',
        errorLabelCssClass: 'invalid-feedback',
        successLabelCssClass: 'valid-feedback',
      });

      validation
        .addField('#email', [{
            rule: 'required',
          },
          {
            rule: 'email',
          }
        ], {
          succesMessage: 'bon mot de passe'
        })
        .addField('#name', [{
            rule: 'required',
          },
          {
            rule: 'minLength',
            value: 3,
            errorMessage: 'The name must be at least 3 characters long',
          },
          {
            rule: 'customRegexp',
            value: /^[a-zA-Z]+$/,
            errorMessage: 'You have to only use letters'
          }
        ], {
          succesMessage: 'bon mot de passe'
        })
        .addField("#iban", [{
            rule: 'minLength',
            value: 14,
            errorMessage: 'Minimum 14 digits exemple : exemple : 12345678912345'
          },
          {
            rule: 'maxLength',
            value: 14,
            errorMessage: 'Maximum 14 chiffres exemple : 12345678912345'
          }
        ], {
          succesMessage: 'bon mot de passe'
        })
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
        .addField('#password_confirm', [{
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
                fields['#password_confirm'].elem
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


    $(document).ready(function() {
      if (justValidateActive) {
        validationFormWithJustValidate();
      }
      var formModified = false;

      // Listen for changes in form inputs
      $('#signupForm :input').on('input change', function() {
        formModified = true;
      });
      // Show SweetAlert2 confirmation modal when clicking on Back button
      $('#bckBtn').click(function(event) {
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
  </script>
</head>

<body>
  <nav class="navbar navbar-light " style="background-color:#3075FF ;">
    <a class="navbar-brand" style="color :white"><i class="fa-solid fa-cat"></i>Tricount</a>
  </nav>
  <div class="main container">
    <h2 class="text-center">Sign Up</h2>
    <form id="signupForm" action="main/signup" method="post">
      <div class="form-group">
        <label for="mail">Email:</label>
        <input id="email" class="form-control" id="mail" name="mail" type="email" value="<?= $mail ?>">
      </div>
      <div class="form-group">
        <label for="full_name">Full Name:</label>
        <input id="name" class="form-control" id="full_name" name="full_name" type="text" value="<?= $full_name ?>">
      </div>
      <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon1">BE</span>
        <input id="iban" type="text" class="form-control" name="iban" value="<?= $iban ?>" aria-describedby="basic-addon1">
      </div>

      <div class="form-group">
        <label for="password">Password:</label>
        <input id="password" class="form-control" id="password" name="password" type="password" value="<?= $password ?>">
      </div>
      <div class="form-group">
        <label for="password_confirm">Confirm Password:</label>
        <input id="password_confirm" class="form-control" id="password_confirm" name="password_confirm" type="password" value="<?= $password_confirm ?>">
      </div>
      <div class="form-group text-center">
        <input class="btn btn-primary" type="submit" value="Sign Up">
        <a class="btn btn-secondary" href="main/index" id="bckBtn">Cancel</a>
      </div>
    </form>
    <?php if (count($errors) != 0) : ?>
      <div class="alert alert-danger mt-5">
        <h5>Please correct the following error(s) :</h5>
        <table class="table table-borderless mx-5">
          <tbody>
            <?php foreach ($errors as $error) : ?>
              <tr>
                <td style="color: red;"><?= $error ?>
                <td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</body>

</html>