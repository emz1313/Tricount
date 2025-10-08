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
  <title>Delete operation <?= $operation->getTitle() ?></title>
</head>

<body>
  <div class="d-flex justify-content-center">
    <div class="text-center">
      <h1>Delete Operation : <?= $operation->getTitle() ?></h1>
      <br>
      <p>Are you sure you want to delete the operation "<?= $operation->getTitle() ?>"?</p>
      <form method="POST" action="operation/delete_service/<?= $tricount->getId() ?>/<?= $operation->getId() ?>">
        <button class="btn btn-primary" name="confirm_delete" value=<?= $operation->getId() ?>>Confirm</button>
      </form>
      <a href="operation/view_operation/<?= $tricount->getId() . '/' . $operation->getId() ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </div>
</body>

</html>