<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>LOGIN</title>
    <base href="<?= $web_root ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <script src="https://kit.fontawesome.com/981b5a6232.js" crossorigin="anonymous"></script>



</head>

<body>
    <nav class="navbar navbar-light " style="background-color:#3075FF ;">
        <a class="navbar-brand" style="color :white"><i class="fa-solid fa-cat"></i>Tricount</a>
    </nav>
    <div class="container">
        <div class="row justify-content-center ">
            <div class="col-md-8">
                <form class="form-center" action="main/login" method="post">
                    <h2 class="text-center">Sign in</h2>

                    <div class="input-group w-100">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" id="email" name="email" class="form-control" placeholder="Your email" aria-label="Input group example" aria-describedby="basic-addon1">
                    </div>
                    <div class="input-group w-100">
                        <span class="input-group-text">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" class="form-control" placeholder="your password" aria-label="Input group example" aria-describedby="basic-addon1">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block w-100">Login</button>
                    </div>
                    <p class="text-center">
                        <a href="main/signup">New Here ? Click Here to join the party <i class="fa-solid fa-party-horn"></i></a>!
                    </p>
                </form>
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

            </div>
        </div>
    </div>


</body>

</html>