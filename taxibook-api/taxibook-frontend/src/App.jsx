import { useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, useLocation } from 'react-router-dom';
import BookingWizard from './components/BookingWizard/BookingWizard';
import TipPayment from './pages/TipPayment';
import TipSuccess from './pages/TipSuccess';
import TipAlready from './pages/TipAlready';
import { HotjarTracking } from './services/hotjarTracking';

// Component to track page views
function PageViewTracker() {
  const location = useLocation();
  
  useEffect(() => {
    // Track virtual page view on route change
    HotjarTracking.vpv(location.pathname);
  }, [location]);
  
  return null;
}

function App() {
  // Hotjar is now initialized via script tag in index.html
  
  return (
    <Router>
      <PageViewTracker />
      <div className="min-h-screen bg-gray-50">
        <Routes>
          <Route path="/" element={<BookingWizard />} />
          <Route path="/tip/:token" element={<TipPayment />} />
          <Route path="/tip/:token/success" element={<TipSuccess />} />
          <Route path="/tip/:token/already-tipped" element={<TipAlready />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;