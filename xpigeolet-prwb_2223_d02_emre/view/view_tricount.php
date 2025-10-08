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
    <title> <?php echo $tricount->getTitle() ?> </title>

    <script>
        $(document).ready(function() {
            $('.py-3').after('<div>\
      <label for="sort-select">Trier par:</label>\
      <select id="sort-select">\
         <option value="amount-asc">Montant (croissant)</option>\
         <option value="amount-desc">Montant (décroissant)</option>\
         <option value="date-asc">Date (croissante)</option>\
         <option value="date-desc">Date (décroissante)</option>\
         <option value="initiator-asc">Nom d\'initiateur(croissante)</option>\
         <option value="initiator-desc">Nom d\'initiateur(décroissante)</option>\
         <option value="title-asc">Titre (croissante)</option>\
         <option value="title-desc">Titre(décroissante)</option>\
      </select>\
   </div>');

            // Attach a click event listener to all elements with the 'card' class
            // within the element with the 'card-deck' class
            $('.card-deck').on('click', '.card', function() {
                // Extract the Tricount ID and Operation ID from the 'data-' attributes
                // on the clicked element using jQuery's 'data' method
                let tricountId = $(this).data('tricount-id');
                let opId = $(this).data('operation-id');

                // Construct the URL for the target page using the extracted IDs
                let url = 'operation/view_operation/' + tricountId + '/' + opId;

                // Redirect the browser to the target page
                window.location.href = url;
            });



        });

        $(document).ready(function() {
            // Intercepte le changement de l'élément de sélection de tri
            $('#sort-select').on('change', function() {
                let sortType = $(this).val();
                let sortedList = $('.card-deck .card').sort(function(a, b) {
                    let aValue, bValue;
                    if (sortType === 'amount-asc' || sortType === 'amount-desc') {
                        // Tri par montant
                        aValue = parseFloat($(a).find('.card-body .col-4 .card-title').text().replace(',', '.'));
                        bValue = parseFloat($(b).find('.card-body .col-4 .card-title').text().replace(',', '.'));
                    } else if (sortType === 'date-asc' || sortType === 'date-desc') {
                        // Tri par date
                        aValue = new Date($(a).find('.card-body .col-4 p').text());
                        bValue = new Date($(b).find('.card-body .col-4 p').text());
                    } else if (sortType === 'initiator-asc' || sortType === 'initiator-desc') {
                        // Tri par nom d'initiateur
                        aValue = $(a).find('.card-body .col-8 p').text();
                        bValue = $(b).find('.card-body .col-8 p').text();
                    } else if (sortType === 'title-asc' || sortType === 'title_desc') {
                        // Tri par titre
                        aValue = $(a).find('.card-body .col-8 .card-title').text().toLocaleLowerCase();
                        bValue = $(b).find('.card-body .col-8 .card-title').text().toLocaleLowerCase();
                    }
                    if (sortType === 'amount-asc' || sortType === 'date-asc' || sortType === 'initiator-asc' || sortType === 'title-asc') {
                        return aValue > bValue ? 1 : -1;
                    } else {
                        return aValue < bValue ? 1 : -1;
                    }
                });
                $('.card-deck').empty().append(sortedList);
            });
        });
    </script>
</head>

<body>
    <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">
        <a href="tricount/index" class="btn btn-outline-danger ">Back</a>
        <a class="navbar-brand" style="color :grey"><?= $tricount->getTitle() ?></a>
        <form class="form-inline" action="tricount/edit_tricount/<?= $tricount->getId() ?>" method="POST">
            <div class="add">
                <input type="submit" class="btn btn-outline-primary btn-sm" id="submit" value="Edit">
            </div>
        </form>
    </nav>
    <div class="container">


        <header class="py-3 mb-4 border-bottom" style="background-color:#6AFFAB ;">
            <div class="container d-flex flex-wrap justify-content-center">
                <a href="tricount/view_balance/<?= $tricount->getId() ?>" style="color:white; text-decoration: none;">
                    <span class="fs-4">
                        <i class="bi bi-arrow-left-right">View balance</i>
                    </span>
                </a>
            </div>
        </header>

        <div class="card-deck " style="width:80%; margin:auto">
            <?php foreach ($operations as $op) : ?>
                <a href="operation/view_operation/<?= $op->getTricount()->getId() ?>/<?= $op->getId() ?>" style="text-decoration: none; color: black;">
                    <div class="card" data-tricount-id="<?= $op->getTricount()->getId() ?>" data-operation-id="<?= $op->getId() ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <h5 class="card-title"> <?= $op->getTitle() ?> </h5>
                                    <p class="card-text" style="text-align:left">Paid by <?= $op->getInitiator()->getFullname() ?> </p>
                                </div>
                                <div class="col-4" style="text-align:right ;">
                                    <h5 class="card-title">
                                        <?php
                                        if (strpos($op->getAmount(), '.') !== false) {
                                            // Il y a une virgule
                                            echo  number_format($op->getAmount(), 2, '.', '');
                                        } else {
                                            // Il n'y a pas de virgule
                                            echo $op->getAmount();
                                        }
                                        ?>
                                    </h5>
                                    <p class="card-text"> <?= $op->getOperationDate() ?> </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="flex-shrink-0 py-4  text-grey" style="background-color:#e3f2fd">
        <div class="container text-center">
            <div class="row">
                <div class="col">
                    MY TOTAL
                    <?= number_format($total, 1) ?>
                    <?php if ($total > 0)
                        echo '€';
                    ?>
                </div>
                <div class="col">
                    <a href="operation/add_operation/<?= $tricount->getId() ?>">
                        <i class="bi bi-plus" style="font-size: 2rem; color: rgb(0, 73, 97);"></i>
                    </a>
                </div>
                <div class="col">
                    TOTAL EXPENSES
                    <?= number_format($expense, 2) ?>
                    <?php if ($expense > 0)
                        echo '€';
                    ?>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>