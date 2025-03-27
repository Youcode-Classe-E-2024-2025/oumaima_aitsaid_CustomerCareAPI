import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Login from './components/auth/Login';
import Dashboard from './components/dashboard/Dashboard';
import TicketList from './components/tickets/TicketList';
import TicketDetail from './components/tickets/TicketDetail';
import CreateTicket from './components/tickets/CreateTicket';
import UserList from './components/users/UserList';
import Navbar from './components/layout/Navbar';
import './App.css';

// Composant pour vérifier l'authentification
const PrivateRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  return token ? children : <Navigate to="/login" />;
};

// Composant pour vérifier les droits d'administrateur
const AdminRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  
  if (!token) {
    return <Navigate to="/login" />;
  }
  
  if (user.role !== 'admin') {
    return <Navigate to="/" />;
  }
  
  return children;
};

function App() {
  return (
    <Router>
      <div className="App">
        <Navbar />
        <main className="main-content">
          <Routes>
            <Route path="/login" element={<Login />} />
            
            <Route 
              path="/" 
              element={
                <PrivateRoute>
                  <Dashboard />
                </PrivateRoute>
              } 
            />
            
            <Route 
              path="/tickets/create" 
              element={
                <PrivateRoute>
                  <CreateTicket />
                </PrivateRoute>
              } 
            />
            
            <Route 
              path="/tickets/:id" 
              element={
                <PrivateRoute>
                  <TicketDetail />
                </PrivateRoute>
              } 
            />
            
            <Route 
              path="/tickets" 
              element={
                <PrivateRoute>
                  <TicketList />
                </PrivateRoute>
              } 
            />
            
            <Route 
              path="/users" 
              element={
                <AdminRoute>
                  <UserList />
                </AdminRoute>
              } 
            />
          </Routes>
        </main>
      </div>
    </Router>
  );
}

export default App;
