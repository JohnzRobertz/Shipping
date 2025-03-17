<?php include 'views/layout/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Import Shipments</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                                echo $_SESSION['success']; 
                                unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                                echo $_SESSION['error']; 
                                unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h5>Import Options</h5>
                        <p>Choose the type of import you want to perform:</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Create New Shipments</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Use this option to create new shipments in the system.</p>
                                        <p><strong>Required fields:</strong> tracking_number, sender_name, receiver_name, weight, transport_type</p>
                                        <p><strong>Optional fields:</strong> sender_contact, sender_phone, receiver_contact, receiver_phone, length, width, height, customer_code, description, price, lot_id</p>
                                        <a href="index.php?page=shipments&action=download_template&type=create" class="btn btn-outline-primary">Download Template</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">Update Existing Shipments</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Use this option to update existing shipments in the system.</p>
                                        <p><strong>Required fields:</strong> tracking_number</p>
                                        <p><strong>Optional fields:</strong> lot_id, domestic_carrier, domestic_tracking_number, handover_date, status</p>
                                        <a href="index.php?page=shipments&action=download_template&type=update" class="btn btn-outline-success">Download Template</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="index.php?page=shipments&action=process_import" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="import_type" class="form-label">Import Type</label>
                            <select name="import_type" id="import_type" class="form-select" required>
                                <option value="">Select Import Type</option>
                                <option value="create">Create New Shipments</option>
                                <option value="update">Update Existing Shipments</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">CSV File</label>
                            <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                            <div class="form-text">
                                Please upload a CSV file with the required fields. You can download a template above.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Import</button>
                            <a href="index.php?page=shipments" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h5>Import Guidelines</h5>
                        <ul>
                            <li>The CSV file must have a header row with the field names.</li>
                            <li>The tracking_number field is required and must be unique for new shipments.</li>
                            <li>For updating shipments, the tracking_number must match an existing shipment.</li>
                            <li>Dates should be in YYYY-MM-DD format.</li>
                            <li>Numeric fields (weight, dimensions, price) should use a period (.) as the decimal separator.</li>
                            <li>The transport_type field should contain the ID of the transport type.</li>
                            <li>The lot_id field should contain the ID of the lot.</li>
                            <li>Valid status values are: pending, processing, in_transit, local_delivery, delivered, cancelled.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>

