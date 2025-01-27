<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
$memberid = $cardnum = $expdate = $nameCard = $errMsg = "";
$fname = $lname = $phone = $email = "";
$cartArray = $resultsArray = [];
$success = true;
$memberid = 27;
$secretKey = "secret";
//echo 'decrypted: ' . decrypt("AWLUnaAOY/WCgqvTYpFMbg==", $secretKey);

getCards();
$successD = true;
getUserDetails();
$successC = true;
getCartItems();
$cardnum = MaskCreditCard($cardnum);

function MaskCreditCard($cc) {
    $cc = str_replace(array('-', ' '), '', $cc);
    // Get the CC Length
    $cc_length = strlen($cc);
    // Initialize the new credit card to contain the last four digits
    $newCreditCard = substr($cc, -4);
    // Walk backwards through the credit card number and add a dash after every fourth digit
    for ($i = $cc_length - 5; $i >= 0; $i--) {
        // If on the fourth character add a dash
        if ((($i + 1) - $cc_length) % 4 == 0) {
            $newCreditCard = ' ' . $newCreditCard;
        }
        // Add the current character to the new credit card
        $newCreditCard = $cc[$i] . $newCreditCard;
    }

    // Get the cc Length
    $cc_length = strlen($newCreditCard);
    // Replace all characters of credit card except the last four and dashes
    for ($i = 0; $i < $cc_length - 4; $i++) {
        if ($newCreditCard[$i] == ' ') {
            continue;
        }
        $newCreditCard[$i] = 'X';
    }
    // Return the masked Credit Card #
    return $newCreditCard;
}

function decrypt($encryptedText, $key) {
    $key = md5($key);
    $iv = substr(hash('sha256', "aaaabbbbcccccddddeweee"), 0, 16);
    $decryptedText = openssl_decrypt(base64_decode($encryptedText), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $decryptedText;
}

function getCards() {
    global $memberid, $errMsg, $success, $resultsArray;

    $config = parse_ini_file('../../private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'],
            $config['password'], $config['dbname']); //$config['dbname']
    if ($conn->connect_error) {
        $errMsg = "Connection failed: " . $conn->connect_error;
        $success = false;
    } else {
// Prepare the statement:
        $stmt = $conn->prepare("SELECT * FROM creditcard WHERE members_mid=?");
// Bind & execute the query statement:
        $stmt->bind_param("i", $memberid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_all();
            $resultsArray = $row;
        } else {
            $errMsg = "No Saved Cards";
            $success = false;
        }$stmt->close();
    }
    $conn->close();
}

//!!!!!ADD THIS
function getUserDetails() {
    global $memberid, $fname, $lname, $phone, $email, $errMsg, $successD;

    $config = parse_ini_file('../../private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'],
            $config['password'], $config['dbname']); //$config['dbname']
    if ($conn->connect_error) {
        $errMsg = "Connection failed: " . $conn->connect_error;
        $successD = false;
    } else {
// Prepare the statement:
        $stmt = $conn->prepare("SELECT * FROM members WHERE mid=?");
// Bind & execute the query statement:
        $stmt->bind_param("i", $memberid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $fname = $row["fname"];
            $lname = $row["lname"];
            $phone = $row["phoneno"];
            $email = $row["email"];
        } else {
            $errMsg = "User Not Found";
            $successD = false;
        }$stmt->close();
    }
    $conn->close();
}

function getCartItems() {
    global $memberid, $errMsg, $successC, $cartArray;
//    $success = true;
    $config = parse_ini_file('../../private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'],
            $config['password'], $config['dbname']); //$config['dbname']
    if ($conn->connect_error) {
        $errMsg = "Connection failed: " . $conn->connect_error;
        $successC = false;
    } else {
        $stmt = $conn->prepare("SELECT * FROM tour_packages T, cart_has_tour_packages CT WHERE T.pid = CT.tour_packages_pid AND cart_cartid = (SELECT cart_cartid FROM travel.members WHERE mid = ?);");
        $stmt->bind_param("i", $memberid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_all();
            $cartArray = $row;
        } else {
            $errMsg = "NO CART ITEMS";
            $successC = false;
        }$stmt->close();
    }
    $conn->close();
}
?>

<html>
    <head>
        <title>CHECKOUT</title>
        <link rel="stylesheet" href="main.css" />
        <link rel="stylesheet"
              href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
              integrity=
              "sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
              crossorigin="anonymous">
        <!-- CSS has to be After the BootStrap -->
        <link rel="stylesheet" href="cssmain.css"/>
        <!--jQuery *Bootstrap JS uses jQuery functions so jQuery must be above Bootstrap JS--> 
        <script defer
                src="https://code.jquery.com/jquery-3.4.1.min.js"
                integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
                crossorigin="anonymous">
        </script>
        <!--Bootstrap JS-->
        <script defer
                src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"
                integrity="sha384-6khuMg9gaYr5AxOqhkVIODVIvm9ynTT5J4V1cfthmT+emCG6yVmEZsRHdxlotUnm"
                crossorigin="anonymous">
        </script>
        <!--FOR CREDIT CARD FORMATTING -->
        <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jquery.payment/3.0.0/jquery.payment.min.js"></script>
        <!-- Custom JS -->
        <script defer src="scripts.js"></script>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-4 order-md-2 mb-4">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Your Cart</span>
                    </h4>

                    <ul class="list-group mb-3">

                        <?php
                        if ($successC) {
                            $total = 0;
//                            echo "RESULT ARRAY: " . count($cartArray) . "<br>";
                            foreach ($cartArray as $values) {
//                                echo $values[1] . ", " . $values[2] . ", " . $values[10] . ", " . $values[3] . "<br>";
                                $curr = $values[3] * $values[10]; //$values[10]
                                echo '<li class="list-group-item d-flex justify-content-between lh-condensed">
                                        <div>
                                            <h6 class="my-0">' . $values[1] . ", " . $values[2] . '</h6>
                                            <small class="text-muted">Quantity: ' . $values[10] . '</small>
                                        </div>
                                        <span class="text-muted">$' . $curr . '</span> 
                                      </li>';
                                $total += $curr;
                            }
                        } else {
//                            echo "<h2>Oops!</h2>";
                            echo "<p>No Cart Items found.</p>";
//                            echo "<p>" . $errorMsg . "</p>";
                        }
                        ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total</span>
                            <strong>$<?php echo $total; ?></strong>
                        </li>
                    </ul>
                </div>
                <div class="col-md-8 order-md-1">
                    <h4 class="mb-3">Contact Information</h4>
                    <form class="needs-validation" id="checkoutform" action="booknow_process.php" method="POST" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName">First name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="" value="<?php echo $fname; ?>" required>
                                <div class="invalid-feedback">
                                    Valid first name is required.
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName">Last name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="" value="<?php echo $lname; ?>" required>
                                <div class="invalid-feedback">
                                    Valid last name is required.
                                </div>
                            </div>
                        </div>
                        <label for="phoneNo">Phone Number: </label>
                        <div class="row">
                            <div class="form-group col-md-8 mb-4">
                                <input class="form-control" type="number" id="phoneNo" name="phoneNo" value="<?php echo $phone; ?>" required>  <!--pattern="\+65[6|8|9]\d{7}"-->
                                <div class="invalid-feedback" style="width: 100%;">
                                    Your phone number is required.
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" value="<?php echo $email; ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        <hr class="mb-4">
                        <h4 class="mb-3">Payment</h4>
                        <h5>Saved Cards</h5>
                        <?php
                        if ($success) {
//                            echo "RESULT ARRAY: " . count($resultsArray) . "<br>";
                            foreach ($resultsArray as $values) {
//                            foreach ($values as $val => $card){
//                                echo "$val = $card<br>";
                                echo "<input type='radio' id='card' onclick='displayRadioValue()'"
                                . " name='card' value='" . MaskCreditCard(decrypt($values[1], $secretKey)) . "," . $values[2] . "," . $values[3] . "'><label for='html'> "
                                . MaskCreditCard(decrypt($values[1], $secretKey)) . "</label><br>";
                            }
//                        }
//            }
                        } else {
//                            echo "<h2>Oops!</h2>";
                            echo "<p>No Cards Saved</p>";
//                            echo "<p>" . $errorMsg . "</p>";
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ccname">Name on card</label>
                                <input type="text" class="form-control" id="ccname" name="ccname" placeholder="" required>
                                <small class="text-muted">Full name as displayed on card</small>
                                <div class="invalid-feedback">
                                    Name on card is required
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ccnum">Credit card number</label>
                                <input type="text" class="form-control ccnum" id="ccnum" name="ccnum" placeholder="•••• •••• •••• ••••" required>
                                <div class="invalid-feedback">
                                    Credit card number is required
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="ccexp">Expiration</label>
                                <input type="text" class="form-control ccexp" id="ccexp" name="ccexp" placeholder="•• / ••" required value=>
                                <div class="invalid-feedback">
                                    Expiration date required
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="cccvc">CVV</label>
                                <input type="text" class="form-control cccvc" id="cccvc" name="cccvc" placeholder="•••" required>
                                <div class="invalid-feedback">
                                    Security code required
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <!--<label for="ccbrand">Card Type</label>-->
                                <input type="hidden" id="totalamt" name="totalamt" value="<?php echo $total; ?>">
                            </div>
                        </div>
                        <div class="form-group form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="savecard">Save this card
                            </label>
                        </div>
                        <hr class="mb-4">
                        <button class="btn btn-primary btn-lg btn-block" id="submitBtn" type="submit">Continue to checkout</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
