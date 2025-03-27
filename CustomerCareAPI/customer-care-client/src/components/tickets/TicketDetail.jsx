import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ticketService } from '../../services/api';

function TicketDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [ticket, setTicket] = useState(null);
  const [responses, setResponses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [newResponse, setNewResponse] = useState('');

  useEffect(() => {
    const fetchTicketDetails = async () => {
      try {
        const response = await ticketService.getTicket(id);
        setTicket(response.data.ticket || response.data);
        setResponses(response.data.responses || []);
        setLoading(false);
      } catch (err) {
        console.error("Erreur lors du chargement du ticket:", err);
        setError("Impossible de charger les détails du ticket.");
        setLoading(false);
      }
    };

    fetchTicketDetails();
  }, [id]);

  const handleSubmitResponse = async (e) => {
    e.preventDefault();
    if (!newResponse.trim()) return;

    try {
      const response = await ticketService.addResponse(id, { content: newResponse });
      setResponses([...responses, response.data]);
      setNewResponse('');
    } catch (err) {
      console.error("Erreur lors de l'ajout de la réponse:", err);
      alert("Impossible d'ajouter votre réponse. Veuillez réessayer.");
    }
  };

  const handleStatusChange = async (newStatus) => {
    try {
      await ticketService.changeStatus(id, newStatus);
      setTicket({ ...ticket, status: newStatus });
    } catch (err) {
      console.error("Erreur lors du changement de statut:", err);
      alert("Impossible de changer le statut. Veuillez réessayer.");
    }
  };

  if (loading) return <div>Chargement des détails du ticket...</div>;
  if (error) return <div style={{ color: 'red' }}>{error}</div>;
  if (!ticket) return <div>Ticket non trouvé</div>;

  return (
    <div className="ticket-detail">
      <button onClick={() => navigate('/tickets')} className="back-button">
        &larr; Retour à la liste
      </button>

      <div className="ticket-header">
        <h2>{ticket.title}</h2>
        <div className="ticket-status">
          <span>Statut: </span>
          <select 
            value={ticket.status} 
            onChange={(e) => handleStatusChange(e.target.value)}
          >
            <option value="open">Ouvert</option>
            <option value="in_progress">En cours</option>
            <option value="resolved">Résolu</option>
            <option value="closed">Fermé</option>
          </select>
        </div>
      </div>

      <div className="ticket-info">
        <p><strong>Priorité:</strong> {ticket.priority}</p>
        <p><strong>Créé le:</strong> {new Date(ticket.created_at).toLocaleDateString()}</p>
        <p><strong>Dernière mise à jour:</strong> {new Date(ticket.updated_at).toLocaleDateString()}</p>
      </div>

      <div className="ticket-description">
        <h3>Description</h3>
        <p>{ticket.description}</p>
      </div>

      <div className="ticket-responses">
        <h3>Réponses</h3>
        {responses.length === 0 ? (
          <p>Aucune réponse pour ce ticket.</p>
        ) : (
          <ul className="response-list">
            {responses.map(response => (
              <li key={response.id} className="response-item">
                <div className="response-header">
                  <span className="response-author">{response.user?.name || 'Utilisateur'}</span>
                  <span className="response-date">{new Date(response.created_at).toLocaleString()}</span>
                </div>
                <div className="response-content">{response.content}</div>
              </li>
            ))}
          </ul>
        )}

        <form onSubmit={handleSubmitResponse} className="response-form">
          <h4>Ajouter une réponse</h4>
          <textarea
            value={newResponse}
            onChange={(e) => setNewResponse(e.target.value)}
            placeholder="Votre réponse..."
            required
          ></textarea>
          <button type="submit">Envoyer</button>
        </form>
      </div>
    </div>
  );
}
export default TicketDetail;