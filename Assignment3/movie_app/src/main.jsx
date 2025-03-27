import React from "react";
import ReactDOM from "react-dom/client";
import { AuthProvider } from './context/AuthContext';  
import { BrowserRouter, Routes, Route } from "react-router-dom";  
import Home from "./commponents/routes/Home";
import MovieDetails from "./commponents/routes/MovieDetails";
import WatchList from "./commponents/routes/WatchList";
import CompletedList from "./commponents/routes/CompletedList";
import Error from "./commponents/routes/Error";
import SearchResults from "./commponents/SearchResults";
import LoginPage from "./commponents/LoginPage";
import NotLoggedIn from "./commponents/NotLoggedIn";
import UserStats from "./commponents/UserStat";


const base = import.meta.env.BASE_URL;
console.log("url" + base);

ReactDOM.createRoot(document.getElementById("root")).render(
 
  <BrowserRouter basename={base}>  
    <AuthProvider>  
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/" element={ <Home /> } />

        <Route path="/movie/:id" element={ <MovieDetails /> } />

        <Route path="/watchlist" element={
        <NotLoggedIn>
          <WatchList />
        </NotLoggedIn>
        } />

        <Route path="/completedlist" element={
        <NotLoggedIn>
          <CompletedList />
        </NotLoggedIn>
        } />

        <Route path="/user-stats" element={
        <NotLoggedIn>
          <UserStats />
        </NotLoggedIn>
        } />

        <Route path="/search" element={<SearchResults />} />

        <Route path="*" element={<Error />} />  
      </Routes>
    </AuthProvider>
  </BrowserRouter>
);
