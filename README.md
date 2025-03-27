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
- **Index** The index page for the main part of your site should provide brief details about your API, as well as a list (with descriptions) of routes and endpoints. This page is basically an API documentation and should tell the users what options are available to them. 

### Other details

- Each of the pages should have a consistent design that makes them look like they are all part of the same site.
- Changing password is left out for this assignment.

## Endpoints

Complete appropriate routing and request completion for each endpoint below. Every endpoint must include logical validation where appropriate, and return proper success and failure HTTP codes as appropriate.

### Movies

- **GET** & `/movies/` - returns all movies, but not all data just some main features of the movie (id, cover, title, rating,...)
- **GET** & `/movies/{id}` - returns the all columns of movie data for a specific movie.
- **GET** & `/movies/{id}/rating` - returns the rating value for a specific movie.

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

_Note:_ because the movie table contains an already-computed average rating, it is needed to recompute this average whenever a user adds or updates their rating by the following formulas:

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

## Assignment 3

For this assignment I'm going to build a React Front-end to make use of the API from assignment 2. 

### Authentication

This application should have a login page that is separate from the page you created in A2, and is instead part of the react front-end. It will use the auth endpoint you created to retrieve the API key based on the user's username and password.

API key should be stored globally using `useContext` (and like with php, if the context does not exist, should redirect to the login page)

### A Note About the Movie List

- From a UX perspective, you don't really want to show the user 4000 movies all at once.
- Including some sort of pagination as an additional feature

### A Couple of Technical Requirements

- Can use the API in the A2 folder (you don't need to copy it into A3)
- React Router should be used for all necessary routing to build a Single Page Application.
  - Should have separate routes for different views. S
  - Should also use path variables and url parameters as part of your routes where appropriate.

### Minimal Requirements & User Stories

- I want to be able to see all the movies in the catalogue
- I want to be able to find more detailed information about a movie
- I want to have at least one way to filter the movies in the catalog
  - i.e Maybe I want to find similar movies (i.e. "other romances", "other movies by Ghibli")
- I want be able to search for a specific movie
- I want to "quick add" a movie to my plan-to-watch list from the main page, with no notes and a default priority
- I want to see all the movies on my watch list sorted by priority
- I want to be able to update the priority of a movie on my watchlist
- I want be able to mark a movie as watched once I've seen it (which should remove it from my watch list and place it on the completed list)
- I want to be able to add a score onto a movie that I've seen (either when moving it to completed or later)
- I want to be able to see all my finished movies sorted by score or date watched (developers choice)
- I want to be able to update the number of times I've watched a movie on my completed list if I've watched it again.
- I want to be able to remove things from my planning list even if I don't end up watching it (e.g., I added the wrong one, or I changed my mind, etc)
- I want to have a pleasant user experience when using your application

## Project Challenges

For assignment 2, before the assignment, we had a lab to practice the same task with less endpoints and much smaller database but it already had a significant amount of code repititions. When it came to this assignment, there are a large number of smaller tasks to be completed, more endpoints to be tested, as well as repititions to be reduced. Therefore, this is an assignment where good design choices are important to avoid repeating codes (validating API key for example).

For assignment 3, I encountered lots of difficulties with CORS header, which is needed in the JSON response in the API from assignment 2 in order to make API requests from local app. But with the help of my instructor, the problem was resolved as below. However, she also mentioned that this solution is not ideal when it comes to matter of security. So this might be what I still need to learn more to effectively resolve the problem.

                header("Content-Type: application/json; charset=UTF-8");
                header("Access-Control-Allow-Origin: *");  
                header("Access-Control-Allow-Methods: *");
                header("Access-Control-Allow-Headers: *");
            
Another challenge I encountered is that the columns for production companies and genre in the data for the movies table (provided by my instructor) contain JSON objects, which basically means the data isn't normalized, and cannot be used directly to filter the movies (by genre or production companies) or display in the movie details part. I know that I need to split that data out into its own tables (a genre table, and a movie_genre table for example) using a one-off script as she mentioned it in the Readme file of the assignment, but my problem is I don't know how to this because it wasn't part of the course. I tried to do it by following some instructions on Youtube as well as other online sources but still didn't work. So I skipped this part as the assignment had deadline and I didn't have enough time to keep trying. I'm still trying to figure out how to do this.

## Techniques

- HTML
- CSS
- PHP
- React
- JavaScript/ JavaScript XML
