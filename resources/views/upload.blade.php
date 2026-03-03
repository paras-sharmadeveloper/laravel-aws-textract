<!DOCTYPE html>
<html>
<head>
    <title>Document Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 40px;
        }

        .card {
            background: white;
            max-width: 750px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 25px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        input[type="file"] {
            padding: 6px;
        }

        small {
            color: #777;
        }

        hr {
            margin: 30px 0;
            border: none;
            border-top: 1px solid #eee;
        }

        button {
            width: 100%;
            padding: 12px;
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
    </style>
</head>
<body>

<div class="card">
    <h2>Document Processor Upload</h2>

    <form method="POST" action="/upload" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Phone *</label>
            <input type="text" name="phone" required>
        </div>

        <hr>

        <div class="form-group">
            <label>Drivers License or Passport *</label>
            <input type="file" name="driving_license" accept=".pdf,.jpg,.jpeg,.png">
            <small>PDF, JPG, PNG only • Max 10MB</small>
        </div>

        <div class="form-group">
            <label>Voided Check / Bank Letter *</label>
            <input type="file" name="bank_doc" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div class="form-group">
            <label>Tax Document</label>
            <input type="file" name="tax_doc" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div class="form-group">
            <label>Bank / Processing Statement</label>
            <input type="file" name="bank_statement" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <hr>

        <div class="form-group">
            <label>Pictures (Multiple Allowed)</label>
            <input type="file" name="pictures[]" multiple accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <div class="form-group">
            <label>Other / Supporting Documents</label>
            <input type="file" name="other_doc[]" multiple accept=".pdf,.jpg,.jpeg,.png">
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
