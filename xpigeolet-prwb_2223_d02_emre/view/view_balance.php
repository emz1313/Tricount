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
</head>

<body>
  <nav class="navbar navbar-light" style="background-color:#e3f2fd;">
    <a href="tricount/view_tricount/<?= $tricount->getId() ?>" class="btn btn-outline-danger ">Back</a>
    <div>
      <?= $tricount->getTitle() ?><i class="bi bi-caret-right"></i>Balance
    </div>
  </nav>

  <div class="container ">


    <?php
    $max = 0;
    foreach ($list as $item) {
      $max = max($max, abs($item['balance']));
    }
    ?>

    <div class="d-flex flex-column ">
      <?php foreach ($list as $item) : ?>
        <div class="d-flex my-3 <?= $item['balance'] < 0 ? "flex-row-reverse" : "" ?>">
          <div class="text-center mr-3 <?= $item['balance'] < 0 ? "mr-0" : "ml-0" ?>">
            <?= $item['user']->getFullname() ?>
          </div>

          <div class="progress" style="width: 50%;">
            <div class="progress-bar <?= $item['balance'] >= 0 ? "bg-success" : "bg-danger" ?>" role="progressbar" style="width: <?= abs($item['balance']) / $max * 100 ?>%">
            </div>
          </div>
          <div class="text-center ml-3 <?= $item['balance'] < 0 ? "ml-0" : "mr-0" ?>">
            <?= number_format($item['balance'], 2) ?>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>
</body>




</html>