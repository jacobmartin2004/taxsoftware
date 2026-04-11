<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Delvin Diamond Tools Industries</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <style>
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
      padding: 10px 15px; /* Top, right, bottom, left */
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
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideInLeft {
      from { transform: translateX(-100%); }
      to { transform: translateX(0); }
    }
  </style>
</head>
<body>
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
              <a class="navbar-brand" href="../index.php">Home</a>
              <a class="navbar-brand" href="view.php">Sales Record</a>
              <a class="navbar-brand" href="view1.php">Purchase Record</a>
              <a class="navbar-brand" href="printsales.php">Print Sales Record</a>
              <a class="navbar-brand" href="printpurchase.php">Print Purchase Record</a>
        </div>
        <div class="space-pad"></div>
        <div class="space-pad"></div>
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1">GST Number</span>
                    </div>
                    <input type="text" class="form-control" name="gst" placeholder="Enter your GST Name" aria-describedby="basic-addon2" required>
                  </div>
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1">Company Name</span>
                    </div>
                    <input type="text" class="form-control" name="name" placeholder="Enter your Company Name" aria-describedby="basic-addon1" required>
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
                    <input type="text" class="form-control" name="date" id="sales-date" placeholder="DD-MM-YYYY" aria-describedby="basic-addon1" required>
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
                  <div class="space-pad"></div>
                  <div>
                    <button class="btn btn-secondary" formaction="save.php">Save</button>
                  </div><br>
                </form>      
              </center>
            </div> 
           
            <div class="col-lg-6 col-md-6 col-sm-12 vertical-line">
            <center>
                <h3>Purchase</h3>
                <form method="post">
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1">GST Number</span>
                    </div>
                    <input type="text" class="form-control" name="gst" placeholder="Enter your GST Name" aria-describedby="basic-addon2" required>
                  </div>
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1">Company Name</span>
                    </div>
                    <input type="text" class="form-control" name="name" placeholder="Enter your Company Name" aria-describedby="basic-addon1" required>
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
                    <button class="btn btn-secondary" formaction="save1.php">Save</button>
                  </div><br>
                </form> 
              </center>
            </div>
          </div>
        </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>
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
