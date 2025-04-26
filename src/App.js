import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Header from './components/Header/Header';
import Blogs from './components/Blog/Blojs';
import Login from './components/Auth/Login';
import Register from './components/Auth/Register';
import './App.css';

function App() {
  return (
    <Router>
      <div className="App">
        <Header />
        <main className="main-content">
          <Routes>
            <Route path="/" element={<Blogs />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            {/* Add more routes as needed */}
          </Routes>
        </main>
      </div>gi
    </Router>
  );
}

export default App;