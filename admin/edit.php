<?php
require_once "../models/db_Model.php";

// Handle edit operations
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $updateQuery = "UPDATE users SET 
                    first_name='$first_name', 
                    last_name='$last_name', 
                    email='$email', 
                    phone='$phone', 
                    address='$address', 
                    city='$city', 
                    state='$state', 
                    zip_code='$zip_code', 
                    is_active='$is_active',
                    updated_at=NOW()
                    WHERE user_id='$user_id'";
    
    global $connection;
    mysqli_query($connection, $updateQuery) or die(mysqli_error($connection));
    redirect_to("user_list.php");
}

if (isset($_POST['update_menu'])) {
    $menu_id = $_POST['menu_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $ingredients = $_POST['ingredients'];
    $preparation_time = $_POST['preparation_time'];
    $image_url = $_POST['image_url'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $updateQuery = "UPDATE menu SET 
                    name='$name', 
                    description='$description', 
                    category='$category', 
                    price='$price', 
                    ingredients='$ingredients', 
                    preparation_time='$preparation_time', 
                    image_url='$image_url', 
                    is_available='$is_available',
                    updated_at=NOW()
                    WHERE menu_id='$menu_id'";
    
    global $connection;
    mysqli_query($connection, $updateQuery) or die(mysqli_error($connection));
    redirect_to("menu_list.php");
}

// Get record to edit
$editData = null;
$editType = '';

if (isset($_GET['editid'])) {
    $edit_id = $_GET['editid'];
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    
    if (strpos($referer, 'user_list.php') !== false) {
        $editType = 'user';
        $sql = "SELECT * FROM users WHERE user_id = '$edit_id'";
    } elseif (strpos($referer, 'menu_list.php') !== false) {
        $editType = 'menu';
        $sql = "SELECT * FROM menu WHERE menu_id = '$edit_id'";
    }
    
    if (isset($sql)) {
        global $connection;
        $result = mysqli_query($connection, $sql);
        $editData = mysqli_fetch_array($result);
    }
}
?>

<html>
<head>
<title>Edit Record - Delicious Eats Admin</title>
<link rel="stylesheet" href="../assets/css/admin_forms.css" type="text/css" media="screen" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
<div id="mainWrapper">
  <?php include_once("../includes/header.php");?>
  <div id="pageContent">
    
    <?php if ($editType == 'user' && $editData): ?>
    <div class="form-container">
      <h2>Edit User Account</h2>
      <form action="edit.php" method="post">
      <input type="hidden" name="user_id" value="<?php echo $editData['user_id']; ?>" />
      <table class="form-table">
        <tr>
          <td>First Name *</td>
          <td><input name="first_name" type="text" value="<?php echo $editData['first_name']; ?>" class="form-input" required /></td>
        </tr>
        <tr>
          <td>Last Name *</td>
          <td><input name="last_name" type="text" value="<?php echo $editData['last_name']; ?>" class="form-input" required /></td>
        </tr>
        <tr>
          <td>Email Address *</td>
          <td><input name="email" type="email" value="<?php echo $editData['email']; ?>" class="form-input" required /></td>
        </tr>
        <tr>
          <td>Phone Number</td>
          <td><input name="phone" type="tel" value="<?php echo $editData['phone']; ?>" class="form-input" /></td>
        </tr>
        <tr>
          <td>Address</td>
          <td><textarea name="address" class="form-input" rows="3"><?php echo $editData['address']; ?></textarea></td>
        </tr>
        <tr>
          <td>City</td>
          <td><input name="city" type="text" value="<?php echo $editData['city']; ?>" class="form-input" /></td>
        </tr>
        <tr>
          <td>State</td>
          <td><input name="state" type="text" value="<?php echo $editData['state']; ?>" class="form-input" /></td>
        </tr>
        <tr>
          <td>ZIP Code</td>
          <td><input name="zip_code" type="text" value="<?php echo $editData['zip_code']; ?>" class="form-input" /></td>
        </tr>
        <tr>
          <td>Account Status</td>
          <td class="checkbox-container">
            <input type="checkbox" name="is_active" id="is_active" <?php echo $editData['is_active'] ? 'checked' : ''; ?> />
            <label for="is_active">Active Account</label>
          </td>
        </tr>
        <tr>
          <td></td>
          <td class="text-center">
            <input type="submit" name="update_user" value="Update User" class="submit-btn" />
            <a href="user_list.php" class="add-new-link" style="margin-left: 20px;">Cancel</a>
          </td>
        </tr>
      </table>
      </form>
    </div>
    
    <?php elseif ($editType == 'menu' && $editData): ?>
    <div class="form-container">
      <h2>Edit Menu Item</h2>
      <form action="edit.php" method="post">
      <input type="hidden" name="menu_id" value="<?php echo $editData['menu_id']; ?>" />
      <table class="form-table">
        <tr>
          <td>Item Name *</td>
          <td><input name="name" type="text" value="<?php echo $editData['name']; ?>" class="form-input" required /></td>
        </tr>
        <tr>
          <td>Price *</td>
          <td><input name="price" type="number" value="<?php echo $editData['price']; ?>" step="0.01" min="0" class="form-input" required /></td>
        </tr>
        <tr>
          <td>Category *</td>
          <td>
            <select name="category" class="form-input" required>
              <option value="pizza" <?php echo ($editData['category'] == 'pizza') ? 'selected' : ''; ?>>Pizza</option>
              <option value="burgers" <?php echo ($editData['category'] == 'burgers') ? 'selected' : ''; ?>>Burgers</option>
              <option value="pasta" <?php echo ($editData['category'] == 'pasta') ? 'selected' : ''; ?>>Pasta</option>
              <option value="salads" <?php echo ($editData['category'] == 'salads') ? 'selected' : ''; ?>>Salads</option>
              <option value="desserts" <?php echo ($editData['category'] == 'desserts') ? 'selected' : ''; ?>>Desserts</option>
              <option value="beverages" <?php echo ($editData['category'] == 'beverages') ? 'selected' : ''; ?>>Beverages</option>
            </select>
          </td>
        </tr>
        <tr>
          <td>Preparation Time</td>
          <td>
            <select name="preparation_time" class="form-input">
              <option value="5" <?php echo ($editData['preparation_time'] == 5) ? 'selected' : ''; ?>>5 minutes</option>
              <option value="10" <?php echo ($editData['preparation_time'] == 10) ? 'selected' : ''; ?>>10 minutes</option>
              <option value="15" <?php echo ($editData['preparation_time'] == 15) ? 'selected' : ''; ?>>15 minutes</option>
              <option value="20" <?php echo ($editData['preparation_time'] == 20) ? 'selected' : ''; ?>>20 minutes</option>
              <option value="25" <?php echo ($editData['preparation_time'] == 25) ? 'selected' : ''; ?>>25 minutes</option>
              <option value="30" <?php echo ($editData['preparation_time'] == 30) ? 'selected' : ''; ?>>30 minutes</option>
            </select>
          </td>
        </tr>
        <tr>
          <td>Description</td>
          <td><textarea name="description" class="form-input" rows="4"><?php echo $editData['description']; ?></textarea></td>
        </tr>
        <tr>
          <td>Ingredients</td>
          <td><textarea name="ingredients" class="form-input" rows="3"><?php echo $editData['ingredients']; ?></textarea></td>
        </tr>
        <tr>
          <td>Image URL</td>
          <td><input name="image_url" type="text" value="<?php echo $editData['image_url']; ?>" class="form-input" /></td>
        </tr>
        <tr>
          <td>Availability</td>
          <td class="checkbox-container">
            <input type="checkbox" name="is_available" id="is_available_edit" <?php echo $editData['is_available'] ? 'checked' : ''; ?> />
            <label for="is_available_edit">Available for order</label>
          </td>
        </tr>
        <tr>
          <td></td>
          <td class="text-center">
            <input type="submit" name="update_menu" value="Update Menu Item" class="submit-btn" />
            <a href="menu_list.php" class="add-new-link" style="margin-left: 20px;">Cancel</a>
          </td>
        </tr>
      </table>
      </form>
    </div>
    
    <?php else: ?>
    <div class="form-container">
      <h2>Record Not Found</h2>
      <div class="message error">
        <p>The requested record could not be found or the request is invalid.</p>
      </div>
      <div class="text-center">
        <a href="../admin/index.php" class="add-new-link">Back to Admin Dashboard</a>
        <a href="user_list.php" class="add-new-link" style="margin-left: 20px;">User Management</a>
        <a href="menu_list.php" class="add-new-link" style="margin-left: 20px;">Menu Management</a>
      </div>
    </div>
    <?php endif; ?>
    
  </div>
  <?php include_once("../includes/footer.php");?>
</div>
<script src="../assets/js/admin_forms.js"></script>
</body>
</html>
