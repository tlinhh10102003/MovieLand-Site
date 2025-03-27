import React, { useState, useEffect } from "react";
import { useLocation } from "react-router-dom";
import { useNavigate } from "react-router-dom";
import { searchMovies } from "../api"; 
import AddToWatchlist from "./AddToWatchList";
import Header from "./Header";

const SearchResults = () => {
    const location = useLocation();
    const query = new URLSearchParams(location.search).get("search"); // get the search queyr
    const [movies, setMovies] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const navigate = useNavigate();

    const handleClick = (id) => {
      navigate(`/movie/${id}`);
    };

    useEffect(() => {
    if (!query) return;

    const fetchSearchResults = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await searchMovies(query); 
            console.log("Movies returned:", response);  // log the response for debug
            
            
            if (response?.results && response.results.length > 0) {
                setMovies(response.results);  
            } else {
                setMovies([]);  // if no results, clear the movies state
            }
        } catch (err) {
            setError(err.response?.data?.error || err.message || "Failed to fetch search results.");
        } finally {
            setLoading(false);
        }
    };

    fetchSearchResults();
}, [query]);

    return (
    <main>
        <Header />
        <div className="search-results">
            <h1>Search Results for "{query}"</h1>
            {loading && <p>Loading...</p>}
            {error && <p className="error">{error}</p>}
            <div className="movie-results">
                {movies.length > 0 ? (movies.map((movie) => (
                    <div key={movie.id} className="searched-movie">
                        <h2>
                            {movie.title.split(" ").slice(0, 4).join(" ")}
                            {movie.title.split(" ").length > 4 ? "..." : ""}
                        </h2>
                        <img src={movie.poster} alt={movie.title} className="movie-poster" />
                        <button onClick={() => handleClick(movie.id)}>View more details</button>
                        <AddToWatchlist movieId={movie.id} />
                    </div>
                    ))
                ) : (
                    !loading && <p>No movies found for "{query}".</p>
                )}
            </div>
        </div>
    </main>
    );
};

export default SearchResults;
