<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <base href="<?= $web_root ?>" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <script src="https://kit.fontawesome.com/981b5a6232.js" crossorigin="anonymous"></script>
  <title>Delete Tricount</title>
</head>

<body>
  <div class="container mt-5">
    <h1 class="text-center">Are you sure?</h1>
    <p class="text-center">Do you really want to delete tricount "<?= $tricount->getTitle() ?>" and all of its dependencies? This process cannot be undone.</p>
    <div class="d-flex justify-content-center">
      <form method="POST" action="tricount/delete_service/">
        <button class="btn btn-danger mr-3" name="confirm_delete" value="<?= $tricount->getId() ?>">Delete</button>
      </form>
      <a href="tricount/edit_tricount/<?= $tricount->getId() ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </div>
</body>

</html>