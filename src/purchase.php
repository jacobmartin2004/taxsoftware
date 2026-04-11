<?php
require_once 'auth.php';
include('conn.php');
$companynames = [];
$sql = "SELECT companyname FROM companydata";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $companynames[] = $row['companyname'];
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .input-group1 {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            /* Aligns the label and select vertically in the center */
        }

        .custom-label1 {
            margin-right: 10px;
            /* Space between the label and the select input */
            min-width: 150px;
            /* Adjust this to ensure the label takes up appropriate width */
        }

        .radio-group1 {
            align-items: center;
            justify-content: center;
            display: flex;
            gap: 20px;
            /* Space between radio buttons */
            align-items: center;
            /* Vertically aligns the radio inputs and labels */
        }

        .custom-radio1 {
            display: flex;
            align-items: center;
            cursor: pointer;
            /* Pointer on hover for better user experience */
        }

        .custom-radio1 input[type="radio"] {
            margin-right: 8px;
            /* Space between the radio button and the label text */
        }

        .custom-radio1 input[type="radio"]:hover {
            cursor: pointer;
            /* Changes the cursor to pointer on hover */
        }

        .custom-radio1 input[type="radio"]:checked {
            accent-color: #007BFF;
            /* Change radio button color when checked (for modern browsers) */
        }

        .custom-input1 {
            flex-grow: 1;
            /* Makes the select box grow to fill the remaining space */
            padding: 8px;
        }

        /* Flexbox layout for input group */
        .input-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        /* Custom label styles */
        .custom-label {
            margin-right: 10px;
            width: 150px;
            /* Adjust as per your requirement */
            font-weight: bold;
            font-family: Arial, sans-serif;
            color: #333;
            text-align: right;
        }

        /* Custom input and select styles */
        .custom-input,
        .custom-select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            flex-grow: 1;
            width: auto;
        }

        .custom-label1 {
            margin-right: 10px;
            width: 150px;
            /* Adjust as per your requirement */
            font-weight: bold;
            font-family: Arial, sans-serif;
            color: #333;
            text-align: right;
        }

        /* Custom input and select styles */
        .custom-input1,
        .custom-select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            flex-grow: 1;
            width: auto;
        }

        /* Input focus styles */
        .custom-input:focus,
        .custom-select:focus {
            border-color: #66afe9;
            outline: none;
        }

        /* Custom button styles */
        .custom-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .custom-button:hover {
            background-color: #45a049;
        }

        /* Radio button group */
        .radio-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .custom-radio {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .space-pad {
            margin-top: 10px;
        }


        .custom-button:hover {
            background-color: #45a049;
        }

        /* Radio button group */
        .radio-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .custom-radio {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .space-pad {
            margin-top: 10px;
        }

        body {
            background: url('https://www.mechatronicsart.com/wp-content/uploads/2016/06/Vilarpac_website_background.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        .jumbotron {
            background: rgba(255, 255, 255, 0.8);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 20px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3);
            animation: fadeIn 2s;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .navbar-brand {
            color: #000 !important;
            font-weight: bold;
        }

        .vertical-line {
            border-left: 3px solid #000;
            height: 100%;
            animation: slideInLeft 1s;
        }

        .space-pad {
            padding: 10px 15px;
            /* Top, right, bottom, left */
        }

        .form-control {
            border-radius: 0;
            animation: fadeIn 1.5s;
        }

        .btn {
            border-radius: 0;
            background-color: #000;
            color: #fff;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #555;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
            }

            to {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="jumbotron text-center">
                    <h1>Delvin Diamond Tools Industries</h1>
                    <h4>Somarsampettai</h4>
                    <h4>Trichy-102..,</h4>
                    <hr>
                    <nav class="navbar navbar-expand-md navbar-light">
                        <div class="container-fluid">
                            <div class="dropdown">
                                <a class="navbar-brand dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Home
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="../index.php">Sales</a></li>
                                    <li><a class="dropdown-item" href="purchase.php">Purchase</a></li>
                                </ul>
                            </div>
                            <a class="navbar-brand" href="view.php">Sales Record</a>
                            <a class="navbar-brand" href="../public/view1.php">Purchase Record</a>
                            <a class="navbar-brand" href="printsales.php">Print Sales Record</a>
                            <a class="navbar-brand" href="printpurchase.php">Print Purchase Record</a>
                            <a class="navbar-brand" href="companydata.php">Company Data</a>
                        </div>
                    </nav>


                    <hr>
                </div>
                <div class="space-pad"></div>
                <div class="space-pad"></div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-12">
                            <center>
                                <h2>Purchase</h2>
                                <form method="post">
                                    <div class="input-group1">
                                        <label for="companyname" class="custom-label1">Company Name</label>
                                        <select class="custom-input1" id="companyname" name="companyname" required>
                                            <option value="" disabled selected>Select or search company name</option>
                                            <?php
                                            foreach ($companynames as $name) {
                                                echo "<option value='$name'>$name</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="input-group">
                                        <label for="gst" class="custom-label">GST Number</label>
                                        <input type="text" class="custom-input" id="gst" name="gst" placeholder="GST Number will be filled" readonly required>
                                    </div>

                                    <div class="input-group">
                                        <label for="bill" class="custom-label">Bill No</label>
                                        <input type="number" class="custom-input" name="bill" placeholder="Enter your Bill No" required>
                                    </div>

                                    <div class="input-group">
                                        <label for="sales-date" class="custom-label">Date</label>
                                        <input type="text" class="custom-input" name="date" id="sales-date" placeholder="DD-MM-YYYY" required>
                                    </div>

                                    <div class="input-group">
                                        <label for="amt" class="custom-label">Taxable Amount</label>
                                        <input type="text" class="custom-input" name="amt" placeholder="Enter your taxable amount" required>
                                    </div>
                                    <!-- GST Type radio buttons -->
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="type" id="tngst" value="tngst"> TNGST
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="type" id="igst" value="igst"> IGST
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="type" id="twentyFiveP" value="25p"> 25p
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="type" id="sixP" value="6p"> 6p
                                        </label>
                                    </div>

                                    <div class="space-pad"></div>
                                    <div>
                                        <button class="btn btn-secondary" formaction="save1.php">Save</button>
                                    </div><br>
                                </form>
                            </center>
                        </div>
                        <!-- <div class="col-sm-6 vertical-line">
              <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
              <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
              <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
              <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
              <center>
                <h3>Purchase</h3>
                <form method="post">
                  <div class="input-group mb-3">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Company Name</span>
                      </div>
                      <select class="form-control" id="companyname" name="companyname" aria-describedby="basic-addon1" required>
                        <option value="" disabled selected>Select or search company name</option>
            
                      </select>
                    </div>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon2">GST Number</span>
                      </div>
                      <input type="text" class="form-control" id="gst" name="gst" placeholder="GST Number will be filled" aria-describedby="basic-addon2" readonly required>
                    </div>

                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Bill No</span>
                      </div>
                      <input type="number" class="form-control" name="bill" placeholder="Enter your Bill No" aria-describedby="basic-addon1" required>
                    </div>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Date</span>
                      </div>
                      <input type="text" class="form-control" name="date" id="purchase-date" placeholder="DD-MM-YYYY" aria-describedby="basic-addon1" required>
                    </div>
                    <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">Taxable amount</span>
                      </div>
                      <input type="text" class="form-control" name="amt" placeholder="Enter your taxable amount" aria-describedby="basic-addon1" required>
                    </div>
                    <div class="form-check form-check-inline">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="type" value="tngst">TNGST
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="type" value="igst">IGST
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="type" value="25p">0.25%
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="type" value="6p">0.6%
                      </label>
                    </div>
                    <div class="space-pad"></div>
                    <div>
                      
                    </div><br>
                </form>
              </center>
            </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#companyname').on('change', function() {
                var companyname = $(this).val();
                console.log("Selected company: " + companyname); // Logs selected company name
            });

            // Initialize Select2
            $('#companyname').select2({
                placeholder: "Select or search company name",
                allowClear: true
            });

            // AJAX to fetch GST number and GST type when a company is selected
            $('#companyname').on('change', function() {
                var companyname = $(this).val();

                // Send AJAX request to fetch the GST number and GST type
                $.ajax({
                    type: 'POST',
                    url: 'get_gst_number.php', // Path to the PHP file that fetches GST number and type
                    data: {
                        companyname: companyname
                    },
                    success: function(response) {
                        // Parse the JSON response
                        var data = JSON.parse(response);

                        // Populate the GST number input field
                        $('#gst').val(data.gstno);

                        // Select the correct GST type radio button
                        // Check the data received and set the appropriate radio button
                        $('input[name="type"]').prop('checked', false); // Clear previous selections
                        if (data.gsttype === 'tngst') {
                            $('#tngst').prop('checked', true);
                        } else if (data.gsttype === 'igst') {
                            $('#igst').prop('checked', true);
                        } else if (data.gsttype === '25p') {
                            $('#twentyFiveP').prop('checked', true);
                        } else if (data.gsttype === '6p') {
                            $('#sixP').prop('checked', true);
                        }
                    }
                });
            });
        });


        function formatDateInput(inputId) {
            document.getElementById(inputId).addEventListener('blur', function(event) {
                let input = event.target;
                let value = input.value;

                // Match the entered value with DD-MM-YYYY format
                let datePattern = /^(\d{2})-(\d{2})-(\d{4})$/;
                if (!datePattern.test(value)) {
                    alert("Please enter the date in DD-MM-YYYY format.");
                    input.value = '';
                    input.focus();
                    return;
                }

                let parts = value.split('-');
                let day = parts[0];
                let month = parts[1];
                let year = parts[2];

                // Check for valid day, month, and year
                if (day < 1 || day > 31 || month < 1 || month > 12 || year.length !== 4) {
                    alert("Invalid date. Please enter a valid date in DD-MM-YYYY format.");
                    input.value = '';
                    input.focus();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            formatDateInput('sales-date');
            formatDateInput('purchase-date');
        });
    </script>

</body>

</html>