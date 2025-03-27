import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ticketService } from '../../services/api';

function Dashboard() {
  const [stats, setStats] = useState({
    total: 0,
    open: 0,
    inProgress: 0,
    resolved: 0,
    closed: 0,
    highPriority: 0
  });
  const [recentTickets, setRecentTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const loadDashboardData = async () => {
      try {
        // Dans un environnement réel, vous auriez un endpoint spécifique pour les statistiques
        // Ici, nous simulons en récupérant tous les tickets et en calculant les statistiques
        const response = await ticketService.getTickets();
        const tickets = response.data.data || response.data;
        
        // Calculer les statistiques
        const statsData = {
          total: tickets.length,
          open: tickets.filter(t => t.status === 'open').length,
          inProgress: tickets.filter(t => t.status === 'in_progress').length,
          resolved: tickets.filter(t => t.status === 'resolved').length,
          closed: tickets.filter(t => t.status === 'closed').length,
          highPriority: tickets.filter(t => t.priority === 'high').length
        };
        
        setStats(statsData);
        
        // Récupérer les 5 tickets les plus récents
        const sortedTickets = [...tickets].sort((a, b) => 
          new Date(b.created_at) - new Date(a.created_at)
        ).slice(0, 5);
        
        setRecentTickets(sortedTickets);
        setLoading(false);
      } catch (err) {
        console.error("Erreur lors du chargement des données du tableau de bord:", err);
        setError("Impossible de charger les données du tableau de bord.");
        setLoading(false);
      }
    };

    loadDashboardData();
  }, []);

  if (loading) return <div>Chargement du tableau de bord...</div>;
  if (error) return <div style={{ color: 'red' }}>{error}</div>;

  return (
    <div className="dashboard">
      <h2>Tableau de bord</h2>
      
      <div className="stats-grid">
        <div className="stat-card total">
          <h3>Total des tickets</h3>
          <div className="stat-value">{stats.total}</div>
        </div>
        
        <div className="stat-card open">
          <h3>Tickets ouverts</h3>
          <div className="stat-value">{stats.open}</div>
        </div>
        
        <div className="stat-card in-progress">
          <h3>En cours</h3>
          <div className="stat-value">{stats.inProgress}</div>
        </div>
        
        <div className="stat-card resolved">
          <h3>Résolus</h3>
          <div className="stat-value">{stats.resolved}</div>
        </div>
        
        <div className="stat-card closed">
          <h3>Fermés</h3>
          <div className="stat-value">{stats.closed}</div>
        </div>
        
        <div className="stat-card high-priority">
          <h3>Haute priorité</h3>
          <div className="stat-value">{stats.highPriority}</div>
        </div>
      </div>
      
      <div className="recent-tickets">
        <div className="section-header">
          <h3>Tickets récents</h3>
          <Link to="/tickets" className="view-all">Voir tous les tickets</Link>
        </div>
        
        {recentTickets.length === 0 ? (
          <p>Aucun ticket récent</p>
        ) : (
          <table className="tickets-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Date de création</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {recentTickets.map(ticket => (
                <tr key={ticket.id}>
                  <td>#{ticket.id}</td>
                  <td>{ticket.title}</td>
                  <td>
                  <span className={`status-badge ${getStatusClass(ticket.status)}`}>
                      {formatStatus(ticket.status)}
                    </span>
                  </td>
                  <td>
                    <span className={`priority-badge priority-${ticket.priority}`}>
                      {ticket.priority === 'high' ? 'Haute' : ticket.priority === 'medium' ? 'Moyenne' : 'Basse'}
                    </span>
                  </td>
                  <td>{new Date(ticket.created_at).toLocaleDateString()}</td>
                  <td>
                    <Link to={`/tickets/${ticket.id}`} className="view-button">
                      Voir
                    </Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
  
  // Fonction pour obtenir la classe CSS en fonction du statut
  function getStatusClass(status) {
    switch (status) {
      case 'open': return 'status-open';
      case 'in_progress': return 'status-progress';
      case 'resolved': return 'status-resolved';
      case 'closed': return 'status-closed';
      default: return '';
    }
  }

  // Fonction pour formater le statut
  function formatStatus(status) {
    switch (status) {
      case 'open': return 'Ouvert';
      case 'in_progress': return 'En cours';
      case 'resolved': return 'Résolu';
      case 'closed': return 'Fermé';
      default: return status;
    }
  }
}

export default Dashboard;
