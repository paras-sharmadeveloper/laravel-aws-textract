<!DOCTYPE html>
<html>

<head>
    <title>Submit DPS Payments</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('logo.jpeg') }}" rel="icon">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: white;
            max-width: 750px;
            width: 100%;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        h2 {
            margin-bottom: 15px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        input[type="file"] {
            padding: 8px;
        }

        small {
            color: #777;
            font-size: 12px;
        }

        hr {
            margin: 25px 0;
            border: none;
            border-top: 1px solid #eee;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #4a6cf7;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #3956d8;
        }

        button:disabled {
            background: gray;
        }

        p.font-size-14 {
            font-size: 13px;
            text-decoration: underline;
        }

        /* MOBILE */
        @media (max-width:768px) {

            body {
                padding: 10px;
            }

            .card {
                padding: 20px;
                border-radius: 8px;
            }

            h2 {
                font-size: 20px;
            }

            label {
                font-size: 13px;
            }

            input {
                font-size: 14px;
            }

            button {
                font-size: 15px;
                padding: 13px;
            }

        }
    </style>
</head>

<body>

    <div class="card">

        @if (session('success'))
            <div style="background:#d4edda;color:#155724;padding:10px;margin-bottom:10px;border-radius:5px;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="background:#f8d7da;color:#721c24;padding:10px;margin-bottom:10px;border-radius:5px;">
                {{ session('error') }}
            </div>
        @endif
        <div style="text-align:center;margin-bottom:20px;">
            <img src="{{ asset('logo.jpeg') }}" alt="Plaid" width="120"
                style="vertical-align:middle;margin-left:5px;border:0;height:auto;">
        </div>
        <div class="header" style="text-align:center;margin-bottom:30px;">
            <h2>Submit DPS Payments </h2>
            <p class="font-size-14">Upload Documents Here</p>
        </div>
        <form method="POST" action="/upload" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone *</label>
                <div style="display:flex; gap:10px;">



                    <input type="text" name="phone" required maxlength="10" pattern="[0-9]{10}"
                        placeholder="Enter phone" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
            </div>

            <hr>

            <div class="form-group">
                <label>Drivers License or Passport *</label>
                <input type="file" name="driving_license" accept=".pdf,.jpg,.jpeg,.png,.heic">
                <small>PDF, JPG, PNG, HEIC only • Max 10MB</small>
            </div>

            <div class="form-group">
                <label>Voided Check / Bank Letter *</label>
                <input type="file" name="bank_doc" accept=".pdf,.jpg,.jpeg,.png,.heic">
            </div>

            <div class="form-group">
                <label>Tax Document</label>
                <input type="file" name="tax_doc" accept=".pdf,.jpg,.jpeg,.png,.heic">
            </div>

            <div class="form-group">
                <label>Bank / Processing Statement</label>
                <input type="file" name="bank_statement" accept=".pdf,.jpg,.jpeg,.png,.heic">
            </div>

            <hr>

            <div class="form-group">
                <label>Pictures (Multiple Allowed)</label>
                <input type="file" name="pictures[]" multiple accept=".pdf,.jpg,.jpeg,.png,.heic">
            </div>

            <div class="form-group">
                <label>Other / Supporting Documents</label>
                <input type="file" name="other_doc[]" multiple accept=".pdf,.jpg,.jpeg,.png,.heic">
            </div>

            <button type="submit">Upload & Process</button>
        </form>
    </div>

    <script>
        document.querySelector("form").addEventListener("submit", function() {
            const btn = document.querySelector("button");
            btn.innerText = "Processing...";
            btn.disabled = true;
        });
    </script>

</body>

</html>
<script>
    document.querySelector("form").addEventListener("submit", function(e) {
        const phone = document.querySelector("[name='phone']").value;
        const email = document.querySelector("[name='email']").value;

        const phoneRegex = /^[0-9]{10}$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!phoneRegex.test(phone)) {
            alert("Phone must be exactly 10 digits");
            e.preventDefault();
            return;
        }

        if (!emailRegex.test(email)) {
            alert("Enter a valid email address");
            e.preventDefault();
            return;
        }
    });
</script>
