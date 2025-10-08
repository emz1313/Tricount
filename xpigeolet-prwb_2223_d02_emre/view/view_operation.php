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

  <title>Operation</title>
</head>

<body>

  <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">
    <div class="container-fluid">
      <a href="tricount/view_tricount/<?= $tricount->getId() ?>" class="btn btn-outline-danger">Back</a>
      <a class="navbar-brand text-muted">
        <?= $tricount->getTitle() ?>
        <i class="bi bi-caret-right"></i>
        <?= $operation->getTitle() ?>
      </a>
      <form action="operation/edit_operation/<?= $tricount->getId() ?>/<?= $operation->getId() ?>" method="post">
        <input type="submit" class="btn btn-outline-primary" id="submit" value="EDIT">
      </form>
    </div>
  </nav>

  <div class="container">
    <div class="card border-0 shadow my-5">
      <div class="card-body p-5">
        <h3 class="text-center mb-4">Operation Details</h3>
        <p class="text-center mb-4">Paid by <?= $operation->getInitiator()->getFullname() ?></p>
        <p class="text-center mb-4">Amount: <?= number_format($operation->getAmount(), 2) ?></p>
        <p class="text-center mb-4">Date: <?= $operation->getOperationDate() ?></p>
        <p class="text-center mb-4">For <?= $nb_user_in_operation ?> participants,
          <?php foreach ($users_weights as $u) : ?>
            <?php if ($u[0]->getId() == $user_connected->getId()) {
              echo "<b>including me</b>";
            }
            ?>
          <?php endforeach; ?>
        </p>
        <table class="table table-striped table-bordered table-responsive text-center mb-4">
          <thead>
            <tr>
              <th>Name</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users_weights as $user) : ?>
              <tr>
                <?php
                if ($user[0]->getFullname() == $user_connected->getFullname()) {
                  echo '<td><b>' . $user[0]->getFullname() . '(me)</b></td>';
                } else echo '<td>' . $user[0]->getFullname() . '</td>'
                ?>

                <td><?= number_format(($operation->getAmount() * $user[1]) / $total_weight, 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>



  <footer class="text-center py-4" style="background-color:#e3f2fd;">
    <div class="container">
      <div class="row">
        <div class="col-12 col-md-6 text-md-start mb-3 mb-md-0">
          <?php
          $currentIndex = array_search($operation->getId(), $array_of_id_operations);
          if ($currentIndex > 0) {
            $previousIndex = $currentIndex - 1;
            $previousOperationId = $array_of_id_operations[$previousIndex];
            echo "<a href='operation/view_operation/{$tricount->getId()}/$previousOperationId' class='btn btn-primary'>Previous</a>";
          }
          ?>
        </div>
        <div class="col-12 col-md-6 text-md-end">
          <?php
          $currentIndex = array_search($operation->getId(), $array_of_id_operations);
          if ($currentIndex < count($array_of_id_operations) - 1) {
            $nextIndex = $currentIndex + 1;
            $nextOperationId = $array_of_id_operations[$nextIndex];
            echo "<a href='operation/view_operation/{$tricount->getId()}/{$nextOperationId}' class='btn btn-primary'>Next</a>";
          }
          ?>
        </div>
      </div>
    </div>
  </footer>

</body>

</html>