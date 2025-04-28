import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './Auth.css';

const Register = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    confirmPassword: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    const registerUser = async (userData) => {
      try {
        const response = await fetch('http://127.0.0.1:8080/api/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            name: userData.name,
            email: userData.email,
            password: userData.password,
            password_confirmation: userData.confirmPassword
          }),
        });
        
        const data = await response.json();
        
        if (!response.ok) {
          // For validation errors (422), show the specific validation message
          if (response.status === 422 && data.errors) {
            // Get the first error message
            const firstError = Object.values(data.errors)[0];
            throw new Error(firstError[0] || 'Validation error');
          }
          throw new Error(data.message || 'Registration failed');
        }
        
        console.log('Registration successful:', data);
        // Navigate to login page after successful registration
        navigate('/login');
        
        return data;
      } catch (error) {
        setError(error.message || 'Registration failed. Please try again.');
        console.error('Registration error:', error);
      } finally {
        setLoading(false);
      }
    };

    // This effect will run when form is submitted
    if (loading) {
      registerUser(formData);
    }
  }, [formData, loading, navigate]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    // Check if passwords match
    if (formData.password !== formData.confirmPassword) {
      setError("Passwords don't match!");
      return;
    }
    
    // Clear any previous errors
    setError(null);
    setLoading(true);
  };

  return (
    <div className="auth-page">
      <div className="auth-form-container">
        <h2>Register</h2>
        {error && <div className="error-message">{error}</div>}
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="name">Full Name</label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
            />
          </div>
          <div className="form-group">
            <label htmlFor="email">Email</label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
            />
          </div>
          <div className="form-group">
            <label htmlFor="password">Password</label>
            <input
              type="password"
              id="password"
              name="password"
              value={formData.password}
              onChange={handleChange}
              required
            />
          </div>
          <div className="form-group">
            <label htmlFor="confirmPassword">Confirm Password</label>
            <input
              type="password"
              id="confirmPassword"
              name="confirmPassword"
              value={formData.confirmPassword}
              onChange={handleChange}
              required
            />
          </div>
          <button 
            type="submit" 
            className="submit-btn"
            disabled={loading}
          >
            {loading ? 'Registering...' : 'Register'}
          </button>
        </form>
        <p className="auth-redirect">
          Already have an account? <Link to="/login">Login here</Link>
        </p>
      </div>
    </div>
  );
};

export default Register;