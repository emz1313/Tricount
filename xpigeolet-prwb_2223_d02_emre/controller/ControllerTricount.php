<?php

require_once 'model/Tricount.php';
require_once 'model/User.php';
require_once 'model/Subscription.php';
require_once 'model/Operation.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'model/Repartition.php';
require_once 'model/Repartition_template.php';
require_once 'model/Repartition_template_item.php';

class ControllerTricount extends Controller
{

    public function index(): void
    {
        $this->list_tricounts();
    }

    // Display of the list of the tricounts 
    public function list_tricounts(): void
    {

        if ($this->user_logged()) {

            $user = $this->get_user_or_redirect();
            $errors = [];
            // liste des abonnements 
            $listeSubscription = Subscription::get_list_subscription();
            // liste des tricounts 
            $listeTricounts = Tricount::get_all_sub_tricount_by_user($user->getId());
            $nbSub = [];
            // boucle afin d'avoir le nombre de sub par tricount
            foreach ($listeSubscription as $subscription) {
                $nbSub[] = Subscription::get_number_of_subscribers($subscription->getTricount());
            }



            (new View("list_tricounts"))->show(array(
                "user" => $user,
                "listeTricounts" => $listeTricounts,
                "listeSubscription" => $listeSubscription,
                "nbSub" => $nbSub,
                "errors" => $errors,

            ));
        }
    }

    // Display of an tricount 
    public function view_tricount(): void
    {

        $user_connected = $this->get_user_or_redirect();

        $nbsub = 0;

        if (isset($_GET["param1"])) {
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            if ($tricount == false) {
                $errors = "Ce tricount n'existe pas ";
                // $this->redirect("tricount", "index");
                (new View("error"))->show(array("errors" => $errors));
            } else {

                $sub_or_not = $user_connected->isSub($user_connected->getId(), $tricount->getId());
                // si l'utilisateur connecté est abonné au tricount, il a accès 

                if ($sub_or_not) {
                    $operations = Operation::get_operation_by_tricount($_GET["param1"]);
                    $nbsub = $tricount->getSubs($tricount->getId());
                    $nbop = $tricount->getOps($tricount->getId());

                    $expenses = 0;
                    foreach ($operations as $op) {

                        $expenses += $op->getAmount();
                    }
                    $total = 0;
                    foreach ($operations as $op) {
                        $user_weight = Repartition::get_weight_by_operation($op->getId(), $user_connected->getId());
                        $total_weight = 0;
                        $users_weights = Repartition::get_users_and_weights_by_operation($op->getId());
                        foreach ($users_weights as $user) {
                            $total_weight += $user[1];
                        }
                        $total += $op->getAmount() * $user_weight / $total_weight;
                    }
                }

                // doing an if and else condition to show different display 
                if ($nbsub == 1 && $nbop <= 1) {
                    (new View("tricount_alone"))->show(array(
                        "user" => $user_connected,
                        "tricount" => $tricount,
                        "operations" => $operations,
                        "nbsub" => $nbsub,
                        "expense" => $expenses,
                        "total" => $total
                    ));
                } else if ($nbsub > 1 && $nbop == 0) {
                    (new View("tricount_empty"))->show(array(
                        "user" => $user_connected,
                        "tricount" => $tricount,
                        "operations" => $operations,
                        "nbsub" => $nbsub,
                        "expense" => $expenses,
                        "total" => $total
                    ));
                } else if ($nbsub >= 1 && $nbop >= 1) {
                    (new View("tricount"))->show(array(
                        "user" => $user_connected,
                        "tricount" => $tricount,
                        "operations" => $operations,
                        "nbsub" => $nbsub,
                        "expense" => $expenses,
                        "total" => $total
                    ));
                } else {
                    $errors = "Vous n'avez pas accès à ce tricount";
                    (new View("error"))->show(array("errors" => $errors));
                }
            }
        }
    }

    // prendre les totaux de chacuns et faire une soustraction de ses initiations 
    public function view_balance(): void
    {
        $errors = [];
        $connected_user = $this->get_user_or_redirect();

        if (isset($_GET["param1"])) {
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            if ($tricount == false) {
                $this->redirect("tricount", "index");
            }

            $sub_or_not = $connected_user->isSub($connected_user->getId(), $tricount->getId());
            // si l'utilisateur connecté est abonné au tricount, il a accès 

            if ($sub_or_not) {
                // get all users of the tricount 
                $users = Tricount::get_all_sub_to_a_tricount($tricount->getId());
                // get all totals of users in the tricount
                $total = 0;
                $operations = Operation::get_operation_by_tricount($_GET["param1"]);
                $user_totals = array();
                foreach ($users as $u) {
                    $user_total = 0;
                    foreach ($operations as $op) {
                        $user_weight = Repartition::get_weight_by_operation($op->getId(), $u->getId());
                        $total_weight = 0;
                        $users_weights = Repartition::get_users_and_weights_by_operation($op->getId());
                        foreach ($users_weights as $user_w) {
                            $total_weight += $user_w[1];
                        }
                        $user_total += $op->getAmount() * $user_weight / $total_weight;
                    }
                    $user_totals[$u->getId()] = $user_total;
                }


                foreach ($users as $u) {
                    $initiated = Tricount::get_all_amount_initiated($u, $tricount);
                    $balance = $initiated - $user_totals[$u->getId()];
                    $list[] = array('user' => $u, 'balance' => $balance);
                }
            } else {
                $this->redirect("tricount", "index");
            }
        }


        (new View("balance", $_GET['param1']))->show(array(
            "user" => $connected_user,
            "tricount" => $tricount,
            "initiated" => $initiated,
            "list" => $list,
            "user_totals" => $user_totals,
            "users" => $users,
            "errors" => $errors
        ));
    }

    public function add_tricount(): void
    {

        $errors = [];
        $user = $this->get_user_or_redirect();


        if (
            isset($_POST['title']) && isset($_POST['created_at'])
            && isset($_POST['description'])
        ) {
            $title = Tools::sanitize($_POST['title']);
            $description = Tools::sanitize($_POST['description']);
            $created_at = $_POST['created_at'];
            $creator = $user;

            $tricount = new Tricount($title, $description, $created_at, $creator->getId());
            $subscription = new Subscription($tricount, $user);

            $errors = $tricount->validate_title_add($title, $user);
            $errors = array_merge($errors, $tricount->validate_description($description));

            if (count($errors) == 0) {
                $tricount->add();
                $subscription->persist();
                $this->redirect("tricount", "view_tricount", $tricount->getId());
            }
        }

        (new View("add_tricount"))->show(array(
            "user" => $user,
            "errors" => $errors
        ));
    }

    public function edit_tricount(): void
    {
        $user_connected = $this->get_user_or_redirect();
        $list_users = $user_connected->get_all_users();
        $errors = [];

        if (isset($_GET["param1"])) {
            $tricount = Tricount::get_tricount_by_id($_GET["param1"]);
            if ($tricount == false) {
                $this->redirect("tricount", "index");
            }

            $sub_or_not = $user_connected->isSub($user_connected->getId(), $tricount->getId());
            // si l'utilisateur connecté est abonné au tricount, il a accès 
            if ($sub_or_not) {

                $notsub = Subscription::get_not_subscribed_users_by_tricount($tricount);
                $subs = Tricount::get_all_sub_to_a_tricount($tricount->getId());
                $subs_json = Tricount::get_all_sub_json($tricount->getId());
                $notsub_json = Subscription::get_not_subscribed_users_by_tricount_json($tricount);


                if (isset($_POST['title']) && isset($_POST['description'])) {
                    if (!empty($_POST['title'])) {
                        $errors = array_merge($errors, $tricount->validate_description($_POST['description']));
                        $tricount->setDescription(Tools::sanitize($_POST['description']));
                        if ($tricount->getTitle() != $_POST["title"]) {
                            $errors = array_merge($errors, $tricount->validate_title_add($_POST['title'], $user_connected));
                            $tricount->setTitle(Tools::sanitize($_POST['title']));
                        }

                        if (empty($errors)) {
                            $tricount->update();
                            $this->redirect("tricount", "index");
                        }
                    }
                }

                $liste_expense = [];
                foreach ($subs as $u) {
                    $tr = Repartition::isAnyDepenseInTheTricount($u, $tricount);

                    $isInitiator[$u->getId()] = Tricount::isThereAnInitiatedOperationByUser($user_connected, $tricount);
                    $liste_expense[$u->getId()] = $tr;
                    if ($liste_expense[$u->getId()] == false) {
                        $canDelete[$u->getId()] = false;
                        $op = Operation::get_operation_by_tricount($tricount->getId());
                        foreach ($op as $o) {
                            if ($o->getInitiator() == $u) {
                                $canDelete[$u->getId()] = true;
                            }
                        }
                    } else  $canDelete[$u->getId()] = true;
                    if ($tricount->getCreator() == $u) {
                        $canDelete[$u->getId()] = true;
                    }
                }

                (new View("edit_tricount"))->show(array(
                    "user" => $user_connected,
                    "tricount" => $tricount,
                    "subs" => $subs,
                    "subs_json" => $subs_json,
                    "notsub_json" => $notsub_json,
                    "list_users" => $list_users,
                    "canDelete" => $canDelete,
                    "notsub" => $notsub,
                    "liste_expense" => $liste_expense,
                    "errors" => $errors,

                ));
            }
        }
    }

    public function delete_tricount(): void
    {

        $user_connected = $this->get_user_or_redirect();

        if (isset($_GET['param1']) != '' && isset($_GET['param1'])) {
            $tricount = Tricount::get_tricount_by_id($_GET['param1']);
        } else {
            $this->redirect("tricount", "index");
        }
        $sub_or_not = $user_connected->isSub($user_connected->getId(), $tricount->getId());
        // si l'utilisateur connecté est abonné au tricount, il a accès 
        if (!$sub_or_not) {
            $this->redirect("tricount", "index");
        }


        (new View("delete_tricount"))->show(array(
            "tricount" => $tricount,
            "user" => $user_connected
        ));
    }

    public function delete_service(): void
    {

        $tricount = Tricount::get_tricount_by_id($_POST['confirm_delete']);
        $tricount->delete($tricount);
        $this->redirect("tricount", "index");
    }

    public function delete_service_json(): void
    {
        $tricountId = $_POST['tricountId'];
        $tricount = Tricount::get_tricount_by_id($tricountId);

        if ($tricount) {
            $tricount->delete($tricount);
            // Vous pouvez renvoyer une réponse JSON pour indiquer que la suppression s'est bien passée
            echo json_encode(['success' => true]);
        } else {
            // En cas d'erreur, vous pouvez renvoyer une réponse JSON avec un message d'erreur
            echo json_encode(['success' => false, 'message' => 'Tricount not found']);
        }
    }

    public function add_sub(): void
    {
        $this->get_user_or_redirect();
        if (isset($_POST["new_sub"]) && isset($_POST["tricount_id"])) {
            $user_id = $_POST["new_sub"];
            $tricount_id = $_POST["tricount_id"];
            $user = User::get_user_by_id($user_id);
            $tricount = Tricount::get_tricount_by_id($tricount_id);
            (new Subscription($tricount, $user))->persist();
        } else {
            echo "faux";
        }

        $this->redirect("tricount", "edit_tricount", $_POST["tricount_id"]);
    }

    public function add_sub_json(): void
    {
        $errors = [];

        if (isset($_POST["new_sub"]) && isset($_POST["tricount_id"])) {
            $user_id = $_POST["new_sub"];
            $tricount_id = $_POST["tricount_id"];
            $user = User::get_user_by_id($user_id);
            $tricount = Tricount::get_tricount_by_id($tricount_id);
            if ($user && $tricount) {
                (new Subscription($tricount, $user))->persist();
                echo json_encode(array('success' => true, 'user_id' => $user->getId(), 'user_fullname' => $user->getFullname()));
            } else {
                $errors = "User or tricount not found";
                echo json_encode(array('success' => false, 'errors' => $errors));
            }
        } else {
            $errors = "Missing parameters";
            echo json_encode(array('success' => false, 'errors' => $errors));
        }
    }

    public function del_sub(): void
    {

        $errors = [];

        if (isset($_POST["user_id"]) && isset($_POST["tricount_id"])) {
            $user_id = $_POST["user_id"];
            $tricount_id = $_POST["tricount_id"];
            $user = User::get_user_by_id($user_id);
            $tricount = Tricount::get_tricount_by_id($tricount_id);
            $sub = Subscription::get_subscriptions_by_user_and_tricount($tricount, $user);

            $creator = $tricount->getCreator();
            if ($sub && ($sub->getUser() != $creator)) {
                if (empty($errors)) {
                    $sub->delete();
                    $this->redirect("tricount", "edit_tricount", $_POST["tricount_id"]);
                } else {
                    $errors = "You can't delete the creator of the tricount";
                    (new View("error"))->show(array("errors" => $errors));
                }
            } else {
                $errors = "You can't delete the creator of the tricount";
                (new View("error"))->show(array("errors" => $errors, "tricount" => $tricount));
            }
        }
    }

    public function del_sub_json(): void
    {
        $errors = [];

        if (isset($_POST["user_id"]) && isset($_POST["tricount_id"])) {
            $user_id = $_POST["user_id"];
            $tricount_id = $_POST["tricount_id"];
            $user = User::get_user_by_id($user_id);
            $tricount = Tricount::get_tricount_by_id($tricount_id);
            $sub = Subscription::get_subscriptions_by_user_and_tricount($tricount, $user);
            $creator = $tricount->getCreator();

            if ($sub && ($sub->getUser() != $creator)) {
                $sub->delete();
                echo json_encode(array('success' => true));
            } else {
                $errors = "You can't delete the creator of the tricount";
                echo json_encode(array('success' => false, 'errors' => $errors));
            }
        }
    }


    public function compareTitleTricount()
    {
        $user_connected = $this->get_user_or_redirect();
        $tricounts = Tricount::get_all_tricounts_by_user($user_connected->getId());
        $new_title = $_POST['new_title'];

        $titles = array();
        foreach ($tricounts as $tricount) {
            $titles[] = $tricount->getTitle();
        }

        foreach ($titles as $t) {
            if (strcasecmp($t, $new_title) == 0) {
                echo "true";
            }
        }

        echo "false";
    }
    public function title_available_service(): void
    {
        $res = true;
        $user_connected = $this->get_user_or_redirect();
        if (isset($_POST["param1"]) && $_POST["param1"] !== "") {
            $tricount_title = ($_POST['tricount_title']);
            $title = ($_POST['param1']); // Supprimer les espaces et décoder l'URL
            $titleAvailable = Tricount::get_tricount_title_by_user($title, $user_connected->getId());
            if ($titleAvailable && $tricount_title != $title) {
                $res = false;
            }
        }
        echo json_encode($res); // Encoder la valeur en JSON
    }
}
