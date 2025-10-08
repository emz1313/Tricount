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

    <title>Document</title>
    <style>
        footer#sticky-footer {
            background: #A3E5FF;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">
        <a href="tricount/index" class="btn btn-outline-danger">Back</a>
        <a class="navbar-brand" style="color :grey"><?= $tricount->getTitle() ?></a>
        <form class="form-inline" action="tricount/edit_tricount/<?= $tricount->getId() ?>" method="POST">
            <div class="add">
                <input type="submit" class="btn btn-outline-primary btn-sm" id="submit" value="Edit">
            </div>
        </form>
    </nav>
    <div class="container d-flex flex-column">


        <br>
        <div class="text-center mt-5 flex-grow-1">
            <h1>Your tricount is empty!</h1>
            <p>Click bellow to add your first expense!</p>
            <a href="operation/add_operation/<?= $tricount->getId() ?>" class="btn btn-primary">Add an expense</a>
        </div>
        <br>

    </div>

</body>
<footer id="sticky-footer" class="flex-shrink-0 py-4  text-grey" style="background-color:#e3f2fd">
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

</html>