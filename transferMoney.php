<?php
    session_start();
    require_once "pdo.php";

    //POST DATA HANDLE
    if(isset($_POST['submit'])) {
        if ( isset($_POST['cancel']) ) {
            header('Location: index.php');
            return;
        }
        if( isset($_POST['sender']) && isset($_POST['receiver']) && isset($_POST['amount'])) {
            //CHECKING SENDER
            $sql="SELECT * FROM customers WHERE id = :id";
            $stmt=$db->prepare($sql);
            $stmt->execute(array(
                ':id' => $_POST['sender']
            ));
            $row=$stmt->fetch(PDO::FETCH_ASSOC);
            if($row == false)
            {
                $_SESSION['error'] = "Sender Not Available";
                header("Location:transaction.php");
                return;
            }
            $Sbalance = $row['balance'];


            //CHECKING BALANCE
            if($_POST['amount'] > $Sbalance)
            {
                if($_POST['amount'] <= 0) {
                    $_SESSION['error'] = "Transferrable amount should be greater than 0";
                    header("Location:transaction.php");
                    return; 
                }
                $_SESSION['error'] = "Entered Amount not available in Account";
                header("Location:transaction.php");
                return; 
            }
            $Sbalance = $Sbalance -  $_POST['amount'];


            //CHECKING RECEIVER
            $sql="SELECT * FROM customers WHERE id = :id";
            $stmt=$db->prepare($sql);
            $stmt->execute(array(
                ':id' => $_POST['receiver']
            ));
            $row=$stmt->fetch(PDO::FETCH_ASSOC);
            if($row == false)
            {
                $_SESSION['error'] = "Receiver Not Available";
                header("Location:transaction.php");
                return;
            }
            $Rbalance = $row['balance'];
            $Rbalance = $Rbalance + $_POST['amount'];


            //INSERTING INTO DATABASE
            $sql="INSERT INTO history (sender_id,receiver_id,amount) VALUES (:sender, :receiver, :amount)";
            $stmt=$db->prepare($sql);
            $stmt->execute(array(
                ':sender' => $_POST['sender'],
                ':receiver' => $_POST['receiver'],
                ':amount' => $_POST['amount']
            ));


            //Updating Sender's Balance
            $sql = " UPDATE customers SET balance = :balance WHERE id = :id ";
            $stmt=$db->prepare($sql);
            $stmt->execute(array(
                ':balance' => $Sbalance,
                ':id' => $_POST['sender']
            ));


            //Updating Receiver's Balance
            $sql = " UPDATE customers SET balance = :balance WHERE id = :id ";
            $stmt=$db->prepare($sql);
            $stmt->execute(array(
                ':balance' => $Rbalance,
                ':id' => $_POST['receiver']
            ));


            $_SESSION['success']="Transaction Successful";
            header('Location:history.php');
            return;
        }
    }

    //GET DETAILS
    if(isset($_GET['id'])){
        if($_GET['id']>0) {
            $sql="SELECT * FROM customers WHERE id = :id";
            $stmt=$db->prepare($sql);
            $stmt->execute(array(
                ':id' => $_GET['id']
            ));
            $row=$stmt->fetch(PDO::FETCH_ASSOC);
            if($row == false)
            {
                header("Location:transaction.php");
                return;
            }
            $id = $_GET['id'];
            $name = htmlentities($row['name']);
            $balance=htmlentities($row['balance']);
        }
        else {
            header("Location:transaction.php");
            return; 
        }
    }
    else {
        header("Location:transaction.php");
        return;   
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer"
    />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@300&display=swap" rel="stylesheet">


    <title>Transaction</title>
</head>

<body>


    <nav class="navbar navbar-expand-lg navbar-dark ">
        <a class="navbar-brand" href="index.php"> <img class="nav-logo" src="images/favicon.png" alt="logo image"> Ideal Bank</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse " id="navbarNav">
            <ul class="navbar-nav ml-md-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="customer.php">Customer</a>
                </li>  
                <li class="nav-item active">
                    <a class="nav-link " href="transaction.php">Transact</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link " href="history.php">History</a>
                </li>            
            </ul>
        </div>
    </nav>

     <div class="container-fluid customer-table">
        <div class="table-head">
            <h2> Transact</h2>
        </div>
        <form method = "post" action="transferMoney.php">
            <div class="form-group">
                <label class="col-sm-2 col-form-label">Sender :</label>
                <input type="text" class="form-control" readonly value=<?=$name?>>
            </div>
            <div class="form-group">
                <label class="col-sm-2 col-form-label">Balance : </label>
                <input type="number" class="form-control" readonly value=<?=$balance?>>
            </div>
            <div class="form-group">
                <input type="hidden" name="sender" value=<?=$id?>>
                <label for="receiver" class="col-sm-2 col-form-label">Email : </label>
                <select class="form-control" aria-label="Default select example" name="receiver" required>
                  <option selected>Select Receiver</option>
                  <?php 
                        $stmt = $db->query("SELECT * FROM customers");
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
                        { 
                            if($row['id'] == $id)
                                continue;
                            ?>
                            <option value = <?= $row['id']?> > <?=$row['name']?> </option>
                        <?php }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="amount" class="col-sm-2 col-form-label">Amount : </label>
                <input type="number" class="form-control" name="amount" required>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-success" name="submit" value="Submit">
                <button type="button"class="btn btn-danger"><a href="index.php" style="color:white;text-decoration:none;">Cancel</a></button>
            </div>
        </form>    

    </div>


    <div class="container-fluid footer ">
        <div class="row ">
            <div class="col-md-4 footer-image ">

                <p>The Ideal Bank (TSF) is an Indian multinational, public sector banking and financial services statutory body headquartered in Mumbai, Maharashtra</p>

            </div>

            <div class=" col-md-4 link ">
                <ul>
                    <li class="nav-item ">
                        <a class="nav-link " href="index.php">Home</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link " href="customer.php">Customer</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link " href="#">Transacty</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link " href="history.php">History</a>
                    </li> 

                </ul>
            </div>
            <div class=" col-md-4 social-link ">
                <a href=" "><i class="fab fa-linkedin fa-2x "></i></a>
                <a href=" "><i class="fab fa-twitter-square fa-2x "></i> </a>
                <a href=" "><i class="fab fa-facebook-square fa-2x "></i></a>

            </div>
        </div>

    </div>
</body>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js " integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj " crossorigin="anonymous "></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js " integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx " crossorigin="anonymous "></script>

<script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#myTable').DataTable();
    });
</script>

</html>