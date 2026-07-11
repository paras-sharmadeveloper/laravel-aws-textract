<!DOCTYPE html>
<html>

<head>
    <title>Submit DPS Payments</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('logo.jpeg') }}" rel="icon">
    <style>
        :root {
            --primary: #4a6cf7;
            --primary-dark: #3956d8;
            --primary-soft: #eef1fe;
            --ink: #1c2333;
            --muted: #6b7280;
            --border: #e2e5ec;
            --bg: #f4f6f9;
            --success-bg: #e8f7ee;
            --success-ink: #1a7f4c;
            --error-bg: #fdecec;
            --error-ink: #b3261e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 32px 16px;
            color: var(--ink);
        }

        .page {
            max-width: 720px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(20, 30, 60, 0.06);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .card-header {
            text-align: center;
            padding: 36px 32px 28px;
            border-bottom: 1px solid var(--border);
        }

        .card-header img {
            width: 96px;
            height: auto;
            margin-bottom: 14px;
            border-radius: 8px;
        }

        .card-header h1 {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 700;
        }

        .card-header p {
            margin: 0;
            font-size: 14px;
            color: var(--muted);
        }

        .card-body {
            padding: 28px 32px 32px;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .alert svg {
            flex: none;
            margin-top: 1px;
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success-ink);
        }

        .alert-error {
            background: var(--error-bg);
            color: var(--error-ink);
        }

        .section {
            margin-bottom: 30px;
        }

        .section:last-of-type {
            margin-bottom: 0;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .section-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            flex: none;
        }

        .section-title h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
        }

        .section-title span.optional {
            font-size: 12px;
            font-weight: 400;
            color: var(--muted);
            margin-left: 4px;
        }

        .row {
            display: flex;
            gap: 14px;
        }

        .row .form-group {
            flex: 1;
            min-width: 0;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        label.field-label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
            font-size: 13.5px;
        }

        label.field-label .req {
            color: var(--primary);
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            background: #fbfbfd;
            transition: border-color .15s, box-shadow .15s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
            background: white;
        }

        .phone-input {
            display: flex;
        }

        .phone-prefix {
            display: flex;
            align-items: center;
            background: #f0f1f5;
            border: 1px solid var(--border);
            border-right: none;
            border-radius: 8px 0 0 8px;
            padding: 0 12px;
            font-size: 14px;
            color: var(--muted);
            font-weight: 600;
            white-space: nowrap;
        }

        .phone-input input {
            border-radius: 0 8px 8px 0;
        }

        small.hint {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 8px;
        }

        /* File upload widget */
        .dropzone {
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1.5px dashed var(--border);
            border-radius: 10px;
            padding: 14px;
            background: #fbfbfd;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            position: relative;
        }

        .dropzone:hover {
            border-color: var(--primary);
            background: var(--primary-soft);
        }

        .dropzone.has-file {
            border-style: solid;
            border-color: var(--primary);
            background: var(--primary-soft);
        }

        .dropzone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .dropzone-icon {
            flex: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: white;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .dropzone-text {
            font-size: 13.5px;
            min-width: 0;
        }

        .dropzone-text .primary {
            font-weight: 600;
            color: var(--ink);
        }

        .dropzone-text .filename {
            display: block;
            color: var(--primary);
            font-weight: 600;
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dropzone-text .filehint {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-top: 2px;
        }

        hr.divider {
            margin: 28px 0;
            border: none;
            border-top: 1px solid var(--border);
        }

        button.submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .15s;
        }

        button.submit-btn:hover {
            background: var(--primary-dark);
        }

        button.submit-btn:disabled {
            background: #a7b0c0;
            cursor: not-allowed;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-top-color: white;
            display: none;
            animation: spin .7s linear infinite;
        }

        button.submit-btn.loading .spinner {
            display: inline-block;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .footnote {
            text-align: center;
            font-size: 12px;
            color: var(--muted);
            margin-top: 14px;
        }

        @media (max-width:600px) {
            body {
                padding: 16px 10px;
            }

            .card-header {
                padding: 28px 20px 22px;
            }

            .card-body {
                padding: 22px 20px 26px;
            }

            .row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>

<body>

    <div class="page">
        <div class="card">
            <div class="card-header">
                <img src="{{ asset('logo.jpeg') }}" alt="Logo">
                <h1>Submit DPS Payments</h1>
                <p>Upload your documents here</p>
            </div>

            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form method="POST" action="/upload" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <div class="section">
                        <div class="section-title">
                            <div class="section-badge">1</div>
                            <h2>Contact Information</h2>
                        </div>

                        <div class="row">
                            <div class="form-group">
                                <label class="field-label">Email <span class="req">*</span></label>
                                <input type="email" name="email" required placeholder="you@example.com">
                            </div>

                            <div class="form-group">
                                <label class="field-label">Phone <span class="req">*</span></label>
                                <div class="phone-input">
                                    <span class="phone-prefix">+1</span>
                                    <input type="text" name="phone" required maxlength="10" pattern="[0-9]{10}"
                                        placeholder="10-digit phone" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="divider">

                    <div class="section">
                        <div class="section-title">
                            <div class="section-badge">2</div>
                            <h2>Identity &amp; Business Documents</h2>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Owner ID: Drivers License / Passport <span class="req">*</span></label>
                            <label class="dropzone" data-dropzone>
                                <input type="file" name="driving_license" accept=".pdf,.jpg,.jpeg,.png,.heic">
                                <span class="dropzone-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="12" r="2"/><path d="M15 10h3M15 14h3"/></svg>
                                </span>
                                <span class="dropzone-text">
                                    <span class="primary">Click to upload</span>
                                    <span class="filename" data-filename></span>
                                    <span class="filehint" data-hint>PDF, JPG, PNG, HEIC • Max 10MB</span>
                                </span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Void Check / Bank Letter <span class="req">*</span></label>
                            <label class="dropzone" data-dropzone>
                                <input type="file" name="bank_doc" accept=".pdf,.jpg,.jpeg,.png,.heic">
                                <span class="dropzone-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M2 10h20"/></svg>
                                </span>
                                <span class="dropzone-text">
                                    <span class="primary">Click to upload</span>
                                    <span class="filename" data-filename></span>
                                    <span class="filehint" data-hint>PDF, JPG, PNG, HEIC • Max 10MB</span>
                                </span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Business ID: EIN / Tax ID</label>
                            <label class="dropzone" data-dropzone>
                                <input type="file" name="tax_doc" accept=".pdf,.jpg,.jpeg,.png,.heic">
                                <span class="dropzone-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 001 1h4"/><path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/></svg>
                                </span>
                                <span class="dropzone-text">
                                    <span class="primary">Click to upload</span>
                                    <span class="filename" data-filename></span>
                                    <span class="filehint" data-hint>PDF, JPG, PNG, HEIC • Max 10MB</span>
                                </span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Statements: Bank / CCP / POS</label>
                            <label class="dropzone" data-dropzone>
                                <input type="file" name="bank_statement" accept=".pdf,.jpg,.jpeg,.png,.heic">
                                <span class="dropzone-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 001 1h4"/><path d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/><path d="M9 13h6M9 17h6"/></svg>
                                </span>
                                <span class="dropzone-text">
                                    <span class="primary">Click to upload</span>
                                    <span class="filename" data-filename></span>
                                    <span class="filehint" data-hint>PDF, JPG, PNG, HEIC • Max 10MB</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <hr class="divider">

                    <div class="section">
                        <div class="section-title">
                            <div class="section-badge">3</div>
                            <h2>Additional Documents <span class="optional">(optional)</span></h2>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Pictures: POS / Store</label>
                            <label class="dropzone" data-dropzone>
                                <input type="file" name="pictures[]" multiple accept=".pdf,.jpg,.jpeg,.png,.heic">
                                <span class="dropzone-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L5 19"/></svg>
                                </span>
                                <span class="dropzone-text">
                                    <span class="primary">Click to upload</span>
                                    <span class="filename" data-filename></span>
                                    <span class="filehint" data-hint>Multiple files allowed</span>
                                </span>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Other / Supporting Documents</label>
                            <label class="dropzone" data-dropzone>
                                <input type="file" name="other_doc[]" multiple accept=".pdf,.jpg,.jpeg,.png,.heic">
                                <span class="dropzone-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a5 5 0 01-7.07-7.07l9.19-9.19a3.5 3.5 0 015 5l-9.2 9.19a1.5 1.5 0 01-2.12-2.12l8.49-8.48"/></svg>
                                </span>
                                <span class="dropzone-text">
                                    <span class="primary">Click to upload</span>
                                    <span class="filename" data-filename></span>
                                    <span class="filehint" data-hint>Multiple files allowed</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <hr class="divider">

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <span class="spinner"></span>
                        <span class="btn-text">Upload &amp; Process</span>
                    </button>
                    <p class="footnote">Processing typically completes within ~2 minutes of upload.</p>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show selected filename(s) inside each dropzone
        document.querySelectorAll('[data-dropzone]').forEach(function(zone) {
            const input = zone.querySelector('input[type="file"]');
            const filenameEl = zone.querySelector('[data-filename]');
            const hintEl = zone.querySelector('[data-hint]');
            const defaultHint = hintEl.textContent;

            input.addEventListener('change', function() {
                if (!input.files.length) {
                    zone.classList.remove('has-file');
                    filenameEl.textContent = '';
                    hintEl.textContent = defaultHint;
                    return;
                }

                zone.classList.add('has-file');

                if (input.files.length === 1) {
                    filenameEl.textContent = input.files[0].name;
                } else {
                    filenameEl.textContent = input.files.length + ' files selected';
                }

                hintEl.textContent = 'Click to change';
            });
        });

        // Validate first, then show the loading state — only if validation passes
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
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

            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            btn.querySelector('.btn-text').textContent = 'Processing...';
            btn.disabled = true;
        });
    </script>

</body>

</html>
