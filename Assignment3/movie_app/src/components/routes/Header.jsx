import React from "react";
import { NavLink } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import SearchBar from "./SearchBar";
import "../styles/Header.css";

const Header = () => {
  const { apiKey, logout } = useAuth();

  return (
    <header className="header">
      <h1>
        <NavLink to="/">MovieLand</NavLink>
      </h1>

      <nav>
        <NavLink to="/">Home</NavLink>
        <NavLink to="/watchlist">Watch List</NavLink>
        <NavLink to="/completedlist">Completed List</NavLink>
        <NavLink to="/user-stats">User Stats</NavLink> 
      </nav>

      {apiKey ? (
        <button onClick={logout} className="logout-button">Log Out</button>
      ) : (
        <NavLink to="/login" className="login-button">
          Log In
        </NavLink>
      )}
      
      <SearchBar />
    </header>
  );
};

export default Header;
