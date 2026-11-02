<?php
require_once __DIR__ . '/config/db.php';

$page = 'customers';
$successMessage = '';
$errorMessage = '';
$formName = '';
$formPhone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionValue = $_POST['action'] ?? '';
    $action = is_string($actionValue) ? $actionValue : '';

    if ($action === 'add') {
        $nameValue = $_POST['name'] ?? '';
        $phoneValue = $_POST['phone'] ?? '';
        $formName = is_string($nameValue) ? trim($nameValue) : '';
        $formPhone = is_string($phoneValue) ? trim($phoneValue) : '';

        $nameLength = function_exists('mb_strlen')
            ? mb_strlen($formName, 'UTF-8')
            : strlen($formName);
        $phoneDigits = preg_replace('/\D/', '', $formPhone);

        if ($formName === '') {
            $errorMessage = 'Enter the customer name.';
        } elseif ($nameLength > 100) {
            $errorMessage = 'The customer name must be 100 characters or fewer.';
        } elseif (
            $formPhone === '' ||
            strlen($formPhone) > 15 ||
            !preg_match('/^[0-9+(). -]+$/', $formPhone) ||
            strlen($phoneDigits) < 7
        ) {
            $errorMessage = 'Enter a valid phone number with at least 7 digits and no more than 15 characters.';
        } else {
            $phoneCheck = $conn->prepare('SELECT customer_id FROM customers WHERE phone = ? LIMIT 1');

            if (!$phoneCheck) {
                $errorMessage = 'Could not check the phone number.';
            } else {
                $phoneCheck->bind_param('s', $formPhone);

                if (!$phoneCheck->execute()) {
                    $errorMessage = 'Could not check the phone number.';
                } else {
                    $phoneCheck->store_result();

                    if ($phoneCheck->num_rows > 0) {
                        $errorMessage = 'That phone number is already in use.';
                    }
                }

                $phoneCheck->close();
            }

            if ($errorMessage === '') {
                $insertCustomer = $conn->prepare('INSERT INTO customers (name, phone) VALUES (?, ?)');

                if (!$insertCustomer) {
                    $errorMessage = 'Could not prepare the customer record.';
                } else {
                    $insertCustomer->bind_param('ss', $formName, $formPhone);

                    if ($insertCustomer->execute()) {
                        $successMessage = 'Customer added successfully.';
                        $formName = '';
                        $formPhone = '';
                    } elseif ($insertCustomer->errno === 1062) {
                        $errorMessage = 'That phone number is already in use.';
                    } else {
                        $errorMessage = 'Could not add the customer.';
                    }

                    $insertCustomer->close();
                }
            }
        }
    } elseif ($action === 'delete') {
        $customerIdValue = $_POST['customer_id'] ?? '';
        $customerId = is_string($customerIdValue)
            ? filter_var($customerIdValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;

        if ($customerId === false) {
            $errorMessage = 'Select a valid customer to delete.';
        } else {
            $orderCheck = $conn->prepare('SELECT COUNT(*) FROM orders WHERE customer_id = ?');

            if (!$orderCheck) {
                $errorMessage = 'Could not check this customer’s order history.';
            } else {
                $orderCheck->bind_param('i', $customerId);

                if (!$orderCheck->execute()) {
                    $errorMessage = 'Could not check this customer’s order history.';
                } else {
                    $orderCheck->bind_result($orderCount);
                    $orderCheck->fetch();
                }

                $orderCheck->close();

                if ($errorMessage === '' && $orderCount > 0) {
                    $errorMessage = 'This customer has orders and cannot be deleted.';
                } elseif ($errorMessage === '') {
                    $deleteCustomer = $conn->prepare('DELETE FROM customers WHERE customer_id = ?');

                    if (!$deleteCustomer) {
                        $errorMessage = 'Could not prepare the customer deletion.';
                    } else {
                        $deleteCustomer->bind_param('i', $customerId);

                        if ($deleteCustomer->execute() && $deleteCustomer->affected_rows === 1) {
                            $successMessage = 'Customer deleted successfully.';
                        } else {
                            $errorMessage = 'The customer was not deleted. They may have an order or may no longer exist.';
                        }

                        $deleteCustomer->close();
                    }
                }
            }
        }
    } else {
        $errorMessage = 'Choose a valid customer action.';
    }
}

$customers = $conn->query(
    'SELECT customers.customer_id, customers.name, customers.phone,
            COUNT(orders.order_id) AS order_count
     FROM customers
     LEFT JOIN orders ON orders.customer_id = customers.customer_id
     GROUP BY customers.customer_id, customers.name, customers.phone
     ORDER BY customers.name ASC'
);

include __DIR__ . '/includes/header.php';
?>

<?php if ($successMessage !== ''): ?>
    <p role="status"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php if ($errorMessage !== ''): ?>
    <p role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<section class="card">
    <h2>Add a customer</h2>
    <form method="post" action="customers.php">
        <input type="hidden" name="action" value="add">

        <div class="form-grid">
            <div class="form-field">
                <label for="name">Name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    maxlength="100"
                    value="<?= htmlspecialchars($formName, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-field">
                <label for="phone">Phone</label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    maxlength="15"
                    value="<?= htmlspecialchars($formPhone, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>
        </div>

        <p>
            <button class="button button-primary" type="submit">Add customer</button>
        </p>
    </form>
</section>

<h2>Customers</h2>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Orders</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($customers && $customers->num_rows > 0): ?>
                <?php while ($customer = $customers->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $customer['customer_id'] ?></td>
                        <td><?= htmlspecialchars($customer['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($customer['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $customer['order_count'] ?></td>
                        <td>
                            <?php if ((int) $customer['order_count'] === 0): ?>
                                <form method="post" action="customers.php" onsubmit="return confirm('Delete this customer?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="customer_id" value="<?= (int) $customer['customer_id'] ?>">
                                    <button class="button button-danger" type="submit">Delete</button>
                                </form>
                            <?php else: ?>
                                Has order history
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php elseif ($customers): ?>
                <tr>
                    <td colspan="5">No customers found.</td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="5">Customers could not be loaded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
