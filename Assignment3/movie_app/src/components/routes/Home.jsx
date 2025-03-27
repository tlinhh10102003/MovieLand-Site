import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { getMoviesPaginated } from "../../api";
import "../../styles/MovieList.css";
import Header from "../Header";
import AddToWatchlist from "../AddToWatchList";

const Home = () => {
  const [movies, setMovies] = useState([]);
  const [filteredMovies, setFilteredMovies] = useState([]);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [yearFilter, setYearFilter] = useState("all"); 
  const resultsPerPage = 30;
  const navigate = useNavigate();

  const handleClick = (id) => {
    navigate(`/movie/${id}`);
  };

  useEffect(() => {
    const fetchMovies = async () => {
      setLoading(true);
      try {
        const data = await getMoviesPaginated(resultsPerPage, currentPage);
        
        if (!data || !data.length) { // no movies found
          console.log("No movies found.");
          setMovies([]); // reset array of movies
          setFilteredMovies([]); // reset array of filtered movie
          return;
        }

        console.log("Movies fetched:", data); // debug
        setMovies(data || []); // set movies to the fetched data
        setFilteredMovies(data); 

        const totalMovies = data.length; // find total movies fetched
        const calculatedTotalPages = Math.ceil(totalMovies / resultsPerPage); // total pages based on total movies
        setTotalPages(calculatedTotalPages); // set total pages
      } catch (error) {
        console.error("Error fetching movies:", error);
        setMovies([]); // reset array of movies
        setFilteredMovies([]);
      } finally {
        setLoading(false);
      }
    };

    fetchMovies();
  }, [currentPage]); // re-redender if current page changes

  // Handle Release Year Filter
  const handleYearFilterChange = (selectedYearRange) => {
    setYearFilter(selectedYearRange);
  
    if (selectedYearRange === "all") {
      setFilteredMovies(movies);
      setTotalPages(Math.ceil(movies.length / resultsPerPage)); // update total pages for all movies
      setCurrentPage(1); // reset to the first page
      return;
    }
  
    const yearRanges = {
      "2000-2005": [2000, 2005],
      "2006-2010": [2006, 2010],
      "2011-2015": [2011, 2015],
      "2016-2020": [2016, 2020],
    };
  
    const [startYear, endYear] = yearRanges[selectedYearRange] || [];
    const filtered = movies.filter((movie) => {
      const movieYear = new Date(movie.release_date).getFullYear();
      return movieYear >= startYear && movieYear <= endYear;
    });
  
    setFilteredMovies(filtered);
    setTotalPages(Math.ceil(filtered.length / resultsPerPage)); // update total pages for filtered movies
    setCurrentPage(1); // reset to the first page
  };
  
  // if (loading) return <p>Loading...</p>;

  const startIndex = (currentPage - 1) * resultsPerPage;
  const endIndex = startIndex + resultsPerPage;
  const paginatedMovies = filteredMovies.slice(startIndex, endIndex);

  return (
    <main>
      <Header />
      <div className="movie-list">
        <h1>Movies List</h1>

        {/* filter movie by released year */}
        <div className="filter-container">
          <label htmlFor="year-filter">Filter by Release Year:</label>
          <select
            id="year-filter"
            value={yearFilter}
            onChange={(e) => handleYearFilterChange(e.target.value)}
          >
            <option value="all">All Years</option>
            <option value="2000-2005">2000 - 2005</option>
            <option value="2006-2010">2006 - 2010</option>
            <option value="2011-2015">2011 - 2015</option>
            <option value="2016-2020">2016 - 2020</option>
          </select>
        </div>

        <div className="movie-section">
          {paginatedMovies.length ? (
            paginatedMovies.map((movie) => (
              <div key={movie.id} className="movie-item">
                <h2 
                  className="movie-title"
                  title={movie.title} // tooltip showing full title
                >
                  <a onClick={() => handleClick(movie.id)}>{movie.title.split(" ").slice(0, 4).join(" ")}</a>
                  {movie.title.split(" ").length > 4 ? "..." : ""}
                </h2>
                <img src={movie.poster} alt={movie.title} className="movie-poster" />
                <button onClick={() => handleClick(movie.id)}>View more details</button>
                <AddToWatchlist movieId={movie.id} />
              </div>
            ))
          ) : (
            <p>No movies available</p>
          )}
        </div>
      </div>

      <div className="pagination">
        <button
          onClick={() => setCurrentPage((prevPage) => Math.max(prevPage - 1, 1))}
          disabled={currentPage === 1}
        >
          Previous
        </button>

        {[...Array(totalPages)].map((_, index) => (
          <button
            key={index}
            onClick={() => setCurrentPage(index + 1)}
            className={currentPage === index + 1 ? "active" : ""}
          >
            {index + 1}
          </button>
        ))}

        <button
          onClick={() => setCurrentPage((prevPage) => prevPage + 1)}
          disabled={currentPage === totalPages}
        >
          Next
        </button>
      </div>

      <p>
        Page {currentPage} of {Math.ceil(filteredMovies.length / resultsPerPage)}
      </p>
    </main>
  );
};

export default Home;
