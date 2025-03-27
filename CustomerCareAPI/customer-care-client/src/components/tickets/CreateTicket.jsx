import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ticketService } from '../../services/api';

function CreateTicket() {
  const navigate = useNavigate();
  const [ticket, setTicket] = useState({
    title: '',
    description: '',
    priority: 'medium'
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setTicket(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await ticketService.createTicket(ticket);
      console.log('Ticket créé:', response.data);
      navigate(`/tickets/${response.data.id}`);
    } catch (err) {
      console.error('Erreur lors de la création du ticket:', err);
      setError(err.response?.data?.message || 'Impossible de créer le ticket. Veuillez réessayer.');
      setLoading(false);
    }
  };

  return (
    <div className="create-ticket">
      <button onClick={() => navigate('/tickets')} className="back-button">
        &larr; Retour à la liste
      </button>

      <h2>Créer un nouveau ticket</h2>
      {error && <div className="error-message">{error}</div>}

      <form onSubmit={handleSubmit} className="ticket-form">
        <div className="form-group">
          <label htmlFor="title">Titre</label>
          <input
            type="text"
            id="title"
            name="title"
            value={ticket.title}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-group">
          <label htmlFor="description">Description</label>
          <textarea
            id="description"
            name="description"
            value={ticket.description}
            onChange={handleChange}
            required
          ></textarea>
        </div>

        <div className="form-group">
          <label htmlFor="priority">Priorité</label>
          <select
            id="priority"
            name="priority"
            value={ticket.priority}
            onChange={handleChange}
          >
            <option value="low">Basse</option>
            <option value="medium">Moyenne</option>
            <option value="high">Haute</option>
          </select>
        </div>

        <button type="submit" disabled={loading}>
          {loading ? 'Création en cours...' : 'Créer le ticket'}
        </button>
      </form>
    </div>
  );
}

export default CreateTicket;