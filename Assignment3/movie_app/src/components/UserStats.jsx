import React, { useState, useEffect } from "react";
import { fetchUserStats } from "../api"; 
import { useAuth } from "../context/AuthContext";
import Header from "./Header";

const UserStats = () => {
  const { apiKey, userId } = useAuth();
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchStats = async () => {
      if (!apiKey || !userId) {
        setError("Please log in to view your statistics.");
        setLoading(false);
        return;
      }

      try {
        const data = await fetchUserStats(userId, apiKey);
        setStats(data);
        setLoading(false);
      } catch (error) {
        setError("Error fetching stats: " + error.message);
        setLoading(false);
      }
    };

    fetchStats();
  }, [apiKey, userId]);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>{error}</div>;

  return (
    <main>
    <Header />
    <div className="user-stats">
      <h1>Your Statistics</h1>
      {stats && (
      <div className="stats">
        <p>Total Movies Watched: {stats.total_movies_watched}</p>
        <p>Total Watch Time: {stats.total_watched_times} hours</p>
        <p>Average Rating: {parseFloat(stats.average_rating).toFixed(1)}</p>
        <p>Movies Planned to Watch: {stats.plan_to_watch}</p>
      </div>
      )}
    </div>
    </main>
  );
};

export default UserStats;
