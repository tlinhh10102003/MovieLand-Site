import React, { useState } from "react";

const MovieCard = ({ movie, onUpdatePriority, onDeleteEntry, onMarkAsWatched }) => {
  const [showForm, setShowForm] = useState(false);
  const [showPriority, setShowPriority] = useState(false);
  const [newPriority, setNewPriority] = useState(""); 
  const [rating, setRating] = useState("");
  const [notes, setNotes] = useState("");

  const togglePriorityVisibility = () => {
    setShowPriority(!showPriority); 
  };

  const toggleFormVisibility = () => {
    setShowForm(!showForm); 
  };

  const handleChange = (e) => {
    setNewPriority(e.target.value);  
  };  

  const handleSubmit = () => {
    const priorityNumber = Number(newPriority); // validate priority
  
    if (!newPriority.trim() || isNaN(priorityNumber) || priorityNumber <= 0) {
      alert("Please enter a valid positive priority.");
      return;
    }
  
    console.log("Priority entered:", newPriority);  // debug
    console.log("Updating priority to:", priorityNumber); // debug
    
    onUpdatePriority(priorityNumber); 
  };
  
  const handleDelete = () => {
    if (!movie.id || !movie.movieID) { 
      alert("Cannot delete. Missing entry ID or movie ID.");
      return;
    }
    onDeleteEntry(movie.id, movie.movieID); 
  };

  const handleWatched = async () => {
    const ratingNumber = (parseFloat)(rating);
    console.log("Rating:", rating, "Notes:", notes);  

    const entryId = movie.id;
    console.log("Entry ID:", entryId); // debug

    if (rating && (isNaN(ratingNumber) || ratingNumber < 1 || ratingNumber > 10)) {
        alert("Please enter a rating between 1 and 10.");
        return;
    }

    try {
        console.log("handleWatched called with:", { entryId, rating: ratingNumber, notes });
        await onMarkAsWatched(movie.movieID, entryId, ratingNumber, notes);
        setRating("");  // reset to empty after marked
        setNotes("");   
    } catch (error) {
        console.error("Error marking movie as watched:", error.response || error.message);
        alert("Failed to mark movie as watched. Please try again later.");
    }
  };


  return (
    <div className="movie-card">
      <h2>{movie.title}</h2>
      <img src={movie.poster} alt={movie.title} />
      <p><strong>Movie Description: </strong>{movie.overview}</p>
      <p><strong>Priority in your list: </strong>{movie.priority}</p>
      <p><strong>Rating: </strong>{movie.rating}</p>
      <p><strong>Your Note: </strong>{movie.notes}</p>

      <button className="update-priority" onClick={togglePriorityVisibility}>
        {showPriority ? "Hide Priority" : "Update Priority"}
      </button>

      {showPriority && (
        <div>
          <div>
            <input
              type="number"
              min="1"
              placeholder="New Priority"
              value={newPriority}
              onChange={handleChange}
            />
            <button onClick={handleSubmit}>Submit</button>
          </div>
        </div>
      )}

      <button className="watched" onClick={toggleFormVisibility}>
        {showForm ? "Hide Rating & Notes" : "Mark as Watched"}
      </button>

      {showForm && ( // only show show form if user want to mark as watched a movie
        <div>
          <label>
              Rating:
              <input
                  type="number"
                  value={rating}
                  onChange={(e) => setRating(e.target.value)}
                  min="1"
                  max="10"
              />
          </label>
          <div>  
            <textarea
                placeholder="Add notes"
                value={notes}
                onChange={(e) => setNotes(e.target.value)}
            />
          </div>
          <button onClick={handleWatched}>Submit Rating & Notes</button>
        </div>
      )}

      <button className="delete-button" onClick={handleDelete}>
        Delete from List
      </button>
    </div>
  );
};

export default MovieCard;
