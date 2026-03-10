<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Setup</title>

    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #e67e22;
            --secondary-dark: #d35400;
            --success: #2ecc71;
            --dark: #2c3e50;
            --gray: #7f8c8d;
            --light: #ecf0f1;
            --bg-light: #f9fafb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-light);
            color: var(--dark);
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 2rem;
        }

        .shop-card {
            background: white;
            padding: 3rem;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .shop-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .shop-header h2 {
            font-size: 2rem;
            margin-bottom: 0.4rem;
        }

        .shop-header p {
            color: var(--gray);
        }

        .form-section {
            margin-bottom: 2.5rem;
        }

        .form-section h3 {
            margin-bottom: 1rem;
            color: var(--primary-dark);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        input,
        select,
        textarea {
            padding: 0.7rem;
            border-radius: 6px;
            border: 1px solid #dcdfe4;
            font-size: 0.95rem;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            outline: none;
        }

        .submit-btn {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 8px;
            background: var(--secondary);
            color: white;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: var(--secondary-dark);
        }
    </style>
</head>

<body>

    <section class="shop-setup">

        <div class="container">

            <div class="shop-card">

                <div class="shop-header">
                    <h2>Set Up Your Shop</h2>
                    <p>Complete your shop profile to start selling on the platform.</p>
                </div>

                <form method="POST" enctype="multipart/form-data">

                    <!-- Shop Information -->
                    <div class="form-section">

                        <h3>Shop Information</h3>

                        <div class="form-grid">

                            <div class="form-group">
                                <label>Store Name *</label>
                                <input type="text" name="store_name" placeholder="Enter store name" required>
                            </div>

                            <div class="form-group">
                                <label>Store Category *</label>
                                <select name="category" required>
                                    <option value="">Select Category</option>
                                    <option>Food</option>
                                    <option>Groceries</option>
                                    <option>Milk Tea</option>
                                    <option>Bakery</option>
                                    <option>Pharmacy</option>
                                    <option>Hardware</option>
                                </select>
                            </div>

                            <div class="form-group full">
                                <label>Store Description *</label>
                                <textarea name="description" placeholder="Describe your store..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Contact Number *</label>
                                <input type="text" name="contact" placeholder="09XXXXXXXXX" required>
                            </div>

                            <div class="form-group">
                                <label>Open Time</label>
                                <input type="time" name="open_time">
                            </div>

                            <div class="form-group">
                                <label>Close Time</label>
                                <input type="time" name="close_time">
                            </div>

                        </div>

                    </div>


                    <!-- Store Address -->
                    <div class="form-section">

                        <h3>Store Address</h3>

                        <div class="form-grid">

                            <div class="form-group">
                                <label>Street *</label>
                                <input type="text" name="street" required>
                            </div>

                            <div class="form-group">
                                <label>Barangay *</label>
                                <input type="text" name="barangay" required>
                            </div>

                            <div class="form-group">
                                <label>City *</label>
                                <input type="text" name="city" value="Dumaguete City" required>
                            </div>

                        </div>

                    </div>


                    <!-- Shop Media -->
                    <div class="form-section">

                        <h3>Shop Media</h3>

                        <div class="form-grid">

                            <div class="form-group">
                                <label>Store Logo</label>
                                <input type="file" name="logo">
                            </div>

                            <div class="form-group">
                                <label>Store Banner</label>
                                <input type="file" name="banner">
                            </div>

                        </div>

                    </div>


                    <!-- Shop Verification -->
                    <div class="form-section">

                        <h3>Shop Verification</h3>

                        <div class="form-grid">

                            <div class="form-group">
                                <label>Owner Full Name *</label>
                                <input type="text" name="owner_name" required>
                            </div>

                            <div class="form-group">
                                <label>Valid ID Type *</label>
                                <select name="id_type" required>
                                    <option value="">Select ID</option>
                                    <option>National ID</option>
                                    <option>Driver's License</option>
                                    <option>Passport</option>
                                    <option>Student ID</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Upload Valid ID *</label>
                                <input type="file" name="valid_id" required>
                            </div>

                            <div class="form-group">
                                <label>Upload Store Photo *</label>
                                <input type="file" name="store_photo" required>
                            </div>

                        </div>

                    </div>


                    <button type="submit" class="submit-btn">
                        Save Shop Information
                    </button>

                </form>

            </div>

        </div>

    </section>

</body>

</html>