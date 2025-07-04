<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        {
            ! ! file_get_contents(public_path('assets/bootstrap.min.css')) ! !
        }
    </style>
</head>

<body>

    <div class="bg-white shadow rounded p-4">
        <!-- Header -->
        <div class="invoice-header mb-4"></div>

        <!-- Club Info -->
        <div class="text-center mb-4">
            <h5 class="fw-bold">Gravity Unisex Fitness Club</h5>
            <p class="text-muted mb-0">Invoice # INV2025070007</p>
        </div>

        <!-- Payment Details -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <small class="text-muted">AMOUNT PAID:</small>
                <div class="fw-bold">999.00</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">DATE PAID:</small>
                <div class="fw-bold">02 Jul, 2025 01:12 PM</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">PAYMENT METHOD:</small>
                <div class="fw-bold text-danger">
                    CASH
                </div>
            </div>
        </div>

        <!-- Summary Table -->
        <h6 class="text-uppercase text-muted">Summary</h6>
        <table class="table table-bordered table-sm mb-4">
            <tbody>
                <tr>
                    <td>Recipient Name</td>
                    <td class="text-end">Tannu Sharma</td>
                </tr>
                <tr>
                    <td>Mobile No</td>
                    <td class="text-end">7857979735</td>
                </tr>
                <tr>
                    <td>Net Amount</td>
                    <td class="text-end">0.00</td>
                </tr>
                <tr>
                    <td>Arrear Amount</td>
                    <td class="text-end">999.00</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td class="text-end">0.00</td>
                </tr>
                <tr class="fw-bold">
                    <td>Amount Paid</td>
                    <td class="text-end">999.00</td>
                </tr>
                <tr class="fw-bold text-danger">
                    <td>Due Balance</td>
                    <td class="text-end">0.00</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small>If you have any questions, contact us at
                    <a href="mailto:info@gravityfitness.com" class="text-primary">info@gravityfitness.com</a>
                    or call at
                    <a href="tel:+910123456789" class="text-primary">+91 0123456789</a>
                </small>
            </div>
        </div>
    </div>

</body>

</html>