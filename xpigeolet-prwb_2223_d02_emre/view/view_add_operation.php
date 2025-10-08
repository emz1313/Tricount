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
    <script src="lib/jquery-3.6.3.min.js" type="text/javascript"></script>
    <script src="lib/sweetalert2@11.js" type="text/javascript"></script>
    <script src="lib/just-validate-4.2.0.production.min.js" type="text/javascript"></script>
    <script src="lib/just-validate-plugin-date-1.2.0.production.min.js" type="text/javascript"></script>
    <style>
        .is-invalid {
            background-color: #FEEBEB;
            border: 1px solid #DC3545;
        }
    </style>


    <script>
        let justValidateActive = <?= Configuration::get("justValidate"); ?>;

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
            leaveWithoutSave();
            if (justValidateActive) {
                validationFormWithJustValidate();
            }

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

            $('input[type="checkbox"]').on('change', function() {

                // Get the value of the user ID associated with the checkbox
                let userId = $(this).val();

                // If the checkbox is unchecked, set the weight value to empty and update the amount to pay to 0
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

        function leaveWithoutSave() {
            var formModified = false;

            // Listen for changes in form inputs
            $('form :input').on('input change', function() {
                formModified = true;
            });

            // Show SweetAlert2 confirmation modal when clicking on Back button or Submit button
            $('#backBtn').on('click', function(event) {
                if (formModified) {
                    event.preventDefault();

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
                }
            });
        }

        function validationFormWithJustValidate() {
            const validation = new JustValidate('#add-operation-form', {
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
                    successMessage: 'Vous avez entréz une bonne valeur !'
                })
                .addField('#operation_date', [{
                        rule: 'required',
                        errorMessage: 'Veuillez sélectionner une date après : <?= $tricount->getCreated_at()->format('d-m-Y') ?>'
                    },
                    {
                        plugin: JustValidatePluginDate((value) => ({
                            isAfterOrEqual: document.querySelector('#tricount_date').value,
                        })),
                        errorMessage: 'Veuillez sélectionner une date après : <?= $tricount->getCreated_at()->format('d-m-Y') ?>'
                    }
                ], {
                    successMessage: 'La date est valide !'
                })

            <?php foreach ($users as $user) :
                $userId = $user->getId();
            ?>
                validation.addRequiredGroup('#repartitions', 'Select at lease one checkbox!', {
                        successMessage: 'Everything looks good',
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
    <title>Add</title>
</head>

<body>
    <form method="post" action="operation/add_operation/<?= $tricount->getId() ?>" id="add-operation-form">
        <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">
            <a href="tricount/view_tricount/<?= $tricount->getId() ?>" class="btn btn-outline-danger " id="backBtn">Back</a>
            <button type="submit" class="btn btn-outline-primary">Add </button>
        </nav>

        <div class="container">


            <span>Titre de l'opération
                <input type="text" name="title" id="title" class="form-control" value="<?= $title ?>">
                <?php if (!empty($errorsTitle)) : ?>
                    <div class="text-danger">
                        <?= $errorsTitle[0] ?>
                    </div>
                <?php endif; ?>
            </span>
            <br>
            <label>Montant de l'opération</label>
            <div class="input-group mb-3">

                <input type="number" value="<?= $amount ?>" name="amount" id="amount" min="0" step="any" class="form-control">
                <div class="input-group-append">
                    <span class="input-group-text">EUR</span>
                </div>
            </div>
            <?php if (!empty($errorsAmount)) : ?>
                <div class="text-danger"><?= $errorsAmount[0] ?></div>
            <?php endif; ?>
            <br>

            <label>Initiateur de l'opération</label>
            <select class="form-control" name="initiator" id="initator_select">
                <?php foreach ($users as $u) : ?>

                    <option value="<?= $u->getId() ?>"><?= $u->getFullname() ?></option>
                <?php endforeach; ?>
            </select>

            <input type="hidden" name="created_at" value="<?php echo date('Y-m-d H:i:s'); ?>">

            <br>
            <label>La date de votre opération
                <input type="date" name="operation_date" id="operation_date" value="<?= $operation_date ?>">
                <input type="date" hidden name="tricount_date" value="<?= $tricount->getCreated_at()->format('Y-m-d') ?>" id="tricount_date">
            </label>
            <?php if (!empty($errorsDate)) : ?>
                <div class="text-danger"><?= $errorsDate[0] ?></div>
            <?php endif; ?>

            <br>

            <br>
            <label>For whom? (Select at least one)</label>
            <div id="repartitions" style="margin-top: 25px;">
                <?php foreach ($users as $user) :
                    $userId = $user->getId();
                ?>
                    <input type="checkbox" name="user[]" value="<?= $userId ?>" id="user_<?= $userId ?>" <?= isset($_POST['weight'][$userId]) && $_POST['weight'][$userId] > 0 ? 'checked' : '' ?>>
                    <label for="user_<?= $userId ?>"><?= $user->getFullname() ?></label>
                    <span id="amount_to_pay_<?= $userId ?>" class="input-group-text" style="display:none;margin-bottom: 30px;"></span>
                    <input type="number" name="weight[<?= $userId ?>]" min="0" id="weight_<?= $userId ?>" value="<?= isset($_POST['weight'][$userId]) ? $_POST['weight'][$userId] : '' ?>" data-user-id="<?= $userId ?>" oninput="updateAmounts(); updateCheckbox(<?= $userId ?>);" class="form-control">

                    <br> <!-- Ajout d'un double saut de ligne pour l'espace entre chaque case à cocher -->

                <?php endforeach; ?>
                <?php if (!empty($errorRepartition)) : ?>
                    <div class="text-danger"><?= $errorRepartition[0] ?></div>
                <?php endif; ?>
            </div>



        </div>

    </form>
</body>

</html>