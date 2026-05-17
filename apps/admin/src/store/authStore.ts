import { create } from 'zustand';

interface User {
  id: number;
  name: string;
  email: string;
  roles: string[];
}

interface AuthState {
  user: User | null;
  accessToken: string | null;
  refreshToken: string | null;
  isAuthenticated: boolean;
  isInitializing: boolean;
  
  init: () => void;
  setTokens: (access: string, refresh: string) => void;
  setUser: (user: User) => void;
  logout: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  accessToken: null,
  refreshToken: null,
  isAuthenticated: false,
  isInitializing: true,

  init: () => {
    const storedAccess = localStorage.getItem('kb_access');
    const storedRefresh = localStorage.getItem('kb_refresh');
    const storedUser = localStorage.getItem('kb_user');
    
    if (storedAccess && storedRefresh && storedUser) {
      set({ 
        accessToken: storedAccess, 
        refreshToken: storedRefresh, 
        user: JSON.parse(storedUser),
        isAuthenticated: true,
        isInitializing: false
      });
    } else {
      set({ isInitializing: false });
    }
  },

  setTokens: (access, refresh) => {
    localStorage.setItem('kb_access', access);
    localStorage.setItem('kb_refresh', refresh);
    set({ accessToken: access, refreshToken: refresh, isAuthenticated: true });
  },

  setUser: (user) => {
    localStorage.setItem('kb_user', JSON.stringify(user));
    set({ user });
  },

  logout: () => {
    localStorage.removeItem('kb_access');
    localStorage.removeItem('kb_refresh');
    localStorage.removeItem('kb_user');
    set({ user: null, accessToken: null, refreshToken: null, isAuthenticated: false });
  }
}));
