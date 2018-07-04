<?php
$dbhost = 'localhost:3306';
$dbuser = 'root';
$dbpass = 'root';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);

if(!$conn){
    die('Could not connect: ' . mysqli_error());
}
echo 'Connect successfully';
mysqli_select_db($conn, 'test');
$query = 'SELECT * FROM posts';
$result = mysqli_query($conn, $query);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hello, world!</title>
</head>
<body>
    <h1>Welcome to Vagrant</h1>
    <?php if(mysqli_num_rows($result) > 0): ?>
        <ul>
            <?php while($row = mysqli_fetch_object($result)): ?>
                <li><?php echo $row->text; ?></li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No posts</p>
    <?php endif; ?>
    <?php //phpinfo(); ?>
</body>
</html>