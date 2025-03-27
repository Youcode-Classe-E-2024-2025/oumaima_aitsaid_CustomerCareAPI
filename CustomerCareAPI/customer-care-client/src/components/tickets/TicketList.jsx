import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ticketService } from '../../services/api';
import TicketFilters from './TicketFilters';

function TicketList() {
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filters, setFilters] = useState({
    search: '',
    status: '',
    priority: '',
    sort: 'newest'
  });
  const [pagination, setPagination] = useState({
    currentPage: 1,
    totalPages: 1,
    totalItems: 0
  });
  const navigate = useNavigate();

  const loadTickets = async (page = 1, filterParams = filters) => {
    setLoading(true);
    try {
      // Préparer les paramètres pour l'API
      const params = {
        page,
        search: filterParams.search,
        status: filterParams.status,
        priority: filterParams.priority,
        sort: filterParams.sort
      };

      const response = await ticketService.getTickets(params);
      console.log("Réponse de l'API:", response.data);
      
      // Gérer différentes structures de réponse API
      if (response.data.data) {
        // Si la réponse a une structure paginée
        setTickets(response.data.data);
        setPagination({
          currentPage: response.data.current_page || 1,
          totalPages: response.data.last_page || 1,
          totalItems: response.data.total || response.data.data.length
        });
      } else if (Array.isArray(response.data)) {
        // Si la réponse est un tableau simple
        setTickets(response.data);
        setPagination({
          currentPage: 1,
          totalPages: 1,
          totalItems: response.data.length
        });
      }
      
      setLoading(false);
    } catch (err) {
      console.error("Erreur lors du chargement des tickets:", err);
      
      if (err.response && err.response.status === 401) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        navigate('/login');
      } else {
        setError("Impossible de charger les tickets. Veuillez réessayer plus tard.");
        setLoading(false);
      }
    }
  };

  useEffect(() => {
    loadTickets();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [navigate]);

  const handlePageChange = (page) => {
    loadTickets(page);
  };

  const applyFilters = (customFilters = filters) => {
    loadTickets(1, customFilters);
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  // Fonction pour obtenir la classe CSS en fonction du statut
  const getStatusClass = (status) => {
    switch (status) {
      case 'open': return 'status-open';
      case 'in_progress': return 'status-progress';
      case 'resolved': return 'status-resolved';
      case 'closed': return 'status-closed';
      default: return '';
    }
  };

  // Fonction pour formater le statut
  const formatStatus = (status) => {
    switch (status) {
      case 'open': return 'Ouvert';
      case 'in_progress': return 'En cours';
      case 'resolved': return 'Résolu';
      case 'closed': return 'Fermé';
      default: return status;
    }
  };

  if (loading && tickets.length === 0) return <div>Chargement...</div>;
  if (error) return <div style={{ color: 'red' }}>{error}</div>;

  return (
    <div className="ticket-list-container">
      <div className="header-actions">
        <h2>Liste des tickets</h2>
        <div>
          <Link to="/tickets/create" className="create-button">Nouveau ticket</Link>
          <button onClick={handleLogout} className="logout-button">Déconnexion</button>
        </div>
      </div>
      
      <TicketFilters 
        filters={filters} 
        setFilters={setFilters} 
        applyFilters={applyFilters} 
      />
      
      {loading ? (
        <div className="loading-overlay">Chargement...</div>
      ) : tickets.length === 0 ? (
        <div className="no-tickets">
          <p>Aucun ticket trouvé</p>
          {(filters.search || filters.status || filters.priority) && (
            <p>Essayez de modifier vos filtres pour voir plus de résultats.</p>
          )}
        </div>
      ) : (
        <>
          <div className="tickets-grid">
            {tickets.map(ticket => (
              <div key={ticket.id} className="ticket-card" onClick={() => navigate(`/tickets/${ticket.id}`)}>
                <div className="ticket-card-header">
                  <h3>{ticket.title}</h3>
                  <span className={`status-badge ${getStatusClass(ticket.status)}`}>
                    {formatStatus(ticket.status)}
                  </span>
                </div>
                <div className="ticket-card-content">
                  <p className="ticket-description">
                    {ticket.description.substring(0, 100)}
                    {ticket.description.length > 100 ? '...' : ''}
                  </p>
                  <div className="ticket-meta">
                    <span className={`priority-badge priority-${ticket.priority}`}>
                      {ticket.priority === 'high' ? 'Haute' : ticket.priority === 'medium' ? 'Moyenne' : 'Basse'}
                    </span>
                    <span className="ticket-date">
                      {new Date(ticket.created_at).toLocaleDateString()}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
          
          {pagination.totalPages > 1 && (
            <div className="pagination">
              <button 
                onClick={() => handlePageChange(pagination.currentPage - 1)}
                disabled={pagination.currentPage === 1}
                className="pagination-button"
              >
                &laquo; Précédent
              </button>
              
              <span className="pagination-info">
                Page {pagination.currentPage} sur {pagination.totalPages}
              </span>
              
              <button 
                onClick={() => handlePageChange(pagination.currentPage + 1)}
                disabled={pagination.currentPage === pagination.totalPages}
                className="pagination-button"
              >
                Suivant &raquo;
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}

export default TicketList;
