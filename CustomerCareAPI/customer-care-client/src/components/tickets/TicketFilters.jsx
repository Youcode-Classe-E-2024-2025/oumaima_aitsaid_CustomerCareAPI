import React from 'react';

function TicketFilters({ filters, setFilters, applyFilters }) {
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    applyFilters();
  };

  const handleReset = () => {
    setFilters({
      search: '',
      status: '',
      priority: '',
      sort: 'newest'
    });
    // Appliquer les filtres réinitialisés
    applyFilters({
      search: '',
      status: '',
      priority: '',
      sort: 'newest'
    });
  };

  return (
    <div className="ticket-filters">
      <form onSubmit={handleSubmit}>
        <div className="filters-row">
          <div className="filter-group">
            <input
              type="text"
              name="search"
              placeholder="Rechercher..."
              value={filters.search}
              onChange={handleChange}
              className="search-input"
            />
          </div>

          <div className="filter-group">
            <select
              name="status"
              value={filters.status}
              onChange={handleChange}
              className="filter-select"
            >
              <option value="">Tous les statuts</option>
              <option value="open">Ouvert</option>
              <option value="in_progress">En cours</option>
              <option value="resolved">Résolu</option>
              <option value="closed">Fermé</option>
            </select>
          </div>

          <div className="filter-group">
            <select
              name="priority"
              value={filters.priority}
              onChange={handleChange}
              className="filter-select"
            >
              <option value="">Toutes les priorités</option>
              <option value="low">Basse</option>
              <option value="medium">Moyenne</option>
              <option value="high">Haute</option>
            </select>
          </div>

          <div className="filter-group">
            <select
              name="sort"
              value={filters.sort}
              onChange={handleChange}
              className="filter-select"
            >
              <option value="newest">Plus récents</option>
              <option value="oldest">Plus anciens</option>
              <option value="priority_high">Priorité (haute → basse)</option>
              <option value="priority_low">Priorité (basse → haute)</option>
            </select>
          </div>

          <div className="filter-actions">
            <button type="submit" className="filter-button">Filtrer</button>
            <button type="button" onClick={handleReset} className="reset-button">Réinitialiser</button>
          </div>
        </div>
      </form>
    </div>
  );
}

export default TicketFilters;
