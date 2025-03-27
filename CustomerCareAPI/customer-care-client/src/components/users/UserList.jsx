import React, { useState, useEffect } from 'react';
import { userService } from '../../services/api';

function UserList() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedUser, setSelectedUser] = useState(null);
  const [formMode, setFormMode] = useState(null); // 'create', 'edit', or null
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    role: 'user'
  });

  useEffect(() => {
    loadUsers();
  }, []);

  const loadUsers = async () => {
    setLoading(true);
    try {
      const response = await userService.getUsers();
      setUsers(response.data.data || response.data);
      setLoading(false);
    } catch (err) {
      console.error("Erreur lors du chargement des utilisateurs:", err);
      setError("Impossible de charger la liste des utilisateurs.");
      setLoading(false);
    }
  };

  const handleCreateUser = () => {
    setFormMode('create');
    setFormData({
      name: '',
      email: '',
      password: '',
      role: 'user'
    });
  };

  const handleEditUser = (user) => {
    setFormMode('edit');
    setSelectedUser(user);
    setFormData({
      name: user.name,
      email: user.email,
      password: '', // Ne pas remplir le mot de passe pour des raisons de sécurité
      role: user.role || 'user'
    });
  };

  const handleCloseForm = () => {
    setFormMode(null);
    setSelectedUser(null);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      if (formMode === 'create') {
        await userService.createUser(formData);
        alert('Utilisateur créé avec succès!');
      } else if (formMode === 'edit' && selectedUser) {
        // Si le mot de passe est vide, ne pas l'inclure dans la mise à jour
        const dataToUpdate = { ...formData };
        if (!dataToUpdate.password) {
          delete dataToUpdate.password;
        }
        
        await userService.updateUser(selectedUser.id, dataToUpdate);
        alert('Utilisateur mis à jour avec succès!');
      }
      
      // Recharger la liste des utilisateurs et fermer le formulaire
      loadUsers();
      handleCloseForm();
    } catch (err) {
      console.error("Erreur lors de l'opération:", err);
      alert(`Erreur: ${err.response?.data?.message || 'Une erreur est survenue'}`);
    }
  };

  const handleDeleteUser = async (userId) => {
    if (!window.confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')) {
      return;
    }
    
    try {
      await userService.deleteUser(userId);
      alert('Utilisateur supprimé avec succès!');
      loadUsers();
    } catch (err) {
      console.error("Erreur lors de la suppression:", err);
      alert(`Erreur: ${err.response?.data?.message || 'Une erreur est survenue'}`);
    }
  };

  if (loading && users.length === 0) return <div>Chargement des utilisateurs...</div>;
  if (error) return <div style={{ color: 'red' }}>{error}</div>;

  return (
    <div className="user-management">
      <div className="section-header">
        <h2>Gestion des utilisateurs</h2>
        <button onClick={handleCreateUser} className="create-button">
          Nouvel utilisateur
        </button>
      </div>
      
      {formMode && (
        <div className="user-form-container">
          <div className="user-form-overlay" onClick={handleCloseForm}></div>
          <div className="user-form">
            <h3>{formMode === 'create' ? 'Créer un utilisateur' : 'Modifier un utilisateur'}</h3>
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label htmlFor="name">Nom</label>
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
                <label htmlFor="password">
                  {formMode === 'create' ? 'Mot de passe' : 'Nouveau mot de passe (laisser vide pour ne pas changer)'}
                </label>
                <input
                  type="password"
                  id="password"
                  name="password"
                  value={formData.password}
                  onChange={handleChange}
                  required={formMode === 'create'}
                />
              </div>
              
              <div className="form-group">
                <label htmlFor="role">Rôle</label>
                <select
                  id="role"
                  name="role"
                  value={formData.role}
                  onChange={handleChange}
                >
                  <option value="user">Utilisateur</option>
                  <option value="admin">Administrateur</option>
                </select>
              </div>
              
              <div className="form-actions">
                <button type="button" onClick={handleCloseForm} className="cancel-button">
                  Annuler
                </button>
                <button type="submit" className="submit-button">
                  {formMode === 'create' ? 'Créer' : 'Mettre à jour'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
      
      {users.length === 0 ? (
        <p>Aucun utilisateur trouvé</p>
      ) : (
        <table className="users-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Rôle</th>
              <th>Date de création</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {users.map(user => (
              <tr key={user.id}>
                <td>{user.id}</td>
                <td>{user.name}</td>
                <td>{user.email}</td>
                <td>{user.role === 'admin' ? 'Administrateur' : 'Utilisateur'}</td>
                <td>{new Date(user.created_at).toLocaleDateString()}</td>
                <td className="actions-cell">
                  <button 
                    onClick={() => handleEditUser(user)} 
                    className="edit-button"
                  >
                    Modifier
                  </button>
                  <button 
                    onClick={() => handleDeleteUser(user.id)} 
                    className="delete-button"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}

export default UserList;
