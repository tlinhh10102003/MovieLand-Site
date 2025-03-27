import { useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import Header from "../Header"
import { getMovieById } from "../../api";
import AddToWatchlist from "../AddToWatchList";

const MovieDetails = () => {
  const { id } = useParams();
  const [movie, setMovie] = useState(null);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchMovieDetails = async () => {
      try {
        const data = await getMovieById(id);
        console.log("Movie details data:", data);
        setMovie(data);
      } catch (error) {
        console.error("Error fetching movie details:", error);
      }
    };

    fetchMovieDetails();
  }, [id]);

  if (error) return <p>{error}</p>;
  if (!movie) return <p>Loading...</p>;

  return (
    <main>
      <Header />
      <div className="movie-detail">
        {movie ? (
          <div className="movie-card">
            <h1>{movie.title}</h1>
            <img src={movie.poster} alt={movie.title}/>
            <div className="details">
              <p><strong>Movie Description:</strong> {movie.overview}</p>
              <p><strong>Homepage:</strong> {movie.homepage}</p>
              <p><strong>Runtime:</strong> {movie.runtime} minutes</p>
              <p><strong>Tagline:</strong> {movie.tagline}</p>
              <p><strong>Rating:</strong> {movie.rating}</p>
              <p><strong>Release Date:</strong> {movie.release_date}</p>
            </div>
            
            <AddToWatchlist movieId={id}/>
          </div>
        ) : (
          <p>Loading movie details...</p>
        )}
      </div>
    </main>
  );
};

export default MovieDetails;
