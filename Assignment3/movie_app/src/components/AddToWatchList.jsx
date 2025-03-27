import { useState } from "react";
import { addToWatchlist } from "../api";
import { useAuth } from "../context/AuthContext";

const AddToWatchlist = ({ movieId }) => {
  const { apiKey } = useAuth(); // get the api key
  const [loading, setLoading] = useState(false); // state for loading
  const [watchlistMessage, setWatchlistMessage] = useState(""); // message when add to watch list
  const [note, setNote] = useState("");
  const [showForm, setShowForm] = useState(false);

  const toggleFormVisibility = () => {
    setShowForm(!showForm); 
  };

  const handleAddToWatchlist = async () => {
    setLoading(true);
    setWatchlistMessage(""); 
    try {
      await addToWatchlist(movieId, 5, note, apiKey); // default rating = 5
      setWatchlistMessage("Movie added to your watchlist!"); // success message
      setNote(""); // clear note field after add
    } catch (error) {
      console.error("Error while adding movie to watchlist:", error); // debug

      if (error.response && error.response.status === 409) {
        setWatchlistMessage("This movie is already in your list!"); // if movie already existed in the watchlist or user already completed it
      } else if (error.message) {
        setWatchlistMessage(`Error: ${error.message}`);
      } else {
        setWatchlistMessage("Failed to add movie to watchlist. Please try again.");
      }
    } finally {
      setLoading(false); // Stop loading spinner
    }
  };

  return (
    <div className="add-to-watchlist">
      <button className="add-twl-button" onClick={toggleFormVisibility}>
        {showForm ? "Hide" : "Add to Watchlist"}
      </button>
      {showForm && (
        <div>
          <textarea
            value={note}
            onChange={(e) => setNote(e.target.value)} 
            placeholder="Enter a note for this movie..."
            rows="3"
            style={{ width: "100%" }}
          />
          <button className="add-twl-submit" onClick={handleAddToWatchlist} disabled={loading}>
            {loading ? "Adding..." : "Submit"}
          </button>
          {watchlistMessage && <p>{watchlistMessage}</p>}
        </div>
      )}
    </div>
  );
};

export default AddToWatchlist;
