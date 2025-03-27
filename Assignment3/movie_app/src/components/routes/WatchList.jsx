import React, { useEffect, useState } from "react";
import { useAuth } from "../../context/AuthContext"; 
import { getWatchListEntries, updateWatchListPriority, deleteWatchListEntries, markMovieAsWatched } from "../../api";
import Header from "../Header";
import MovieCard from "../MovieCard"; 

const WatchList = () => {
  const { apiKey } = useAuth();
  const [watchlist, setWatchlist] = useState([]);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState("");

  const sortWatchlist = (list) => {
    return [...list].sort((a, b) => a.priority - b.priority); 
  };
  
  useEffect(() => {
    if (!apiKey) {
      setError("API key is missing. Please log in first");
      setLoading(false);
      return;
    }

    // fetch user watchlsit
    const fetchWatchlist = async () => {
      try {
        const data = await getWatchListEntries(apiKey);
        console.log("Fetched watchlist data:", data);
        setWatchlist(data);
      } catch (err) {
        setError("Failed to fetch the watchlist.");
      } finally {
        setLoading(false);
      }
    };

    if (apiKey) {
      fetchWatchlist();
    }
  }, [apiKey]);

  // handle update priority 
  const handleUpdatePriority = async (id, priority, movieID) => {
    console.log("Updating priority to", priority, "for movie ID:", movieID);
  
    try {
      await updateWatchListPriority(apiKey, id, priority, movieID);
      setWatchlist((prevList) => sortWatchlist(
        prevList.map((movie) =>
          movie.id === id ? { ...movie, priority: priority } : movie
        )
      )); // Sort the updated list
      setMessage(`Priority for entry ID ${id} updated to ${priority}.`);
    } catch (error) {
      console.error("Error updating priority:", error);
      setMessage("Failed to update priority. Please try again.");
    }
  };
  
  // handle delete a watchlist entry
  const handleDeleteEntries = async (apiKey, entryId, movieId) => {
    console.log("Deleting movie with ID:", movieId);
    if (!movieId) {
      console.error("Missing movie ID for entry ID:", entryId);
      setMessage("Cannot delete entry. Missing movie ID.");
      return;
    }
  
    if (!apiKey) {
      setError("API key is missing.");
      return;
    }
  
    try {
      await deleteWatchListEntries(apiKey, entryId, movieId);
      setMessage(`Movie with entry ID ${entryId} deleted successfully.`);
      setWatchlist((prevList) => prevList.filter((movie) => movie.id !== entryId)); // Filter out the deleted entry
    } catch (error) {
      console.error("Error deleting entry:", error);
      setMessage("Failed to delete entry. Please try again.");
    }
  };  

  const handleMarkAsWatched = async (movieId, entryId, rating, notes) => {
    if (!apiKey) {
      setError("API key is missing.");
      return;
    }
    if (!entryId) {
      setError("Entry ID is missing.");
      return;
    }
  
    const parsedRating = parseFloat(rating);
  
    try {
      await markMovieAsWatched({
        apiKey,
        entryId,
        rating: parsedRating,
        notes,
      });
  
      setWatchlist((prevList) => prevList.filter((movie) => movie.id !== entryId));
      setMessage("Movie marked as watched successfully.");
  
      setMessage("Movie marked as watched successfully.");
    } catch (error) {
      console.error("Error marking movie as watched:", error);
      setMessage("Failed to mark movie as watched.");
    }
  };  

  if (loading) return <p>Loading watchlist...</p>;
  if (error) return <p>{error}</p>;
  if (!watchlist.length) return <p>Your watchlist is empty!</p>;

  return (
    <main>
      <Header />
      <div className="watch-list">
        <h1>My WatchList</h1>
        <div className="watchlist-container">
          {watchlist.map((movie) => (
            <MovieCard
            key={movie.id}
            movie={movie}
            onUpdatePriority={(priority) => handleUpdatePriority(movie.id, priority, movie.movieID)}  
            onDeleteEntry={(entryId, movieId) => handleDeleteEntries(apiKey, entryId, movieId)}
            onMarkAsWatched={handleMarkAsWatched}
          />
          
          ))}
        </div>
        {message && <p className="feedback-message">{message}</p>}
      </div>
    </main>
  );
};

export default WatchList;
