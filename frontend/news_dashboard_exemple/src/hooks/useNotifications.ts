
import { useState, useEffect } from 'react';

export interface Notification {
  id: string;
  type: 'ct' | 'maintenance' | 'admin' | 'invoice' | 'mileage';
  priority: 'high' | 'medium' | 'low';
  title: string;
  message: string;
  vehicle?: string;
  date: string;
  status: 'read' | 'unread';
  category: string;
}

export const useNotifications = () => {
  const [notifications, setNotifications] = useState<Notification[]>([
    {
      id: '1',
      type: 'ct',
      priority: 'high',
      title: 'Contrôle technique expiré',
      message: 'Le CT du véhicule AB-123-CD a expiré le 15/03/2024',
      vehicle: 'AB-123-CD',
      date: '2024-03-16',
      status: 'unread',
      category: 'Urgent'
    },
    {
      id: '2',
      type: 'maintenance',
      priority: 'medium',
      title: 'Révision programmée',
      message: 'Révision des 20 000 km prévue pour EF-456-GH',
      vehicle: 'EF-456-GH',
      date: '2024-03-15',
      status: 'unread',
      category: 'Entretien'
    },
    {
      id: '3',
      type: 'admin',
      priority: 'medium',
      title: 'Assurance à renouveler',
      message: 'L\'assurance de IJ-789-KL expire dans 30 jours',
      vehicle: 'IJ-789-KL',
      date: '2024-03-14',
      status: 'unread',
      category: 'Administratif'
    }
  ]);

  const unreadCount = notifications.filter(n => n.status === 'unread').length;

  const markAsRead = (notificationId: string) => {
    setNotifications(prev => 
      prev.map(notification => 
        notification.id === notificationId 
          ? { ...notification, status: 'read' as const }
          : notification
      )
    );
  };

  const markAllAsRead = () => {
    setNotifications(prev => 
      prev.map(notification => ({ ...notification, status: 'read' as const }))
    );
  };

  const deleteNotification = (notificationId: string) => {
    setNotifications(prev => prev.filter(n => n.id !== notificationId));
  };

  return {
    notifications,
    unreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification
  };
};
