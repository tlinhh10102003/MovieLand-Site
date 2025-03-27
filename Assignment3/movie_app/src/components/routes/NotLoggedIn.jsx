import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const ProtectedRoute = ({ children }) => {
  const { apiKey, userId } = useAuth();
  if (!apiKey || !userId) {
    return <Navigate to="/login" replace />;
  }
  return children;
};

export default ProtectedRoute;
