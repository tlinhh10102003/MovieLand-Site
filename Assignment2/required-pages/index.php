<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <link rel="stylesheet" href="./styles/main.css">
</head>
<body id="api">
    <div id="header">
        <a href="homepage.php"> MovieLand </a>
        <img src="./tv.png" alt="TV retro style"/>
    </div>
    
    <h1>API ENDPOINTS</h1>

    <h2>Welcome to the MovieLand API, where allows you to search for movies, manage your to-watch movie list or completed watchlist,
        as well as access to your account data. You'll find more detail as below</h2>
    <div id="endpoints">
        
        <p><strong><em>1. /movies Endpoint</em></strong></p>
        <ul>
            <li><strong>GET /movies/</strong> - Retrieve a list of all movies.</li>
            <li><strong>GET /movies/{id}</strong> - Retrieve detailed information about a specific movie.</li>
            <li><strong>GET /movies/{id}/rating</strong> - Retrieve rating of a specific movie given its id.</li>
        </ul>

        <hr>

        <p><strong><em>2. /towatchlist Endpoint</em></strong></p>
        <ul>
            <li><strong>GET /towatchlist/entries</strong> - Retrieve all entries on the to-watch list.</li>
            <li><strong>POST /towatchlist/entries</strong> - Add a movie to the to-watch list.</li>
            <li><strong>PUT /towatchlist/entries/{id}</strong> - Replace a movie in the to-watch list.</li>
            <li><strong>PATCH /towatchlist/entries/{id}/priority</strong> -  Updates the user's priority for the appropriate movie.</li>
            <li><strong>DELETE /towatchlist/entries/{id}</strong> - Delete the appropriate movie from the user's towatch list..</li>
        </ul>

        <hr>
        
        <p><strong><em>3. /completedwatchlist Endpoint</em></strong></p>
        <ul>
            <li><strong>GET /completedwatchlist/entries</strong> - Retrieve all entries on the completed watchlist.</li>
            <li><strong>GET /completedwatchlist/entries/{id}/times-watched </strong> - Requires an api key and returns the number of times the user has watched the given movie.</li>
            <li><strong>GET /completedwatchlist/entries/{id}/rating </strong> - requires an api key and returns the user's rating for this specific movie.</li>
            <li><strong>POST /completedwatchlist/entries </strong> - requires an api key and all other data necessary for the completedWatchList table, validates then inserts the data. It should also recompute and update the rating for the appropriate movie.</li>
            <li><strong>PATCH /completedwatchlist/entries/{id}/rating </strong> - requires an api key and new rating and updates the rating for the appropriate movie in the completedWatchList table, then recalculates the movie's rating and updates the movies table. </li>
            <li><strong> PATCH /completedwatchlist/entries/{id}/times-watched </strong> - requires an api key and increments the number of times watched and updates the last date watched of the appropriate movie. </li>
            <li><strong> DELETE /completedwatchlist /entries/{id} </strong> - requires and api key and movieID and deletes the appropriate movie from the completedWatchList. </li>
        </ul>
        
        <hr>

        <p><strong><em>4. /users Endpoint</em></strong></p>
        <ul>
            <li><strong>GET & /users/{id}/stats</strong> - returns basic watching stats for the provided user. You can chose the stats, but you should have at least 4. e.g. total time watched, average score, planned time to watch, etc.</li>
            <li><strong>POST & /users/session</strong> - accepts a username and password, verifies these credentials and returns the corresponding API key. (You can mostly steal this logic from your login page above, just generate json responses instead)</li>
        </ul>
        
    </div>
    <footer>&copy; MovieLand, Inc. 2024</footer>
</body>
</html>
