<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <base href="<?= $web_root ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="lib/sweetalert2@11.js" type="text/javascript"></script>
    <script src="lib/jquery-3.6.3.min.js" type="text/javascript"></script>
    <script src="lib/just-validate-4.2.0.production.min.js" type="text/javascript"></script>
    <script src="lib/just-validate-plugin-date-1.2.0.production.min.js" type="text/javascript"></script>
    <script>
        let tricountID = <?= $tricount->getId() ?>;
        $(document).ready(function() {

            // Show the "amount to pay" element for each weight input
            $('input[name^="weight"]').each(function() {
                let userId = $(this).attr('data-user-id');
                $('#amount_to_pay_' + userId).css('display', 'inline');
            });

            // Listen for changes to the weight inputs
            $('input[name^="weight"]').on('input', function() {
                updateAmounts();
            });

            // Listen for changes to the total amount input
            $('#amount').change(function() {
                updateAmounts();
            });

            // Initialize the amounts
            updateAmounts();
            let justValidateActive = <?= Configuration::get("justValidate"); ?>;
            $(document).ready(function() {
                if (justValidateActive) {
                    console.log(justValidateActive);
                    validationFormWithJustValidate();
                }
            });
        });




        function updateCheckbox(userId) {
            let weight = parseFloat($("#weight_" + userId).val());
            let checkbox = $("#user_" + userId);

            if (weight > 0) checkbox.prop("checked", true);
            else checkbox.prop("checked", false);
        }


        function updateAmounts() {
            // Get the total amount entered by the user
            let totalAmount = parseFloat($('#amount').val());

            // Initialize the total weight to zero
            let totalWeight = 0;

            // Loop through all weight inputs and add up the total weight
            $('input[name^="weight"]').each(function() {
                // Get the weight value of this input
                let weight = parseFloat($(this).val());

                // Check if the corresponding checkbox is checked
                let checkbox = $('input[type="checkbox"][value="' + $(this).attr('data-user-id') + '"]');
                if (checkbox.prop('checked')) {
                    // Add the weight to the total weight only if the checkbox is checked
                    if (!isNaN(weight)) {
                        totalWeight += weight;
                    }
                }
            });

            // Listen for changes to any checkbox element
            $('input[type="checkbox"]').on('change', function() {

                // Get the value of the user ID associated with the checkbox
                let userId = $(this).val();

                // If the checkbox is unchecked, set the weight value to 0 and update the amount to pay to 0
                if (!$(this).is(':checked')) {
                    $('#weight_' + userId).val('');
                    $('#amount_to_pay_' + userId).text('amount : ' + 0 + " euros ");
                } else {
                    $('#weight_' + userId).val(1);
                }

                // Update the amounts
                updateAmounts();
            });


            // Loop through all weight inputs again and update the amount to pay for each user
            $('input[name^="weight"]').each(function() {
                // Get the weight value of this input
                let weight = parseFloat($(this).val());

                // Check if the corresponding checkbox is checked
                let checkbox = $('input[type="checkbox"][value="' + $(this).attr('data-user-id') + '"]');
                if (checkbox.prop('checked')) {
                    if (!isNaN(weight)) {
                        // Calculate the amount to pay for this user based on their weight and the total weight
                        let amount = totalAmount * weight / totalWeight;

                        // Get the user ID from the data attribute
                        let userId = $(this).attr('data-user-id');

                        // Update the "amount to pay" element for this user with the calculated amount
                        $('#amount_to_pay_' + userId).text('amount : ' + amount.toFixed(2) + ' euros  ');
                    }
                } else {
                    // If the checkbox is unchecked, set the amount to pay to zero
                    let userId = $(this).attr('data-user-id');
                    $('#amount_to_pay_' + userId).text('amount : ' + 0 + " euros ");
                }
            });

            // Update the hidden "amount to pay" input with the total amount entered by the user
            $('#amount_to_pay').val(totalAmount);
        }

        $(document).ready(function() {
            let id_op = <?= $operation->getId() ?>;
            var formModified = false;

            // Listen for changes in form inputs
            $('form.edit-operation-form :input').on('input change', function() {
                formModified = true;
            });

            // Listen for the "Cancel" button click
            $('#cancelBtn').on('click', function(event) {
                if (formModified) {
                    event.preventDefault(); // Prevent navigation

                    // Show the confirmation modal
                    Swal.fire({
                        title: 'Unsaved changes!',
                        html: 'You have unsaved changes. <br> Are you sure you want to leave without saving?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#FF0000',
                        confirmButtonText: 'Leave without saving',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = $(this).attr('href');
                        }
                    });
                }
            });

            $('#delete_operation').on('click', async function(event) {
                event.preventDefault();
                try {


                    // Afficher la boîte de dialogue de confirmation de suppression
                    const confirmResult = await Swal.fire({
                        title: 'Confirmation',
                        html: 'Are you sure you want to delete the operation : <b><?= $operation->getTitle() ?></b> and all of its dependencies ? <br> This proccess cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Delete it!',
                        confirmButtonColor: 'cornflowerblue',
                        cancelButtonText: 'Cancel',
                        cancelButtonColor: 'crimson',
                        reverseButtons: true
                    });

                    if (confirmResult.isConfirmed) {

                        // Supprimer l'opération
                        await $.post("operation/delete_service_json", {
                            id_op
                        });

                        console.log("Operation deleted successfully.");

                        // Afficher une boîte de dialogue de suppression confirmée
                        Swal.fire('Deleted!', 'The Operation has been deleted successfully.', 'success')
                            .then(() => {
                                // Rediriger vers tricount/index
                                window.location.href = "tricount/view_tricount/" + tricountID;
                            });
                    }
                } catch (e) {
                    // Gérer l'erreur de suppression
                    Swal.fire('Error', 'An error occurred while deleting the operation.', 'error');
                }
            });

        });

        function validationFormWithJustValidate() {
            const validation = new JustValidate('#edit-operation-form', {
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
                        errorMessage: 'Le champ est obligatoire'
                    },
                    {
                        rule: 'minLength',
                        value: 3,
                        errorMessage: 'Minimum 3 caractères'
                    },
                    {
                        rule: 'maxLength',
                        value: 256,
                        errorMessage: 'Maximum 256 caractères'
                    }
                ], {
                    successMessage: 'Valide !'
                })
                .addField('#amount', [{
                        rule: 'required',
                        errorMessage: 'Le champ est obligatoire'
                    },
                    {
                        rule: 'minNumber',
                        value: 0.01,
                        errorMessage: 'Minimum 0.01 euro'
                    }
                ], {
                    successMessage: 'Valide !'
                })
                .addField('#operation_date', [{
                        rule: 'required',
                        errorMessage: 'Veuillez sélectionner une date après : <?= $tricount->getCreated_at()->format('d-m-Y') ?>'
                    },
                    {
                        plugin: JustValidatePluginDate((value) => ({
                            isAfterOrEqual: $('#tricount_date').val(),
                        })),
                        errorMessage: 'Veuillez sélectionner une date après : <?= $tricount->getCreated_at()->format('d-m-Y') ?>'
                    }
                ], {
                    successMessage: 'Valide !'
                })

            <?php foreach ($users as $user) :
                $userId = $user->getId();
            ?>
                    .addRequiredGroup('#repartitions', 'Veuillez sélectionner une checkbox', {
                        tooltip: {
                            position: 'bottom'
                        },
                        successMessage: "Vous avez bien une checkbox de coché"
                    })

                    .addField('#weight_<?= $userId ?>', [

                        {
                            rule: 'minNumber',
                            value: 1,
                            errorMessage: 'Le poids doit être supérieur ou égal à 1'
                        }
                    ], {

                    })



            <?php endforeach; ?>

                .onSuccess(function(event) {
                    event.preventDefault();
                    event.target.submit();
                });

            $("input:text:first").focus();
        }
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <script src="https://kit.fontawesome.com/981b5a6232.js" crossorigin="anonymous"></script>
    <title>Edit Operation : <?= $user->getFullname() ?></title>
</head>

<body>


    <form action="operation/edit_operation/<?= $tricount->getId() . '/' . $operation->getId() ?>" method="post" id="edit-operation-form" class="edit-operation-form">

        <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">
            <a href="operation/view_operation/<?= $tricount->getId() ?>/<?= $operation->getId() ?>" class="btn btn-outline-danger " id="cancelBtn">
                Cancel
            </a>
            <button class="btn btn-outline-primary" type="submit">EDIT</button>
        </nav>

        <div class="container">
            <form method="post">
                <h1 class="text-center">You are Editing "<?= $operation->getTitle() ?>"</h1>
                <p>
                    <input id="title" type="text" name="title" class="form-control" value="<?= $operation->getTitle() ?>">
                </p>
                <p>
                <div class="input-group mb-3">
                    <input type="number" name="amount" id="amount" min="0" step="any" class="form-control" value="<?= $operation->getAmount() ?>">
                    <div class="input-group-append">
                        <span class="input-group-text">EUR</span>
                    </div>
                </div>
                </p>
                <p>
                    <label for="operation_date">La date de l'opération est le : </label>
                    <input type="date" name="operation_date" id="operation_date" class="form-control" value="<?= $operation->getOperationDate() ?>">
                    <input type="date" hidden name="tricount_date" value="<?= $tricount->getCreated_at()->format('Y-m-d') ?>" id="tricount_date">
                </p>
                <p>
                    <label>Paid by </label>
                </p>
                <select class="form-control" name="initiator">
                    <?php foreach ($users as $u) : ?>
                        <option value="<?= $u->getId() ?>" <?= $u->getId() == $operation->getInitiator()->getId() ? "selected" : "" ?>><?= $u->getFullname() ?></option>
                    <?php endforeach; ?>
                </select>

                <br>
                <label style="color:black;">For whom? (Select at least one)</label>
                <div id="repartitions">
                    <br>

                    <?php foreach ($users as $user) :
                        $userId = $user->getId();
                        $oldWeight = isset($user_weight[$userId]) ? $user_weight[$userId] : '';
                        $checked = $oldWeight > 0;
                    ?>
                        <input type="checkbox" name="user[]" value="<?= $userId ?>" id="user_<?= $userId ?>" <?= $checked ? 'checked' : '' ?>>
                        <label for="user_<?= $userId ?>" style="margin-left: 10px;"><?= $user->getFullname() ?></label>

                        <span id="amount_to_pay_<?= $userId ?>" class="input-group-text" style="display:none;"></span>

                        <input type="number" name="weight[<?= $userId ?>]" min="0" id="weight_<?= $userId ?>" value="<?= $oldWeight ?>" data-user-id="<?= $userId ?>" oninput="updateAmounts(); updateCheckbox(<?= $userId ?>);" class="form-control">


                        <br>
                    <?php endforeach; ?>
                </div>

            </form>
        </div>
    </form>
    <br>
    <div class="d-flex justify-content-center" id="delete-operation">
        <a id="delete_operation" href="operation/delete_operation/<?= $tricount->getId() ?>/<?= $operation->getId() ?>" class="btn btn-outline-primary">Delete this operation</a>
    </div>





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

</body>

</html>