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



    <title>the list of <?= $user->getFullname() ?> tricounts</title>

</head>

<body>
    <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">
        <a class="navbar-brand" style="color :grey">Your tricounts</a>
        <a href="tricount/add_tricount" class="btn btn-outline-primary ">Add</a>
    </nav>
    <div class="container">

        <?php foreach ($listeTricounts as $tricount) : ?>

            <ul class="list-group list-group-light list-group-small">
                <li class="list-group-item">
                    <a style="text-decoration: none; color: black;background-color: transparent;" href="tricount/view_tricount/<?= $tricount->getId() ?>">
                        <div><?= $tricount->getTitle() ?>
                            <div style="text-align:right ;">
                                <?php
                                $nbUser = $tricount->getSubs($tricount->getId());
                                if ($nbUser == "1") {
                                    echo "<small>you're alone</small>";
                                }
                                if ($nbUser == "2") {
                                    echo "<small>with 1 friend </small>";
                                } else if ($nbUser > "2") echo "<small> with ", $nbUser - 1, " friends </small>";
                                ?>
                            </div>
                        </div>
                        <?php
                        if ($tricount->getDescription() !== '') {
                            echo "<small>", ($tricount->getDescription()), "</small>";
                        } else {
                            echo "<small>No description</small>";
                        }
                        ?>
                    </a>
                </li>
            </ul>
        <?php endforeach; ?>
        <div class="set d-flex justify-content-end">
            <a href="user/settings">
                <i class="bi bi-gear" style="font-size: 2rem; color:blue;"></i>
            </a>
        </div>
    </div>



</body>

</html>