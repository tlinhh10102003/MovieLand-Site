<?php 
require_once "./includes/library.php";
$movies = [];
$search = isset($_POST['search']) ? $_POST['search'] : " ";
if($_SERVER['REQUEST_METHOD'] === "POST")
{
    $search = strtolower($search);  
    if($search) {
        $pdo = connectdb();
        $query = "SELECT id, title, overview, rating FROM MOVIES WHERE LOWER(title) LIKE ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['%' . strtolower($search) . '%']);
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        // Return all movies if no search term is provided
        $query = "SELECT id, title, overview, rating, poster FROM MOVIES";
        $stmt = $pdo->query($query);
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HomePage</title>
    <link rel="stylesheet" href="./styles/main.css">
    <script src="https://kit.fontawesome.com/a8c4a04983.js" crossorigin="anonymous"></script>
</head>

<body id = "homepage">
    <div id="nav-bar">
        <div id="header">
            <a href="homepage.php"> MovieLand </a>
            <img src="./tv.png" alt="TV retro style"/>
        </div>
        <div id="options">
            <a href="login.php">LOG IN</a>
            <a href="create-account.php">SIGN UP</a>
            <a href="filmlist.php">FILM LIST</a>
            <a href="index.php">API DOCUMENTATION</a>
        </div>
    
        <form method="POST">
            <button id="submit" type="submit" name="submit"><strong>Search</strong></button>
            <i id="search-glass" class="fa-solid fa-magnifying-glass" style="color: #b64931"></i>
            <input type="text" name="search" id="search" value="<?= $search ?>" />
        </form>
    </div> 

    <?php if (!empty($movies)): ?>
    <div class="movies-list">
        <h1>SEARCH RESULTS</h1>

        <table>
        <thead>
        <th>Title</th>
        <th>Overview</th>
        <th>Rating</th>
        </thead>
        <tbody>
            <?php foreach ($movies as $movie): 
            if (!empty($search)) {
                if($search && stripos($movie['title'], $search) !== FALSE)
                {
                    //if search term is found in the string, return new string which replace original search term with the highlighted one
                    if($search && stripos($movie['title'], $search) !== FALSE) {
                        $highlightedOverview = str_ireplace($search, "<span class='highlight'>$search</span>", $movie['overview']); 
                        $highlightedTitle = str_ireplace($search, "<span class='highlight'>$search</span>", $movie['title']);
                    }
                    else {
                        $highlightedTitle = str_ireplace($search, "<span class='highlight'>$search</span>", $movie['title']);
                    }
                }
            }
                ?>
            <tr>
            <td><?= $highlightedTitle ?></td>
            <td><?= $highlightedOverview ?></td>
            <td><?= ($movie['rating']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>

    <?php elseif ($_SERVER['REQUEST_METHOD'] === "POST"): ?>
    <p>No results found for "<?= ($search); ?>"</p>
    <?php endif; ?>
    
    <footer>&copy; MovieLand, Inc. 2024</footer>
</body>
</html>
