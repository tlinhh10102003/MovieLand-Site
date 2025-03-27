# MovieLand-Site

## Project Description

This project is a React-based simple movie browsing site developed as part of university course - COIS 3430: Web Development II: Server-side and Frameworks, and was divided into 2 assignments accomplished througout the Fall semester 2024. Both assignments were built individually under course's instructor and teaching assistant guidance.

## Assignment 2

This assignment focuses on building backend API to handle endpoints routing, create database tables, along with self-processing PHP pages for authentication, creating account, viewing account information, regenerating API key if requested. Later in assignment 3, we'll use the front-end framework in conjunction with your back-end api to complete a full application.

Required pages for this assignment:

- **Create Account**: The create account page should collect at minimum username, email and password (thinking of the app we're going to build in the end, you can also include other fields if you want).
  - The _username_ should be unique, the email address must be valid, and you should enforce some sort of logical and secure password strength verification.
  - Creating an account should also generate an API key for the user.
  - This information should be written to a user's table in the database (you'll need to create the table).
- **Login**: The login page should accept the username and password and verify them against the database. Then setup session to verify their authentication on the view account page.
  - If the user is logged in, you should display a logout button so they can logout.
- **View Account**: This page should show the user their account details (except password). Its purpose is largely to give them access to their API key.
  - It should also include a button/link that allows them to request a new API key, should theirs be compromised.
- **Index** The index page for the main part of your site should provide brief details about your API, as well as a list (with descriptions) of routes and endpoints. This page is basically your API documentation and should tell the users what options are available to them. This is just straight HTML.

### Other details

- Each of the pages should have a consistent design that makes them look like they are all part of the same site.
- Changing password is left out for this assignment.

## Endpoints

Complete appropriate routing and request completion for each endpoint below. Every endpoint must include logical validation where appropriate, and return proper success and failure HTTP codes as appropriate.

### Movies

- **GET** & `/movies/` - should return all movies, but not all data.
  - Consider what the main display of your movies might need to include (id, cover, title, rating - maybe? ), and only return that.
  - For testing this, you might want to at least temporarily limit it to something like the first 100 rows.
- **GET** & `/movies/{id}` - returns the all columns of movie data for a specific movie.
- **GET** & `/movies/{id}/rating` - returns the rating value for a specific movie.
  - this is mostly an efficiency endpoint, so later we can get an updated rating without needing to retrieve all the data again.

### toWatchList

- **GET** & `/towatchlist/entries` - requires an api key and returns all entries on the user's toWatchList (Note: this needs to include the basic movie information as well..think about what you'd need to display to show them their watch list)
- **POST** & `/towatchlist/entries` - requires an api key and all other data necessary for the toWatchList table, validates then inserts the data.
- **PUT** & `/towatchlist/entries/{id}` - requires an api key and all other data necessary for the toWatchList table and replaces the entire record in the database (if there is no record it should insert and return the appropriate HTTP code).
- **PATCH** & `/towatchlist/entries/{id}/priority` - requires an api key and new priority and updates the user's priority for the appropriate movie.
- **DELETE** & `/towatchlist/entries/{id}` - requires and api key and movieID and deletes the appropriate movie from the user's watchlist.

### completedWatchList

- **GET** & `/completedwatchlist/entries` - requires an api key and returns all entries on the user's completedWatchList. (Note: this needs to include the basic movie information as well..think about what you'd need to display to show them their completed list)
- **GET** & `/completedwatchlist/entries/{id}/times-watched` - requires an api key and returns the number of times the user has watched the given movie
- **GET** & `/completedwatchlist/entries/{id}/rating` - requires an api key and returns the user's rating for this specific movie
- **POST** & `/completedwatchlist/entries` - requires an api key and all other data necessary for the completedWatchList table, validates then inserts the data. It should also recompute and update the rating for the appropriate movie.
- **PATCH** & `/completedwatchlist/entries/{id}/rating` - requires an api key and new rating and updates the rating for the appropriate movie in the completedWatchList table, then recalculates the movie's rating and updates the movies table.
- **PATCH** & `/completedwatchlist/entries/{id}/times-watched` - requires an api key and increments the number of times watched and updates the last date watched of the appropriate movie.
- **DELETE** & `/completedwatchlist /entries/{id}` - requires and api key and movieID and deletes the appropriate movie from the completedWatchList.

_Note:_ because the movie table contains an already-computed average rating, you need to recompute this average whenever a user adds or updates their rating. You can use the following formulas to determine the new rating.

#### Adding a new rating

<!-- Turn on your Markdown Preview to be able to read these formulas! -->

$$
\text{NewAvgRating} = \frac{
  (\text{OldAvgRating} \cdot \text{OldRatingCount}) + \text{NewRating}
}{
  \text{NewCount}
}
$$

#### Updating an existing rating

$$
\text{NewAvgRating} = \frac{
  (\text{OldAvgRating} \cdot \text{OldCount}) - \text{OldRating} + \text{NewRating}
}{
  \text{NewCount}
}
$$

### Users

- **GET** & `/users/{id}/stats` - returns basic watching stats for the provided user. You can chose the stats, but you should have at least 4. e.g. total time watched, average score, planned time to watch, etc.

### Auth

- **POST** & `/users/session` - accepts a username and password, verifies these credentials and returns the corresponding API key. (You can mostly steal this logic from your login page above, just generate json responses instead)

### Filters

Extend up to four of the above GET endpoints to support filters. You should have at least four filters total, implemented across at least 3 different endpoints. This might include things like: filtering all movies by title or genre, toWatch movies by priority, most watched movies, best rated, etc.
