<?php 
include "./includes/library.php";

$pdo = connectdb();

// divide results into pages 
$results_per_page = 30;

// current page or default = 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// starting point for each page
$start_from = ($page - 1) * $results_per_page;

$total_movies_query = "select count(*) AS total from MOVIES";
$total_movies_stmt = $pdo->query($total_movies_query);
$total_movies = $total_movies_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_movies / $results_per_page);

// fetch the movies for the current page
$query = "SELECT id, title, overview, rating 
          FROM MOVIES 
          LIMIT :start_from, :results_per_page";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':start_from', (int)$start_from, PDO::PARAM_INT);
$stmt->bindValue(':results_per_page', (int)$results_per_page, PDO::PARAM_INT);

if ($stmt->execute()) {
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FILM LIST</title>
    <link rel="stylesheet" href="./styles/main.css">
    <script src="https://kit.fontawesome.com/a8c4a04983.js" crossorigin="anonymous"></script>
</head>

<body id="filmlist">
    <div id="header">
        <a href="homepage.php"> MovieLand </a>
        <img src="./tv.png" alt="TV retro style"/>
    </div>

    <div id="list">
        <h1>MOVIES LIST</h1>
        <?php if (!empty($movies)): ?>
            <table id="movies">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Overview</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($movies as $movie): ?>
                        <tr>
                            <td><?= ($movie['id']) ?></td>
                            <td><?= ($movie['title']) ?></td>
                            <td><?= ($movie['overview']) ?></td>
                            <td><?= ($movie['rating']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No movies found for this page.</p>
        <?php endif; ?>
    </div>

    <div class="page-section">
        <?php if ($page > 1): ?>
            <a href="filmlist.php?page=<?= $page - 1 ?>">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="filmlist.php?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="filmlist.php?page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>

    <footer>&copy; MovieLand, Inc. 2024</footer>
</body>
</html>
