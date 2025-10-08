<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <base href="<?= $web_root ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <script src="https://kit.fontawesome.com/981b5a6232.js" crossorigin="anonymous"></script>
  <script src="lib/jquery-3.6.3.min.js" type="text/javascript"></script>
  <script src="lib/sweetalert2@11.js" type="text/javascript"></script>
  <script src="lib/just-validate-4.2.0.production.min.js" type="text/javascript"></script>
  <script src="lib/just-validate-plugin-date-1.2.0.production.min.js" type="text/javascript"></script>
  <style>
    .delete-sub {
      background-color: #ffffff;
      border: none;
      cursor: pointer;
      padding: 5px 10px;
      border-radius: 4px;
    }

    .delete-sub i {
      margin-right: 5px;
    }

    .delete-sub span {
      font-weight: bold;

    }

    .delete-sub:hover {
      background-color: #ffdddd;
    }

    .subscriber-item {
      width: 15%;
    }
  </style>

  <script>
    let justValidateActive = <?= Configuration::get("justValidate"); ?>;
    console.log("just validate est : " + justValidateActive);
    $(document).ready(function() {

      leaveWithoutSave();

      if (justValidateActive) {

        validationFormWithJustValidate();
      } else {
        console.log("Just validate est off donc le formulaire va se valider autrement");
        validationForm();
      }
    });

    function validationFormWithJustValidate() {
      let titleAvailable = true;
      const tricount_title = '<?= $tricount->getTitle() ?>'.trim();
      const validation = new JustValidate('#edit-tricount-form', {
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
            errorMessage: 'You have to enter a title'
          },
          {
            rule: 'minLength',
            value: 3,
            errorMessage: 'Title must have at least 3 characters'
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
          successMessage: 'Title is good!'
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
          successMessage: 'Description is good!'
        })

        .onValidate(async function(event) {
          const title = $("#title").val().trim();

          try {
            const response = await $.ajax({
              url: 'tricount/title_available_service',
              method: 'POST',
              data: {
                param1: title,
                tricount_title: tricount_title
              },
              dataType: 'json'
            })

            console.log(response);
            titleAvailable = response;
            const currentTitle = '<?= $tricount->getTitle() ?>'.trim(); // Remplacez cette fonction par la logique pour obtenir le titre du tricount actuel
            if (!titleAvailable && (title !== currentTitle)) {
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
            // Soumission du formulaire
            event.target.submit();
          }
        });

      $("input:text:first").focus();
    }


    //initialisation des non subs du tricount 
    let tricount_id = <?= $tricount->getId() ?>;
    let sub = <?= $subs_json ?>;

    // Tableau global pour stocker les noms d'utilisateurs
    let userNames = {};

    $(document).ready(function() {
      sortSubscribers(); // Sort the subscribers after removing one

      // When the delete button is clicked
      $('#subscriber-list').on('click', 'button.delete-sub', function(e) {
        e.preventDefault();

        // Get the ID of the form
        let formId = $(this).parent('form').attr('id');

        // Get the ID of the subscriber and the tricount
        let subscriberId = $(this).siblings('[name=user_id]').val();
        let tricountId = $(this).siblings('[name=tricount_id]').val();
        let fullname = userNames[subscriberId];

        // If the subscriber ID is undefined, display an error message and return
        if (typeof subscriberId === 'undefined') {
          alert('subscriberId is not defined');
          return;
        }

        // Send an AJAX request to delete the subscription
        $.ajax({
          type: "POST",
          url: "tricount/del_sub_json",
          dataType: "json",
          data: {
            user_id: subscriberId,
            tricount_id: tricountId
          },
          success: function(data) {
            // If the deletion was successful, remove the subscription from the page
            $("#" + formId).parent('li').remove();
            // Ajouter le nouveau abonné dans le selecteur de nouveau
            $('select[name="new_sub"]').append($('<option>', {
              value: subscriberId,
              text: userNames[subscriberId] // Utiliser le nom stocké dans le tableau global
            }));

            sortSubscribers(); // Sort the subscribers after removing one


          }
        });
      });

      // Stocker les noms d'utilisateurs dans le tableau global lors de la création de la page
      $('#new_sub option').each(function() {
        let value = $(this).val();
        let text = $(this).text();
        userNames[value] = text;
      });

      // Stocker les noms des souscripteurs dans le tableau global
      sub.forEach(function(user) {
        userNames[user.id] = user.fullname;
      });

      $('#add-button').on('click', function() {
        // Récupérer la valeur de l'ID sélectionné
        const selectedUserId = $('#new_sub').val();
        const tricountId = $('#tricount_id').val();

        $.ajax({
          type: 'POST',
          url: 'tricount/add_sub_json',
          dataType: "json",
          data: {
            new_sub: selectedUserId,
            tricount_id: tricountId
          },
          success: function(data) {
            // Si l'ajout a réussi, ajouter la souscription à la liste des abonnements
            // Vérifier que la requête AJAX a réussi
            if (data.success) {
              // Sélectionner la liste des abonnés existants
              const subscriberList = $('#subscriber-list');

              // Créer un nouvel élément pour le nouvel abonné
              const newSubscriber = $('<li>', {
                class: 'list-group-item d-flex justify-content-between subscriber-item',
                style: "width: 35%;",
                id: 'subscriber-' + data.user_id
              }).append($('<span>', {
                text: data.user_fullname
              })).appendTo(subscriberList);

              // Créer un formulaire pour la suppression de l'abonné
              const deleteForm = $('<form>', {
                id: 'delete-subscription-form-' + data.user_id,
                action: 'tricount/del_sub/' + data.user_id,
                method: 'post'
              }).appendTo(newSubscriber);

              // Ajouter des champs cachés pour l'ID du tricount et de l'utilisateur
              $('<input>', {
                type: 'hidden',
                name: 'tricount_id',
                value: tricountId
              }).appendTo(deleteForm);
              $('<input>', {
                type: 'hidden',
                name: 'user_id',
                value: data.user_id
              }).appendTo(deleteForm);

              // Ajouter un bouton pour supprimer l'abonné

              $('<button>', {
                type: 'submit',
                class: 'bi bi-trash-fill delete-sub',
                style: 'font-size:20px; ',
                id: 'sub_to_delete_' + data.user_id
              }).appendTo(deleteForm);
              // Supprimer la personne disponible du selecteur
              $('#new_sub option[value="' + selectedUserId + '"]').remove();
              sortSubscribers(); // Sort the subscribers after removing one
            } else {
              // Si une erreur s'est produite, afficher un message d'erreur et enregistrer un message dans la console
              alert(data.errors);
            }
          },
          error: function() {}
        });
        // Empêcher le comportement par défaut de l'événement de clic sur le bouton
        return false;
      });
    });


    // Fonction pour trier les abonnés
    function sortSubscribers() {
      let list = $("ul.list-group");
      let listItems = list.children("li");
      listItems.sort(function(a, b) {
        let p1 = $(a).text().trim();
        let p2 = $(b).text().trim();
        return p1.toUpperCase().localeCompare(p2);
      });
      list.append(listItems);
    }


    function validationForm() {
      const form = $('#edit-tricount-form');
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

      let isTitleModified = false;

      function validateTitle() {
        if (!isTitleModified) {
          return true;
        }

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

      // Ajoutez un gestionnaire d'événement pour détecter les modifications du champ de titre
      titleInput.on('input', function() {
        isTitleModified = true;
      });

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

    function confirmDelete(tricountId) {
      event.preventDefault();
      // Utilisez Sweet Alert pour afficher la fenêtre modale de confirmation
      Swal.fire({
        title: 'Are you sure?',
        html: 'Do you really want to delete tricount <?= $tricount->getTitle() ?> and all of his dependencies?<br><br>This process cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes,delete it!',
        cancelButtonText: 'Cancel',
        cancelButtonColor: 'Crimson',
        confirmButtonColor: "light-blue",
      }).then((result) => {
        if (result.isConfirmed) {
          // Si l'utilisateur clique sur "Oui", effectuez la suppression en utilisant une requête Ajax
          deleteTricount(tricountId);

        }

      });

    }

    async function deleteTricount() {
      let tricountId = <?= $tricount->getId() ?>;
      // Effectuez la requête Ajax pour supprimer le Tricount
      await $.post("tricount/delete_service_json", {
        tricountId
      });

      // Affichez une notification ou redirigez l'utilisateur vers une autre page si nécessaire
      Swal.fire({
        title: 'Deleted!',
        text: 'The Tricount has been deleted.',
        icon: 'success'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'tricount/index'; // Redirigez l'utilisateur vers la page spécifique
        }
      });
    }


    function leaveWithoutSave() {
      var formModified = false;

      // Listen for changes in form inputs
      $('#edit-tricount-form :input').on('input change', function() {
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




  <title>Edit Tricount</title>
</head>

<body>
  <form action="tricount/edit_tricount/<?= $tricount->getId() ?>" method="post" class="mb-5" id="edit-tricount-form">
    <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">

      <a href="tricount/view_tricount/<?= $tricount->getId() ?>" class="btn btn-outline-danger " id="backBtn">Back</a>
      <a class="navbar-brand" style="color :grey"><?= $tricount->getTitle() ?></a>

      <button type="submit" class="btn btn-outline-primary">Update</button>
    </nav>



    <div class="container mt-5">
      <h1 class="text-center">Settings</h1>
      <div class="row mt-5">
        <div class="col-md-6 mx-auto">
          <input type="hidden" name="tricount_id" value="<?php echo $tricount->getId(); ?>" />
          <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo $tricount->getTitle(); ?>" required />
            <div id="title-error"></div>
            <p id="title-validation"></p>
          </div>
          <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" id="description" name="description"><?php echo $tricount->getDescription(); ?></textarea>
            <div id="description-validation"></div>
            <div id="description-Error"></div>
          </div>
        </div>
      </div>
    </div>
  </form>





  <h5 class="d-flex justify-content-center">Subscribers:</h5>
  <ul id="subscriber-list" class="list-group d-flex justify-content-between align-items-center">
    <?php foreach ($subs as $subscriber) : ?>
      <li class="list-group-item d-flex justify-content-between align-items-center subscriber-item" style="width: 35%;">
        <?= $subscriber->getFullname() ?>
        <form id="delete-subscription-form-<?= $subscriber->getId() ?>" action="tricount/del_sub/<?= $subscriber->getId() ?>" method="post">
          <input type="hidden" name="tricount_id" value="<?= $tricount->getId() ?>" />
          <input type="hidden" name="user_id" value="<?= $subscriber->getId() ?>" />
          <input type="hidden" name="user_name" value="<?= $subscriber->getFullname() ?>" />
          <?php if ($canDelete[$subscriber->getId()] == false) : ?>
            <button type="submit" class="delete-sub" id="sub_to_delete_<?= $subscriber->getId() ?>">
              <i class="bi bi-trash-fill" style="font-size: 20px; color: red;"></i>

            </button>
            <input type="hidden" class name="user_name" value="<?= $subscriber->getFullname() ?>">
          <?php endif ?>
        </form>
      </li>
    <?php endforeach; ?>
  </ul>







  <div class="form-inline d-flex justify-content-center">
    <div class="row">
      <div class="col">
        <form class="form-inline add-sub-form" action="tricount/add_sub" method="post">
          <select class="custom-select my-1 mr-sm-2" name="new_sub" id="new_sub">
            <option selected disabled>-- Add another subscriber --</option>
            <?php foreach ($notsub as $sub) : ?>
              <option value="<?= $sub->getId() ?>"><?= $sub->getFullname() ?></option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="tricount_id" id="tricount_id" value="<?= $tricount->getId() ?>">
          <button type="submit" class="btn btn-outline-primary my-1" id="add-button">Add</button>
        </form>
      </div>
    </div>
  </div>

  <div class="form-inline d-flex justify-content-center" style="margin-top:15px; ;">
    <div class="row">
      <div class="col">
        <div class="row">
          <button type="submit" class="btn btn-outline-primary my-1">Manage repartition (not working for the moment)</button>
        </div>
        <div id="formContainer"></div>
        <div class="row">


          <input type="hidden" name="tricountId" value="<?= $tricount->getId() ?>">
          <a href="tricount/delete_tricount/<?= $tricount->getId() ?>" type="submit" class="btn btn-danger" style="width:100% ; margin-top: 5px;" onclick="confirmDelete()">
            Delete Tricount
          </a>


        </div>
      </div>
    </div>
  </div>



  </div>
  <div>
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
</body>

</html>