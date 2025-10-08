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


    <title>Settings</title>


</head>

<body>




    <nav class="navbar navbar-light " style="background-color:#e3f2fd ;">

        <a href="tricount/index" class="btn btn-outline-danger ">Back</a>

        <a class="navbar-brand" style="color :grey">Settings</a>

    </nav>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <br>
                <p class="h3">Hey <b style="color:grey ;"> <?= $user->getFullName() ?></b>!</p>
                <p class="h4">I know your email address is <span class="text-danger"> <?= $user->getEmail() ?></span></p>
                <p class="h4">What can i do for you?</p>
                <br>
                <div class="d-flex flex-column align-items-center w-100 ">
                    <a href="user/edit_profile/<?= $user->getId() ?>" class="btn btn-primary mb-3 w-100">Edit Profile</a>
                    <a href="user/edit_password/" class="btn btn-primary mb-3 w-100">Change Password</a>
                    <form action="main/logout" method="post" class="w-100">
                        <input type="text" hidden name="logout" id="logout">
                        <button type="submit" class="btn btn-danger w-100">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


</body>

</html>