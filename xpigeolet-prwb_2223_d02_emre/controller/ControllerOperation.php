<?php
require_once 'model/Tricount.php';
require_once 'model/User.php';
require_once 'model/Subscription.php';
require_once 'model/Operation.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'model/Repartition.php';
require_once 'model/Repartition_template.php';

class ControllerOperation extends Controller
{

    public function index(): void
    {
        $this->view_operation();
    }

    public function view_operation(): void
    {
        $errors = [];
        // give the current user 
        $user_connected = $this->get_user_or_redirect();
        if (isset($_GET['param1']) && isset($_GET['param2'])) {

            // give the actual tricount 
            $tricount = Tricount::get_tricount_by_id($_GET['param1']);
            if ($tricount == null) {
                $this->redirect("tricount", "index");
            }

            // get all id operations of the tricount 
            $list_operations = Operation::get_operation_by_tricount($tricount->getId());
            $array_of_id_operations = [];
            foreach ($list_operations as $operation) {
                $array_of_id_operations[] = $operation->getId();
            }
            // give the actual operation
            $operation = Operation::get_operation_by_id($_GET['param2']);

            if ($operation == null or $user_connected->isSub($user_connected->getId(), $tricount->getId()) == null) {
                $this->redirect("tricount", "index");
            }


            // give the number of users in an operation 
            $nb_user_in_operation = Repartition::get_number_of_user_by_operation($operation->getId());
            // give users of an operation 
            $users = Repartition::get_users_from_an_operation($operation->getId());
            // give an array of users and their weight in the actual operation 
            $users_weights = Repartition::get_users_and_weights_by_operation($operation->getId());
            $total_weight = 0;
            foreach ($users_weights as $user) {

                $total_weight += $user[1];
            }

            (new View("operation"))->show(array(
                "user_connected" => $user_connected,
                "users" => $users,
                "tricount" => $tricount,
                "operation" => $operation,
                "nb_user_in_operation" => $nb_user_in_operation,
                "users_weights" => $users_weights,
                "total_weight" => $total_weight,
                "array_of_id_operations" => $array_of_id_operations,
                "errors" => $errors
            ));
        }
    }
    public function add_operation(): void
    {
        // Récupération de l'utilisateur connecté
        $connected_user = $this->get_user_or_redirect();

        if (isset($_GET["param1"])) {
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            if ($tricount == false) {
                $this->redirect("tricount", "index");
            }

            $sub_or_not = $connected_user->isSub($connected_user->getId(), $tricount->getId());
            // si l'utilisateur connecté est abonné au tricount, il a accès 
            if ($sub_or_not) {
                // Récupération des informations relatives au tricount

                $users_in_tricount = Tricount::get_all_sub_to_a_tricount($tricount->getId());
                $repartitons = Repartition_template::get_all_repartition_template_of_the_tricount($tricount->getId());

                // Initialisation du tableau d'erreurs
                $errors = [];
                $errorsTitle = [];
                $errorsAmount = [];
                $errorsOperation_date = [];
                $errorRepartition = [];
                $title = '';
                $amount = '';
                $operation_date = '';
                // Vérification de la soumission du formulaire d'ajout d'opération
                if (
                    isset($_POST["title"]) && isset($_POST["amount"]) && isset($_POST["initiator"])
                    && isset($_POST["created_at"]) && isset($_POST['operation_date'])
                ) {
                    // Récupération des données du formulaire
                    $title = $_POST["title"];
                    $amount = $_POST["amount"];
                    $initiator = $_POST["initiator"];
                    $created_at = $_POST['created_at'];
                    $operation_date = $_POST['operation_date'];

                    /*
                     * Si pas d'erreurs, ajout de l'opération en base de données
                     */
                    $operation = new Operation(
                        $title,
                        $tricount,
                        $amount,
                        $operation_date,
                        $initiator,
                        $created_at
                    );

                    $errorsTitle = $operation->validate_title();
                    $errorsAmount = $operation->validate_amount();
                    $errorsOperation_date = $operation->validate_dates();
                    $errors = array_merge($errorsTitle, $errorsAmount, $errorsOperation_date);
                    $errorRepartition = [];



                    //$errors = $operation->validate();
                    if (isset($_POST['user']) && isset($_POST['weight'])) {
                        // Récupération des utilisateurs sélectionnés pour la répartition
                        $users = $_POST['user'];
                        $weights = $_POST['weight'];
                        $new_repartition = $_POST['weight'];
                        $has_repartition = false;
                        foreach ($users as $id) {

                            $weight = $weights[$id];
                            if ($weight > 0) {
                                $has_repartition = true;
                            }
                        }
                        if ($has_repartition) {
                            // Ajout de l'opération en base de données
                            if (empty($errors)) {
                                $operation->add();
                                // Ajout des répartitions
                                foreach ($new_repartition as $user_id => $new_weight) {
                                    // Vérifie si le poids est supérieur à zéro et si l'utilisateur est sélectionné
                                    if ($new_weight > 0 && in_array($user_id, $_POST['user'])) {
                                        $r = new Repartition($operation->getId(), $user_id, $new_weight);
                                        $r->add();
                                    }
                                }
                                // Redirection
                                $this->redirect("tricount", "view_tricount", $tricount->getId());
                            }
                        }

                        $errorRepartition[] = "There is an error with the repartitions";
                    }
                }
            }
        }
        // Affichage de la vue
        (new View("add_operation"))->show(array(
            "user" => $connected_user,
            "tricount" => $tricount,
            "users" => $users_in_tricount,
            "repartitions" => $repartitons,
            "title" => $title,
            "amount" => $amount,
            "operation_date" => $operation_date,
            "errorsTitle" => $errorsTitle,
            "errorsAmount" => $errorsAmount,
            "errorsDate" => $errorsOperation_date,
            "errorRepartition" => $errorRepartition,
            "errors" => $errors
        ));
    }
    public function edit_operation(): void
    {
        $connected_user = $this->get_user_or_redirect();
        $errors = [];
        if (
            isset($_GET['param1']) && isset($_GET['param2']) &&
            isset($_GET['param1']) != '' && isset($_GET['param1']) != ''
        ) {

            if (isset($_GET["param1"])) {
                $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
                if ($tricount == false) {
                    $this->redirect("tricount", "index");
                }
                $sub_or_not = $connected_user->isSub($connected_user->getId(), $tricount->getId());
                // si l'utilisateur connecté est abonné au tricount, il a accès 
                if (!$sub_or_not) {
                    $this->redirect("tricount", "index");
                } else {
                    $operation = Operation::get_operation_by_id($_GET['param2']);
                    if ($operation == null or $connected_user->isSub($connected_user->getId(), $tricount->getId()) == null) {
                        $this->redirect("tricount", "index");
                    }
                    $tricount = Tricount::get_tricount_by_id($_GET['param1']);
                    $users_in_tricount = Tricount::get_all_sub_to_a_tricount($tricount->getId());
                    // gettings users in the operations 
                    $users = Repartition::get_users_from_an_operation($operation->getId());
                    // getting the weight of users                     
                    foreach ($users as $user) {
                        // getting the weight of each users in the operation
                        $weight = Repartition::get_weight_by_operation($operation->getId(), $user->getId());
                        $user_weight[$user->getId()] = $weight;
                    }
                    if (
                        isset($_POST['title']) && isset($_POST['title']) != '' &&
                        isset($_POST['amount']) && isset($_POST['amount']) > 0 &&
                        isset($_POST['initiator']) && isset($_POST['operation_date'])
                    ) {

                        $title = Tools::sanitize($_POST["title"]);
                        $amount = $_POST["amount"];
                        $initiator = User::get_user_by_id($_POST["initiator"]);
                        $date = $_POST['operation_date'];
                        $operation->setTitle($title);
                        $operation->setAmount($amount);
                        $operation->setInitiator($initiator);
                        $operation->setOperationDate($date);

                        $errors = $operation->validate();


                        $count = 0;
                        $users_in_repartition = Repartition::getByOperationId($operation->getId());
                        if (isset($_POST['weight']) && isset($_POST['user'])) {
                            // Récupère les nouvelles répartitions depuis la requête POST
                            $new_repartition = $_POST['weight'];
                            // supprime toutes les anciennes répartitions et ajoute les nouvelles
                            foreach ($users_in_repartition as $rep) {
                                $rep->delete_repartition($operation->getId());
                            }
                            // Parcourt chaque élément de la nouvelle répartition
                            foreach ($new_repartition as $user_id => $new_weight) {
                                // Vérifie si le poids est supérieur à zéro et si l'utilisateur est sélectionné
                                if ($new_weight > 0 && in_array($user_id, $_POST['user'])) {
                                    $r = new Repartition($operation->getId(), $user_id, $new_weight);
                                    $r->add();
                                    if ($r != null) {
                                        $count++;
                                    }
                                }
                            }
                        }

                        if ($count <= 0) {
                            $array_er = array();
                            $array_er[] = "Il y a une erreur dans les répartitions";
                            $errors = array_merge($errors, $array_er);
                        }

                        if (empty($errors)) {

                            $operation->update();
                            $this->redirect("operation", "view_operation", $tricount->getId(), $operation->getId());
                        }
                    }
                }
            }
        }

        (new View("edit_operation"))->show(array(
            "user" => $connected_user,
            "operation" => $operation,
            "users" => $users_in_tricount,
            "tricount" => $tricount,
            "user_weight" => $user_weight,
            "errors" => $errors
        ));
    }

    public function delete_operation(): void
    {
        // Get the connected user
        $connected_user = $this->get_user_or_redirect();

        // Get the operation details
        if (
            isset($_GET['param1']) != '' && isset($_GET['param2']) != '' &&
            isset($_GET['param1'])  && isset($_GET['param2'])
        ) {

            if (isset($_GET["param1"])) {
                $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
                if ($tricount == false) {
                    $this->redirect("tricount", "index");
                }

                $sub_or_not = $connected_user->isSub($connected_user->getId(), $tricount->getId());
                // si l'utilisateur connecté est abonné au tricount, il a accès 
                if (!$sub_or_not) {
                    $this->redirect("tricount", "index");
                } else {
                    $operation = Operation::get_operation_by_id($_GET['param2']);
                    if ($operation == null or $connected_user->isSub($connected_user->getId(), $tricount->getId()) == null) {
                        $this->redirect("tricount", "index");
                    }
                }
            }
        }
        (new View("delete_operation"))->show(array(
            "user" => $connected_user,
            "tricount" => $tricount,
            "operation" => $operation,
        ));
    }

    public function delete_service(): void
    {
        $operationToDelete = Operation::get_operation_by_id($_GET['param2']);
        Operation::delete_operation($operationToDelete);
        $this->redirect("tricount", "view_tricount", $_GET['param1']);
    }
    public function delete_service_json(): void
    {
        $operationToDelete = Operation::get_operation_by_id($_POST['id_op']);

        if ($operationToDelete) {
            Operation::delete_operation($operationToDelete);
            // Vous pouvez renvoyer une réponse JSON pour indiquer que la suppression s'est bien passée
            echo json_encode(['success' => true]);
        } else {
            // En cas d'erreur, vous pouvez renvoyer une réponse JSON avec un message d'erreur
            echo json_encode(['success' => false, 'message' => 'Operation not found']);
        }
    }
}
