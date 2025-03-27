import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/SearchBar.css";

const SearchBar = () => {
  const [query, setQuery] = useState(""); // Search input state
  const navigate = useNavigate();

  const handleSearch = (e) => {
    e.preventDefault();
    if (query.trim()) {
      navigate(`/search?search=${encodeURIComponent(query)}`);
    }
  };

  return (
    <form onSubmit={handleSearch}>
      <input
        type="text"
        placeholder="Search for a movie..."
        value={query}
        onChange={(e) => setQuery(e.target.value)}
      />
      <button className="search-button" type="submit"></button>
    </form>
  );
};

export default SearchBar;
