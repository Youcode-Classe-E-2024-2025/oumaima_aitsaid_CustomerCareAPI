import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

// Créer une instance axios avec la configuration de base
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Intercepteur pour ajouter le token d'authentification à chaque requête
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// Services d'authentification
export const authService = {
  login: (credentials) => api.post('/login', credentials),
  register: (userData) => api.post('/register', userData),
  logout: () => api.post('/logout'),
  getProfile: () => api.get('/user')
};

// Services de gestion des tickets
export const ticketService = {
  // Récupérer tous les tickets avec filtres optionnels
  getTickets: (params = {}) => api.get('/tickets', { params }),
  
  // Récupérer un ticket spécifique
  getTicket: (id) => api.get(`/tickets/${id}`),
  
  // Créer un nouveau ticket
  createTicket: (ticketData) => api.post('/tickets', ticketData),
  
  // Mettre à jour un ticket
  updateTicket: (id, ticketData) => api.put(`/tickets/${id}`, ticketData),
  
  // Changer le statut d'un ticket
  changeStatus: (id, status) => api.patch(`/tickets/${id}/status`, { status }),
  
  // Ajouter une réponse à un ticket
  addResponse: (id, responseData) => api.post(`/tickets/${id}/responses`, responseData),
  
  // Supprimer un ticket
  deleteTicket: (id) => api.delete(`/tickets/${id}`)
};

// Services de gestion des utilisateurs
export const userService = {
  // Récupérer tous les utilisateurs
  getUsers: () => api.get('/users'),
  
  // Récupérer un utilisateur spécifique
  getUser: (id) => api.get(`/users/${id}`),
  
  // Créer un nouvel utilisateur
  createUser: (userData) => api.post('/users', userData),
  
  // Mettre à jour un utilisateur
  updateUser: (id, userData) => api.put(`/users/${id}`, userData),
  
  // Supprimer un utilisateur
  deleteUser: (id) => api.delete(`/users/${id}`)
};

export default api;
