<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .edit-row {
            background-color: #f8f9fa;
        }
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">Product Management System</h1>

                <div id="alert-container"></div>

                <!-- Product Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Add New Product</h5>
                    </div>
                    <div class="card-body">
                        <form id="productForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback" id="name-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity in Stock</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                                        <div class="invalid-feedback" id="quantity-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price per Item</label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" required>
                                        <div class="invalid-feedback" id="price-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary d-block w-100">
                                            <i class="fas fa-plus"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Products List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Quantity in Stock</th>
                                        <th>Price per Item</th>
                                        <th>DateTime Submitted</th>
                                        <th>Total Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="products-table-body">
                                    @foreach($products as $product)
                                    <tr data-id="{{ $product->id }}">
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->quantity }}</td>
                                        <td>${{ number_format($product->price, 2) }}</td>
                                        <td>{{ $product->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>${{ number_format($product->total_value, 2) }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-edit" data-id="{{ $product->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-delete" data-id="{{ $product->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="4" class="text-end"><strong>Total Sum:</strong></td>
                                        <td><strong id="total-sum">${{ number_format($totalSum, 2) }}</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // CSRF Token Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Add Product Form Submission
            $('#productForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    name: $('#name').val(),
                    quantity: $('#quantity').val(),
                    price: $('#price').val()
                };

                $.ajax({
                    url: '{{ route("products.store") }}',
                    method: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $('body').addClass('loading');
                        clearErrors();
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            updateTable(response.products, response.totalSum);
                            $('#productForm')[0].reset();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            showValidationErrors(xhr.responseJSON.errors);
                        } else {
                            showAlert('danger', 'An error occurred. Please try again.');
                        }
                    },
                    complete: function() {
                        $('body').removeClass('loading');
                    }
                });
            });

            // Edit Product
            $(document).on('click', '.btn-edit', function() {
                const row = $(this).closest('tr');
                const productId = $(this).data('id');

                if (row.hasClass('edit-row')) {
                    saveProduct(productId, row);
                } else {
                    enterEditMode(row);
                }
            });

            // Delete Product
            $(document).on('click', '.btn-delete', function() {
                const productId = $(this).data('id');

                if (confirm('Are you sure you want to delete this product?')) {
                    $.ajax({
                        url: `/products/${productId}`,
                        method: 'DELETE',
                        beforeSend: function() {
                            $('body').addClass('loading');
                        },
                        success: function(response) {
                            if (response.success) {
                                showAlert('success', response.message);
                                updateTable(response.products, response.totalSum);
                            }
                        },
                        error: function() {
                            showAlert('danger', 'An error occurred. Please try again.');
                        },
                        complete: function() {
                            $('body').removeClass('loading');
                        }
                    });
                }
            });

            // Cancel Edit
            $(document).on('click', '.btn-cancel', function() {
                location.reload();
            });

            function enterEditMode(row) {
                const cells = row.find('td');
                const productId = row.data('id');

                // Make cells editable
                cells.eq(0).html(`<input type="text" class="form-control form-control-sm" value="${cells.eq(0).text()}">`);
                cells.eq(1).html(`<input type="number" class="form-control form-control-sm" value="${cells.eq(1).text()}" min="1">`);
                cells.eq(2).html(`<input type="number" class="form-control form-control-sm" value="${cells.eq(2).text().replace('$', '')}" step="0.01" min="0.01">`);

                // Change buttons
                cells.eq(5).html(`
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-success btn-edit" data-id="${productId}">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-secondary btn-cancel">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);

                row.addClass('edit-row');
            }

            function saveProduct(productId, row) {
                const cells = row.find('td');
                const formData = {
                    name: cells.eq(0).find('input').val(),
                    quantity: cells.eq(1).find('input').val(),
                    price: cells.eq(2).find('input').val()
                };

                $.ajax({
                    url: `/products/${productId}`,
                    method: 'PUT',
                    data: formData,
                    beforeSend: function() {
                        $('body').addClass('loading');
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            updateTable(response.products, response.totalSum);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            showAlert('danger', 'Please check your input values.');
                        } else {
                            showAlert('danger', 'An error occurred. Please try again.');
                        }
                    },
                    complete: function() {
                        $('body').removeClass('loading');
                    }
                });
            }

            function updateTable(products, totalSum) {
                const tbody = $('#products-table-body');
                tbody.empty();

                products.forEach(function(product) {
                    const row = `
                        <tr data-id="${product.id}">
                            <td>${product.name}</td>
                            <td>${product.quantity}</td>
                            <td>$${parseFloat(product.price).toFixed(2)}</td>
                            <td>${new Date(product.created_at).toLocaleString()}</td>
                            <td>$${parseFloat(product.total_value).toFixed(2)}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-edit" data-id="${product.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-delete" data-id="${product.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                $('#total-sum').text(`$${parseFloat(totalSum).toFixed(2)}`);
            }

            function showAlert(type, message) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#alert-container').html(alertHtml);

                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            }

            function showValidationErrors(errors) {
                clearErrors();

                Object.keys(errors).forEach(function(field) {
                    const input = $(`#${field}`);
                    const errorDiv = $(`#${field}-error`);

                    input.addClass('is-invalid');
                    errorDiv.text(errors[field][0]);
                });
            }

            function clearErrors() {
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            }
        });
    </script>
</body>
</html>
