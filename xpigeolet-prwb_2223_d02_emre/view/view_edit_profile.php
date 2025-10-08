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

  <title>Edit Profil</title>
  <script>
    let justValidateActive = <?= Configuration::get("justValidate"); ?>;

    $(document).ready(function() {
      if (justValidateActive) {
        console.log(justValidateActive);
        validationFormWithJustValidate();
      }
      
      var formModified = false;

      // Listen for changes in form inputs
      $('#edit-profil-form :input').on('input change', function() {
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
      const validation = new JustValidate('#edit-profil-form', {
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
        .onSuccess(function(event) {
          event.preventDefault();
          event.target.submit();

        });

      $("input:text:first").focus();
    }
  </script>
</head>

<body>
  <form action="user/edit_profile/<?= $user->getId() ?>" method="post" id="edit-profil-form">
    <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">

      <a href="user/settings" class="btn btn-outline-danger" id="backBtn">Back</a>


      <button type="submit" class="btn btn-outline-primary">Edit</button>
    </nav>
    <div class="container mt-5">
      <h3 class="text-center">You are editing your profil :) </h3>
      <div class="form-group">
        <label>Your email is : </label>
        <input id="email" type="email" name="email" class="form-control" value="<?= $user->getEmail() ?>">
      </div>
      <div class="form-group">
        <label>You name is <?= $user->getFullname() ?> . Do you want to change it ? </label>
        <input id="name" type="text" name="name" class="form-control" value="<?= $user->getFullname() ?>">
      </div>
      <div class="form-group">
        <label>
          Your iban is :
        </label>

        <div class="input-group mb-3">
          <span class="input-group-text" id="basic-addon1">BE</span>
          <input id="iban" type="text" class="form-control" name="iban" value="<?= $user->getIban() ?>" aria-describedby="basic-addon1">
        </div>
      </div>

      <?php if (count($errors) != 0) : ?>
        <div class="alert alert-danger mt-5">
          <h5>Please correct the following error(s) :</h5>
          <ul>
            <?php foreach ($errors as $error) : ?>
              <li><?= $error ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </form>
</body>

</html>