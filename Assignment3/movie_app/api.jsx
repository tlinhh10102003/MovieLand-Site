import axios from "axios";

// API URL
const API_URL = "https://loki.trentu.ca/~litran/3430/assn/assn2-tlinhh10102003/api";

export const getMoviesPaginated = async (resultsPerPage, currentPage) => {
    try {
      const response = await axios.get(`${API_URL}/movies`, {
        params: {
          page: currentPage,
          results_per_page: resultsPerPage,
        },
      });
  
      return response.data; 
    } catch (error) {
      console.error("Error fetching movies:", error);
      throw error;
    }
};


export const getMovieById = async (id) => {
    try {
        const response = await axios.get(`${API_URL}/movies/${id}`, 
            // headers: { "x-api-key": `${apiKey}` },
        );
        return response.data;
    } catch (error) {
        console.error("Error fetching movie details:", error);
        throw error;
    }
};

export const getMovieRating = async (id, apiKey) => {
    if (!apiKey) {
        throw new Error("API key is missing. Please log in.");
    }

    try {
        const response = await axios.get(`${API_URL}/movies/${id}/rating`, {
            headers: { "x-api-key": `${apiKey}` },
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching movie rating:", error);
        throw error;
    }
};

export const searchMovies = async (query) => {
    console.log("Passed query:", query);  // debug

    try {
        const encodedQuery = encodeURIComponent(query);  
        const response = await axios.get(
            `${API_URL}/movies/search?q=${encodedQuery}`,
            {
                headers: {
                    'Content-Type': 'application/json',
                    // 'x-api-key': apiKey,
                },
            }
        );
        console.log("API Response:", response.data);
        return response.data;
    } catch (error) {
        console.error('Error searching for movies:', error.response?.data || error.message);
    }
};

export const getWatchListEntries = async (apiKey) => {
    try {
      const response = await axios.get(`${API_URL}/towatchlist/entries`, {
        headers: {
          "x-api-key": `${apiKey}`,
        },
      });
      return response.data; 
    } catch (error) {
      console.error("Error fetching watchlist:", error);
      throw error; // Re-throw to be caught in the component
    }
};
  
export const addToWatchlist = async (movieId, priority = 5, notes = "", apiKey) => {
    if (!apiKey) {
        throw new Error("API key is missing. Please log in.");
    }

    try {
        const response = await axios.post(`${API_URL}/towatchlist/entries`, 
        { 
            movie_id: movieId, 
            priority,         
            notes             
        }, 
        { 
            headers: { "x-api-key": `${apiKey}` }
        });
        return response.data;
    } catch (error) {
        console.error("Error adding to watchlist:", error.response?.data || error.message);
        throw error;
    }
};


export const updateWatchListPriority = async (apiKey, entryId, priority, movieID) => {
    try {
      console.log("Updating priority...");
      console.log(`Entry ID: ${entryId}, Priority: ${priority}, Movie ID: ${movieID}`);
  
      const response = await axios.put(
        `${API_URL}/towatchlist/entries/${entryId}/priority`, 
        { 
          priority: priority,
          movie_id: movieID, 
        },
        {
          headers: { 
            "x-api-key": apiKey, 
            "Content-Type": "application/json"
          }
        }
      );
  
      console.log("Response:", response);
      return response.data;
    } catch (error) {
      console.error("Error updating priority:", error);
      if (error.response) {
        console.error("Response Error: ", error.response.data);
      }
      throw error;
    }
};


export const deleteWatchListEntries = async (apiKey, entryId, movieId) => {
    try {
        const response = await axios.delete(
          `${API_URL}/towatchlist/entries/${entryId}`, 
          {
            headers: { "x-api-key": apiKey },
            data: { movie_id: movieId } 
          }
        );
        return response.data;
    } catch (error) {
        console.error("Error in API request:", error);
        throw error;
    }
};

// Fetch completed watch movies
export const getCompletedMovies = async (apiKey) => {
    try {
        const response = await axios.get(`${API_URL}/completedwatchlist/entries`, {
            headers: { "x-api-key": `${apiKey}` },
        });
        console.log("API Response:", response.data);
        return response.data;
    } catch (error) {
        console.error("Error fetching completed movies:", error);
        throw error;
    }
};

// Update times watched for a completed movie
export const updateTimesWatched = async (entryId, apiKey) => {
    if (!apiKey) {
        throw new Error("API key is missing. Please log in.");
    }

    try {
        const response  = await axios.patch(
            `${API_URL}/completedwatchlist/entries/${entryId}/times-watched`,
            {},
            { headers: { "x-api-key": `${apiKey}` } }
        );

        if (response.status === 200) {
            console.log('Successfully updated times watched:', response.data);
        }
    } catch (error) {
        console.error("Error updating times watched:", error);
        throw error;
    }
};

// Update a movie's rating
export const updateMovieRating = async (entryId, rating, apiKey) => {
    if (!apiKey) {
        throw new Error("API key is missing. Please log in.");
    }

    try {
        console.log("Received rating:", rating);

        if (rating && typeof rating === 'object' && rating.new_rating) {
            rating = rating.new_rating;
        }

        if (isNaN(rating) || rating == null) {
            console.error("Invalid rating:", rating);
            return;
        }

        console.log("Sending updated rating:", rating);

        const updatedMovie = { new_rating: rating };

        const response = await axios.patch(
            `${API_URL}/completedwatchlist/entries/${entryId}/rating`,
            updatedMovie,
            { headers: { 'Content-Type': 'application/json', 'x-api-key': `${apiKey}` } }
        );

        console.log("Response from server:", response.data);
        return response.data;
    } catch (error) {
        console.error("Error updating movie rating:", error.response || error);
        if (error.response?.status === 404) {
            console.error(`Entry ID ${entryId} not found.`);
        }
        throw error;
    }
};

export const markMovieAsWatched = async ({ apiKey, entryId, rating, notes }) => {
    try {
        // if not provided set to null
        const data = {
            note: notes && notes.trim() !== "" ? notes : null,  
            rating: rating ? rating : null,  
        };

        console.log("Request body:", data); // debug

        const response = await axios.post(
            `${API_URL}/towatchlist/entries/${entryId}/watched`,
            data,
            { headers: { 'Content-Type': 'application/json', 'x-api-key': apiKey } }
        );

        console.log("Response:", response);

    } catch (error) {
        if (error.response) {
            console.error("Error marking movie as watched:", error.response.data);
            console.error("Status Code:", error.response.status);
        } else if (error.request) {
            console.error("Error: No response received from server.");
        } else {
            console.error("Error in request setup:", error.message);
        }
    }
};

export const fetchUserStats = async (userId, apiKey) => {
    if (!userId) throw new Error("User ID is missing.");
    if (!apiKey) throw new Error("API key is missing.");

    try {
        const response = await axios.get(`${API_URL}/users/${userId}/stats`, {
        headers: { 'x-api-key': apiKey },
        });
        return response.data; 
    } catch (error) {
        throw new Error(error.response?.data?.error || "Failed to fetch user stats.");
    }
};
