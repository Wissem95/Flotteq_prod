
import { useState, useEffect } from 'react';

export const useCollapsibleState = (key: string, defaultValue: boolean = true) => {
  const [isExpanded, setIsExpanded] = useState<boolean>(() => {
    try {
      const storedValue = localStorage.getItem(`collapsible_${key}`);
      return storedValue !== null ? JSON.parse(storedValue) : defaultValue;
    } catch (error) {
      console.warn(`Error reading localStorage for key ${key}:`, error);
      return defaultValue;
    }
  });

  const toggle = () => {
    const newValue = !isExpanded;
    setIsExpanded(newValue);
    try {
      localStorage.setItem(`collapsible_${key}`, JSON.stringify(newValue));
    } catch (error) {
      console.warn(`Error saving to localStorage for key ${key}:`, error);
    }
  };

  const setExpanded = (value: boolean) => {
    setIsExpanded(value);
    try {
      localStorage.setItem(`collapsible_${key}`, JSON.stringify(value));
    } catch (error) {
      console.warn(`Error saving to localStorage for key ${key}:`, error);
    }
  };

  // Synchroniser avec les changements de localStorage depuis d'autres onglets
  useEffect(() => {
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === `collapsible_${key}` && e.newValue !== null) {
        try {
          setIsExpanded(JSON.parse(e.newValue));
        } catch (error) {
          console.warn(`Error parsing localStorage value for key ${key}:`, error);
        }
      }
    };

    window.addEventListener('storage', handleStorageChange);
    return () => window.removeEventListener('storage', handleStorageChange);
  }, [key]);

  return {
    isExpanded,
    toggle,
    setExpanded
  };
};
