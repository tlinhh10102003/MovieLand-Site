import React, { useState, useEffect } from "react";
import { useAuth } from "../../context/AuthContext"; 
import Header from "../Header";
import { getCompletedMovies, updateTimesWatched, updateMovieRating } from "../../api";

const CompletedList = () => {
  const { apiKey } = useAuth();
  const [completedMovies, setCompletedMovies] = useState([]);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const [message, setStatusMessage] = useState("");
  const [movieRatings, setMovieRatings] = useState({}); 
  const [showForm, setShowForm] = useState(false);

  const toggleFormVisibility = () => {
    setShowForm(!showForm); 
  };

  useEffect(() => {
    if (!apiKey) {
      setError("API key is missing.");
      setLoading(false);
      return;
    }
    const fetchCompletedMovies = async () => {
      try {
        const data = await getCompletedMovies(apiKey);
        console.log("Fetched watchlist data:", data);
        setCompletedMovies(data);
      } catch (error) {
        setError("Error fetching completed movies:", error);
      } finally {
        setLoading(false); 
      }
    };
    if(apiKey){
      fetchCompletedMovies(); 
    }
  }, [apiKey]);

  const handleUpdateTimesWatched = async (movieId) => {
    if (!apiKey) {
      setError("API key is missing.");
      return;
    }
    try {
      await updateTimesWatched(movieId, apiKey);
      const updatedMovies = await getCompletedMovies(apiKey); // refetch completed list to display updated data
      setCompletedMovies(updatedMovies); // update the list
      setStatusMessage("Successfully updated the times watched!");
    } catch (error) {
      setError("Error updating times watched", error);
      setStatusMessage("Failed to update times watched.");
    }
  };
  
  const handleUpdateRating = async (movieId) => {
    if (!apiKey) {
      setError("API key is missing.");
      return;
    }
  
    const rating = movieRatings[movieId]; // get the rating for the specific movie
  
    console.log("Updating rating for movie entry ID:", movieId, "to rating:", rating); // debug
  
    // validate rating
    if (typeof rating !== 'number' || isNaN(rating)) {
      setStatusMessage("Invalid rating. Please enter a valid numeric value.");
      return;
    }
  
    try {
      const updatedMovie = { new_rating: parseFloat(rating) };
  
      console.log("Sending updated rating:", updatedMovie.new_rating); // debug
  
      // call API request to update rating
      await updateMovieRating(movieId, updatedMovie, apiKey);
  
      setStatusMessage("Rating updated successfully!");
    } catch (error) {
      setStatusMessage("Failed to update rating.");
      console.error("Error updating rating:", error);
    }
  };
  
  
  // handle the change in rating for a specific movie
  const handleRatingChange = (movieId, newRating) => {
    const parsedRating = parseFloat(newRating);
  
    setMovieRatings((prevRatings) => ({
      ...prevRatings,
      [movieId]: parsedRating, 
    }));
  };
  
  // show loading, error, or empty message if no movies
  if (loading) return <p>Loading completed watchlist...</p>;
  if (error) return <p>{error}</p>;
  if (!completedMovies.length) return <p>Your completed list is empty!</p>;

  return (
    <main>
      <Header />
      <div className="completed-list">
        
        <h1>My Completed List</h1>
        <div className="completedlist-container">
          {completedMovies.map((movie) => (
            <div key={movie.id} className="movie-card">
              <h2>{movie.title}</h2>
              <img src={movie.poster} alt={movie.title} />
              <p><strong>Movie Description: </strong>{movie.overview}</p>
              <p><strong>Movie Average Rating: </strong>{movie.rating}</p>
              <p><strong>Your Rating: </strong>{movieRatings[movie.id] || movie.userRating}</p>
              <p><strong>Times you have watched this movie: </strong>{movie.times_watched}</p>
              <p><strong>The last time you watched it: </strong>{movie.date_last_watched}</p>
              <p><strong>A note you left for this movie: </strong> {movie.notes}</p>
  
              <button className="update-times-watched" onClick={() => handleUpdateTimesWatched(movie.id)}>
                Watched Again
              </button>
  
              {message && (
                <div className={message.includes("Failed") ? "error-message" : "success-message"}>
                  {message}
                </div>
              )}

              <button className="update-rating" onClick={toggleFormVisibility}>
                {showForm ? "Hide Rating" : "Updating movie rating"}
              </button>

              {showForm && (
                <div>
                  <label>
                    (Re)-Rate this movie:
                    <input
                      type="number"
                      value={movieRatings[movie.id] || ""} 
                      onChange={(e) => handleRatingChange(movie.id, e.target.value)} 
                      min="1"
                      max="10"
                    />
                  </label>
      
                  <button className="update-rating-submit" onClick={() => handleUpdateRating(movie.id)}>
                    Submit
                  </button>
                </div>
              )}

            </div>
          ))}
        </div>
      </div>
    </main>
  );
};

export default CompletedList;
