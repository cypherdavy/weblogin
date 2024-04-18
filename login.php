<?php
// config.php
$host = "localhost";
$user = "username";
$password = "password";
$dbname = "hackers_db";

// connect to the database
$conn = new mysqli($host, $user, $password, $dbname);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// register.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field
    $name = $_POST["name"];
    $pass = $_POST["pass"];

    // create a unique salt for the password
    $salt = bin2hex(random_bytes(32));
    $hashed_pass = hash_hmac('sha256', $pass, $salt);

    // insert data into database
    $sql = "INSERT INTO users (name, pass, salt) VALUES ('$name', '$hashed_pass', '$salt')";
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        Name: <input type="text" name="name">
        Password: <input type="password" name="pass">
        <input type="submit">
    </form>
</body>
</html>

// login.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // collect value of input field
    $name = $_POST["name"];
    $pass = $_POST["pass"];

    // get the salt for the user
    $sql = "SELECT salt FROM users WHERE name='$name'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $salt = $row["salt"];

    // hash the password with the salt
    $hashed_pass = hash_hmac('sha256', $pass, $salt);

    // check if the password is correct
    $sql = "SELECT * FROM users WHERE name='$name' AND pass='$hashed_pass'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "You are now logged in";
    } else {
        echo "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        Name: <input type="text" name="name">
        Password: <input type="password" name="pass">
        <input type="submit">
    </form>
</body>
</html>

// upload.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get the uploaded file
    $file = $_FILES["file"];

    // get the file name
    $filename = basename($file["name"]);

    // get the file type
    $filetype = pathinfo($filename, PATHINFO_EXTENSION);

    // only allow certain file types
    $allowed_types = array("php", "py", "exe", "js");
    if (!in_array($filetype, $allowed_types)) {
        die("Invalid file type");
    }

    // move the file to the uploads directory
    $target_dir = "uploads/";
    $target_file = $target_dir . $filename;
    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        die("Error uploading file");
    }

    // insert data into database
    $sql = "INSERT INTO files (name, type) VALUES ('$filename', '$filetype')";
    if ($conn->query($sql) === TRUE) {
        echo "File uploaded successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload</title>
</head>
<body>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        Select file to upload:
        <input type="file" name="file">
        <input type="submit" value="Upload File" name="submit">
    </form>
</body>
</html>
