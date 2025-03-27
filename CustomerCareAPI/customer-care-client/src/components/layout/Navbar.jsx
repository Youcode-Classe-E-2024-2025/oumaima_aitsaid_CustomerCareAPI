import React from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';

function Navbar() {
  const location = useLocation();
  const navigate = useNavigate();
  const user = JSON.parse(localStorage.getItem('user') || '{}');

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  // Vérifier si l'utilisateur est connecté
  if (!localStorage.getItem('token')) {
    return null; // Ne pas afficher la barre de navigation si l'utilisateur n'est pas connecté
  }

  return (
    <nav className="navbar">
      <div className="navbar-brand">
        <Link to="/">Service Client</Link>
      </div>
      
      <ul className="navbar-nav">
        <li className={location.pathname === '/' ? 'active' : ''}>
          <Link to="/">Tableau de bord</Link>
        </li>
        <li className={location.pathname.startsWith('/tickets') ? 'active' : ''}>
          <Link to="/tickets">Tickets</Link>
        </li>
        {user.role === 'admin' && (
          <li className={location.pathname.startsWith('/users') ? 'active' : ''}>
            <Link to="/users">Utilisateurs</Link>
          </li>
        )}
      </ul>
      
      <div className="navbar-user">
        <span className="user-name">{user.name || 'Utilisateur'}</span>
        <button onClick={handleLogout} className="logout-button">Déconnexion</button>
      </div>
    </nav>
  );
}

export default Navbar;
