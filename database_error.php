<?php
    session_reset();
    ?>

<!DOCTYPE html>
<html>
    <head>
        <title> Student Manager Database Error</title>
        <link rel="stylesheet"  type="txt/css" href="/css/app.css" />
    </head>
    <body>
        <?php include("header.php"); ?>

        <main>
            <h2>Database Error info</h2>

            <p>There was an error connecting to the database.</p>
            <p>The database must be installed.</p>
            <p>MySQL must be running.</p>
            <p>Restart Configuration and retry again</p>
            <p>Error mesage: <?php echo $_SESSION["database_error"]; ?></p>

            <p><a href="index.php">View Contact List</a></p>
        </main>
        <?php include("footer.php"); ?>
        
    </body>
</html>