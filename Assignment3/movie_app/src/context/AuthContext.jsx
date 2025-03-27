import React, { createContext, useState, useContext, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const AuthContext = createContext();
export const useAuth = () => {
  return useContext(AuthContext);
};

export const AuthProvider = ({ children }) => {
  const [apiKey, setApiKey] = useState(null);
  const [userId, setUserId] = useState(null);
  const [loading, setLoading] = useState(true); 
  const navigate = useNavigate();

  useEffect(() => {
    const storedApiKey = localStorage.getItem('apiKey');
    const storedExpiryTime = localStorage.getItem('apiKeyExpiry');
    const storedUserId = localStorage.getItem('userId');
    
    console.log('Stored API Key:', storedApiKey); // debug
    console.log('Stored Expiry Time:', storedExpiryTime); // debug
    console.log('Stored User ID:', storedUserId); 

    if (storedApiKey && storedExpiryTime && storedUserId) {
      const currentTime = Date.now();
      console.log('Current time:', currentTime);

      if (currentTime < parseInt(storedExpiryTime)) {
        setApiKey(storedApiKey); // set api key 
        setUserId(storedUserId);
      } else {
        // remove api key in local storage 
        localStorage.removeItem('apiKey');
        localStorage.removeItem('apiKeyExpiry');
        localStorage.removeItem('userId');
        setApiKey(null);
        setUserId(null);
        console.log('API key has expired');
        // navigate('/login'); // redirect to log in page if time expires
      }
    // } else {
    //   console.log('No API key or userId found in localStorage');
    //   navigate('/login'); // no api key is found (not logged in)
    }

    setLoading(false); 
  }, []);

  const login = (username, password) => {
    fetch('https://loki.trentu.ca/~litran/3430/assn/assn2-tlinhh10102003/api/users/session', {
      method: 'POST',
      body: JSON.stringify({ username, password }),
      headers: {
        'Content-Type': 'application/json',
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Failed to authenticate');
        }
        return response.json();
      })
      .then((data) => {
        if (data['Your API key'] && data.user_id) {
          const apiKey = data['Your API key'];
          const userId = data.user_id;
          setApiKey(apiKey);
          setUserId(userId);

          const expirationTime = Date.now() + 3600000; // 1 hour expiry
          localStorage.setItem('apiKey', apiKey);
          localStorage.setItem('apiKeyExpiry', expirationTime.toString());
          localStorage.setItem('userId', userId)

          console.log('API key and User ID stored in localStorage');
          navigate('/'); // naviagte to home after successful login
        } else {
          console.log('API key or User ID not found in response:', data);
          alert('Invalid credentials');
        }
      })
      .catch((error) => {
        console.error('Error logging in:', error);
        alert('Error logging in: ' + error.message);
      });
  };

  const logout = () => {
    setApiKey(null);
    setUserId(null); 
    localStorage.removeItem('apiKey');
    localStorage.removeItem('apiKeyExpiry');
    localStorage.removeItem('userId'); 
    console.log('Logged out');
    navigate('/login');
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <AuthContext.Provider value={{ apiKey, userId, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};
